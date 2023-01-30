const path = require('path');

module.exports = [
    {
        name: 'instant_search_dropdown',
        entry: path.resolve(__dirname, './src/instant_search/instant_search_dropdown.tsx'),
        output: {
            path: path.resolve(__dirname, './includes/templates/responsive_classic/jscript/'),
            filename: './instant_search_dropdown.min.js',
        },
        module: {
            rules: [
                {
                    test: /\.tsx?$/,
                    use: 'ts-loader',
                    exclude: /node_modules/,
                },
            ],
        },
    },
    {
        name: 'instant_search_results',
        entry: path.resolve(__dirname, './src/instant_search/instant_search_results.tsx'),
        output: {
            path: path.resolve(__dirname, './includes/templates/responsive_classic/jscript/'),
            filename: './instant_search_results.min.js',
        },
        module: {
            rules: [
                {
                    test: /\.tsx?$/,
                    use: 'ts-loader',
                    exclude: /node_modules/,
                },
            ],
        },
    },
    {
        name: 'typesense_dashboard',
        mode: 'development',
        entry: path.resolve(__dirname, './src/typesense_dashboard/typesense_dashboard.js'),
        output: {
            path: path.resolve(__dirname, './includes/templates/responsive_classic/jscript/'),
            filename: './zc_plugins/InstantSearch/v3.0.1/admin/typesense_dashboard.min.js',
        },
        module: {
            rules: [
                {
                    use: ['babel-loader', 'ts-loader'],
                },
            ],
        },
    },
];
