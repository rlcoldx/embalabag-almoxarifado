const path = require('path');
const webpack = require('webpack');

let ASSET_PATH = '';
module.exports = env => {

    let DOMAIN = JSON.stringify('http://localhost/embalabag-almoxarifado');
    if (env.NODE_ENV === 'erp') {
        DOMAIN = JSON.stringify('https://erp.embalabag.com.br');
        ASSET_PATH = '';
    }

    return {
        mode: 'production',
        entry: './view/assets/js/init.js',
        output: {
            filename: 'main.js',
            path: path.resolve(__dirname, 'view/assets/dist'),
            publicPath: ASSET_PATH + '/view/assets/dist/',
            // Configurações para evitar WebAssembly
            hashFunction: 'xxhash64',
            hashDigest: 'hex',
            hashDigestLength: 8
        },
        plugins: [
            new webpack.DefinePlugin({
                DOMAIN,
                'process.env.NODE_ENV': JSON.stringify('production')
            }),
            new webpack.ProvidePlugin({
                $: 'jquery',
                jQuery: 'jquery'
            }),
        ],
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: 'babel-loader'
                },
            ]
        },
        resolve: {
            extensions: ['.js'],
            fallback: {
                crypto: false,
                stream: false,
                util: false,
                buffer: false
            }
        },
        optimization: {
            minimize: false
        },
        experiments: {
            topLevelAwait: false,
            asyncWebAssembly: false,
            syncWebAssembly: false
        },
        cache: false,
        performance: {
            hints: false
        },
        stats: {
            errorDetails: false,
            children: false
        }
    }
}
