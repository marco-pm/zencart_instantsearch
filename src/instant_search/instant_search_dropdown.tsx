/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  3.0.1
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

import React, {useState, useEffect, useRef} from 'react';
import { createRoot } from 'react-dom/client';
import { useDebounce } from 'use-debounce';
import { QueryClient, QueryClientProvider, useQuery } from "@tanstack/react-query";
import parse from 'html-react-parser';
import { SlideDown } from 'react-slidedown';

declare const instantSearchSecurityToken: string;
declare const instantSearchDropdownInputSelector: string;
declare const instantSearchDropdownInputMinLength: number;
declare const instantSearchDropdownInputWaitTime: number;

async function fetchResults(queryTextParsed: string): Promise<string> {
    const data = new FormData();
    data.append('keyword', queryTextParsed);
    data.append('scope', 'dropdown');
    data.append('securityToken', instantSearchSecurityToken);

    const response = await fetch('ajax.php?act=ajaxInstantSearch&method=instantSearch', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data
    });
    return await response.json() as string;
}

interface ResultsContainerProps {
    queryTextParsed: string;
    containerIndex: number;
}

const ResultsContainer = ({ queryTextParsed, containerIndex }: ResultsContainerProps) => {
    interface Data {
        results: string;
        count: number;
    }

    const {isLoading, isError, data, error} = useQuery<Data, Error>({
        queryKey: ['results', queryTextParsed],
        queryFn: async () => fetchResults(queryTextParsed).then(data => JSON.parse(data) as Data)
    });
    const [previousData, setPreviousData] = useState<Data | null>(null);
    const [isSlideDownRendered, setIsSlideDownRendered] = useState(false);
    const [additionalClass, setAdditionalClass] = useState('');

    const resultsContainerSelector = 'instantSearchResultsDropdownContainer';
    const resultsContainerId = `${resultsContainerSelector}-${containerIndex}`;

    useEffect(() => {
        if (data) {
            setPreviousData(data);
        }
    }, [data]);

    useEffect(() => {
        const div = document.querySelector(`#${resultsContainerId}`);
        if (div) {
            if (div.clientWidth > 250) {
                setAdditionalClass(' instantSearchResultsDropdownContainer--lg');
            } else {
                setAdditionalClass('');
            }
        }
    }, [data]);

    if (isLoading) {
        if (previousData && previousData.results) {
            if (!isSlideDownRendered) {
                setIsSlideDownRendered(true);
            }
            return (
                <div id={resultsContainerId} className={`${resultsContainerSelector}${additionalClass}`}>
                    {parse(previousData.results)}
                </div>
            );
        } else {
            if (isSlideDownRendered) {
                setIsSlideDownRendered(false);
            }
            return <></>;
        }
    }

    if (isError) {
        console.log(error);
        return <></>;
    }

    if (!data || !data.results) {
        return <></>;
    }

    let results;
    if (!isSlideDownRendered) {
        results = <SlideDown>{parse(data.results)}</SlideDown>;
    } else {
        results = parse(data.results);
    }

    return (
        <div id={resultsContainerId} className={`${resultsContainerSelector}${additionalClass}`}>
            {results}
        </div>
    );
}

interface InstantSearchDropdownProps {
    inputTextAttributes: NamedNodeMap;
    containerIndex: number;
}

const InstantSearchDropdown = ({ inputTextAttributes, containerIndex }: InstantSearchDropdownProps) => {
    const [queryText, setQueryText] = useState('');
    const [debouncedQueryText] = useDebounce(queryText, instantSearchDropdownInputWaitTime);
    const [showResults, setShowResults] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    const queryClient = new QueryClient();

    const queryTextParsed = debouncedQueryText.replace(/^\s+/, "").replace(/  +/g, ' ');

    useEffect(() => {
        const cancelQuery = async () => {
            await queryClient.cancelQueries({queryKey: ['results']});
        }

        cancelQuery().catch(console.error);
    }, [queryText]);

    useEffect(() => {
        if (inputRef.current) {
            for (let i = 0; i < inputTextAttributes.length; i++) {
                // Exclude attributes that are already set
                if (inputRef.current.hasAttribute(inputTextAttributes[i].name) || inputTextAttributes[i].name.startsWith('on')) {
                    continue;
                }
                inputRef.current.setAttribute(inputTextAttributes[i].name, inputTextAttributes[i].value);
            }
        }
    }, []);

    return (
        <React.StrictMode>
            <QueryClientProvider client={queryClient}>
                <input
                    type="text"
                    value={queryText}
                    onChange={e => setQueryText(e.currentTarget.value)}
                    onFocus={() => setShowResults(true)}
                    onBlur={() => setShowResults(false)}
                    ref={inputRef}
                />
                {
                    showResults &&
                    queryTextParsed.length >= instantSearchDropdownInputMinLength &&
                    <ResultsContainer queryTextParsed={debouncedQueryText} containerIndex={containerIndex} />
                }
            </QueryClientProvider>
        </React.StrictMode>
    )
}

// Add autocomplete dropdown on search inputs
const instantSearchInputs = document.querySelectorAll(instantSearchDropdownInputSelector);

for (let i = 0; i < instantSearchInputs.length; i++) {
    const input = instantSearchInputs[i] as HTMLInputElement;
    const inputTextAttributes = input.attributes;

    const container = document.createElement('div');
    container.className = 'instantSearchInputWrapper';
    if (input.parentNode) {
        input.parentNode.insertBefore(container, input);
        input.remove();

        const root = createRoot(container);
        root.render(<InstantSearchDropdown inputTextAttributes={inputTextAttributes} containerIndex={i}/>);
    }
}
