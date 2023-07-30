/**
 * @package  Instant Search Plugin for Zen Cart
 * @author   marco-pm
 * @version  4.0.2
 * @see      https://github.com/marco-pm/zencart_instantsearch
 * @license  GNU Public License V2.0
 */

import React, { useState, useEffect, useRef } from 'react';
import { createRoot } from 'react-dom/client';
import { useDebounce } from 'use-debounce';
import { QueryClient, QueryClientProvider, useQuery } from '@tanstack/react-query';
import parse from 'html-react-parser';
import { SlideDown } from 'react-slidedown';

declare const instantSearchSecurityToken: string;
declare const instantSearchDropdownInputSelector: string;
declare const instantSearchDropdownInputMinLength: number;
declare const instantSearchDropdownInputWaitTime: number;

const resultsContainerSelector = 'instantSearchResultsDropdownContainer';

const fetchResults = async (queryTextParsed: string, signal: AbortSignal): Promise<string> => {
    const data = new FormData();
    data.append('keyword', queryTextParsed);
    data.append('scope', 'dropdown');
    data.append('securityToken', instantSearchSecurityToken);

    const response = await fetch('ajax.php?act=ajaxInstantSearch&method=instantSearch', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: data,
        signal,
    });
    return await response.json() as string;
}

interface ResultsContainerProps {
    queryTextParsed: string;
    containerIndex: number;
    setIsResultsContainerExpanded: (isExpanded: boolean) => void;
    setLinkClicked: (isExpanded: boolean) => void;
}

const ResultsContainer = ({ queryTextParsed, containerIndex, setIsResultsContainerExpanded, setLinkClicked }: ResultsContainerProps) => {
    interface Data {
        results: string;
        count: number;
    }

    const [previousController, setPreviousController] = useState<AbortController | null>(null);

    const {isLoading, isError, data, error} = useQuery({
        queryKey: ['results', queryTextParsed],
        queryFn: () => {
            if (previousController) {
                previousController.abort();
            }

            const controller = new AbortController();
            const signal = controller.signal;
            setPreviousController(controller);

            return fetchResults(queryTextParsed, signal).then(data => JSON.parse(data) as Data);
        },
    });
    const [previousData, setPreviousData] = useState<Data | null>(null);
    const [isSlideDownRendered, setIsSlideDownRendered] = useState(false);
    const [additionalClass, setAdditionalClass] = useState('');

    const resultsContainerId = `${resultsContainerSelector}-${containerIndex}`;

    useEffect(() => {
        if (data) {
            setPreviousData(data);
            setIsResultsContainerExpanded(data.count > 0);
        } else {
            setIsResultsContainerExpanded(false);
        }
    }, [data]);

    // Set CSS class depending on the width of the container
    useEffect(() => {
        const resultsContainerDiv = document.querySelector(`#${resultsContainerId}`);

        if (resultsContainerDiv) {
            if (resultsContainerDiv.clientWidth > 250) {
                setAdditionalClass(' instantSearchResultsDropdownContainer--lg');
            } else {
                setAdditionalClass('');
            }
        }
    }, [data]);

    // Handle keyboard navigation (tab and arrow keys, with roving tabindex)
    useEffect(() => {
        const resultsContainerDiv = document.querySelector(`#${resultsContainerId}`);
        const resultsElements = document.querySelectorAll(`#${resultsContainerId} ul li`);

        const handleKeyDown = (event: Event) => {
            const keyboardEvent = event as KeyboardEvent;

            if (keyboardEvent.key !== 'ArrowDown' &&
                keyboardEvent.key !== 'ArrowUp' &&
                keyboardEvent.key !== 'Tab' &&
                !(keyboardEvent.shiftKey && keyboardEvent.key === 'Tab')
            ) {
                return;
            }

            event.preventDefault()
            const option = event.target as HTMLElement;

            if (!option) {
                return;
            }

            let selectedOption: Element | null = null;
            if (keyboardEvent.key === 'ArrowDown' || (keyboardEvent.key === 'Tab' && !keyboardEvent.shiftKey)) {
                const parent = option.parentNode as HTMLElement;
                if (parent) {
                    selectedOption = parent.nextElementSibling;
                    if (!selectedOption) {
                        const parent = option.parentNode as HTMLElement;
                        if (parent) {
                            const selectedOptionUl = parent.parentNode as HTMLElement;
                            const nextUl = selectedOptionUl.nextElementSibling?.nextElementSibling; // Take into account the separator
                            if (nextUl && nextUl.tagName === 'UL') {
                                selectedOption = nextUl.querySelector('li');
                            }
                        }
                    }
                }
            }
            if (keyboardEvent.key === 'ArrowUp' || (keyboardEvent.shiftKey && keyboardEvent.key === 'Tab')) {
                const parent = option.parentNode as HTMLElement;
                if (parent) {
                    selectedOption = parent.previousElementSibling;
                    if (!selectedOption) {
                        const parent = option.parentNode as HTMLElement;
                        if (parent) {
                            const selectedOptionUl = parent.parentNode as HTMLElement;
                            const previousUl = selectedOptionUl.previousElementSibling?.previousElementSibling; // Take into account the separator
                            if (previousUl && previousUl.tagName === 'UL') {
                                selectedOption = previousUl.querySelector('li:last-child');
                            }
                        }
                    }
                }
            }

            if (selectedOption) {
                const selectedOptionA = selectedOption.querySelector('a');
                if (selectedOptionA) {
                    selectedOptionA.focus();
                    resultsElements.forEach(element => element.setAttribute('tabindex', '-1'));
                    selectedOption.setAttribute('tabindex', '0');
                }
            }
        };

        if (!resultsContainerDiv || !resultsElements) {
            return;
        }

        // We set the tabindex of the first element to 0 when it has focus
        if (resultsElements.length && resultsElements[0]) {
            const firstResultA = resultsElements[0].querySelector('a');
            if (firstResultA) {
                firstResultA.addEventListener('focus', () => {
                    resultsElements[0].setAttribute('tabindex', '0');
                });
            }
        }

        const handleLinkClick = () => {
            setLinkClicked(true);
        };

        resultsContainerDiv.addEventListener('mousedown', () => handleLinkClick());
        resultsContainerDiv.addEventListener('touchstart', () => handleLinkClick());

        resultsContainerDiv.addEventListener('keydown', (event: Event) => handleKeyDown(event));

        return () => {
            resultsContainerDiv.removeEventListener('mousedown', handleLinkClick);
            resultsContainerDiv.removeEventListener('touchstart', handleLinkClick);

            resultsContainerDiv.removeEventListener('keydown', handleKeyDown);
        };
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
    const [linkClicked, setLinkClicked] = useState(false);
    const [isResultsContainerExpanded, setIsResultsContainerExpanded] = useState(false);
    const inputRef = useRef<HTMLInputElement>(null);

    const queryClient = new QueryClient();

    const queryTextParsed = debouncedQueryText.replace(/^\s+/, '').replace(/  +/g, ' ');

    function checkQueryLength() {
        if (queryTextParsed.length >= instantSearchDropdownInputMinLength) {
            setShowResults(true);
        } else {
            setShowResults(false);
        }
    }

    useEffect(() => {
        checkQueryLength();
    }, [queryTextParsed]);

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

    // Close dropdown on escape
    useEffect(() => {
        function handleKeyDown(event: KeyboardEvent) {
            if (event.key === 'Escape') {
                setShowResults(false);
            }
        }

        document.addEventListener('keydown', handleKeyDown);

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
        };
    }, []);

    function handleBlur(event: React.FocusEvent<HTMLInputElement>) {
        // if a result link has been clicked, or focus has moved to the results container, do not hide the results
        if (linkClicked || (
            event.relatedTarget &&
            event.relatedTarget instanceof HTMLElement &&
            event.relatedTarget.classList.contains('instantSearchResultsDropdownContainer__link')
            )
        ) {
            return;
        }

        setShowResults(false);
    }

    return (
        <React.StrictMode>
            <QueryClientProvider client={queryClient}>
                <input
                    type='text'
                    value={queryText}
                    onChange={e => setQueryText(e.currentTarget.value)}
                    onFocus={() => checkQueryLength()}
                    onBlur={handleBlur}
                    aria-expanded={showResults && isResultsContainerExpanded}
                    autoComplete='off'
                    role='combobox'
                    aria-autocomplete='list'
                    aria-owns={showResults ? `#${resultsContainerSelector}-${containerIndex}` : ''}
                    ref={inputRef}
                />
                {
                    showResults &&
                    <ResultsContainer
                        queryTextParsed={debouncedQueryText}
                        containerIndex={containerIndex}
                        setIsResultsContainerExpanded={setIsResultsContainerExpanded}
                        setLinkClicked={setLinkClicked}
                    />
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
