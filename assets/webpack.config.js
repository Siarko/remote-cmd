const path = require('path');
var webpack = require('webpack');

module.exports = {
    entry: './js/src/index.js',
    mode: 'development',
    output: {
        filename: 'main.js',
        path: path.resolve(__dirname, 'js/dist')
    },
    resolve: {
        modules: ['classes', 'templates', path.resolve(__dirname, 'node_modules')]
    },
    module:{
        rules: [
            {
                test: /\.hbs$/,
                loader: 'handlebars-loader',
                options: {
                    helperDirs: path.join(__dirname, 'js/src/helpers'),
                    precompileOptions: {
                        knownHelpersOnly: false,
                    },
                },
            },
            {
                test: /\.css$/,
                use: ['style-loader', 'css-loader']
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'Slider': 'bootstrap-slider'
        })
    ]
};