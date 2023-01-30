import React from 'react'
import { createRoot } from 'react-dom/client';
import { ChakraProvider } from '@chakra-ui/react'
import { extendTheme } from "@chakra-ui/react"
import { Heading, SimpleGrid, Box, Text } from '@chakra-ui/react'
import { Card, CardHeader, CardBody, CardFooter } from '@chakra-ui/react'
import { Icon } from '@chakra-ui/react'
import { BiServer } from 'react-icons/bi'

const theme = extendTheme({
    fonts: {
        body: "Roboto, sans-serif",
    },
})

function Dashboard() {
    return (
        <React.StrictMode>
            <ChakraProvider theme={theme}>
                <Heading mb={10} textAlign='center'>Typesense Dashboard</Heading>
                <SimpleGrid columns={[1, null, 2]} spacing={10} p={4} fontSize='1.3rem'>
                    <Card>
                        <CardHeader>
                            <Heading display='flex' alignItems='center' columnGap={2}>
                                <Icon as={BiServer} /> Server statusa
                            </Heading>
                        </CardHeader>
                        <CardBody>
                            <Text>good</Text>
                        </CardBody>
                    </Card>
                </SimpleGrid>
            </ChakraProvider>
        </React.StrictMode>
    );
}

const container = document.getElementById('main');
const root = createRoot(container);
root.render(<Dashboard />);
