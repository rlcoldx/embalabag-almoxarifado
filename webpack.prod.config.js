const path = require('path');
const webpack = require('webpack');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');

let ASSET_PATH = '';
module.exports = env => {

    let DOMAIN = JSON.stringify('http://localhost/embalabag-almoxarifado');
    if (env.NODE_ENV === 'erp') {
        DOMAIN = JSON.stringify('https://erp.embalabag.com.br');
        ASSET_PATH = '';
    }

    return {
        mode: 'production',
        entry: {
            main: './view/assets/js/init.js',
        },
        output: {
            filename: "[name].js",
            path: path.resolve(__dirname, 'view/assets/dist'),
            publicPath: ASSET_PATH + '/view/assets/dist/',
            clean: true,
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
            new WebpackManifestPlugin(),
        ],
        devtool: false,
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                ['@babel/preset-env', {
                                    targets: {
                                        browsers: ['> 1%', 'last 2 versions']
                                    },
                                    useBuiltIns: false
                                }]
                            ],
                            cacheDirectory: false,
                            cacheCompression: false
                        }
                    }
                },
            ]
        },
        resolve: {
            extensions: ['.js'],
            fallback: {
                fs: false,
                path: false,
                crypto: false,
                stream: false,
                util: false,
                buffer: false,
                process: false
            }
        },
        optimization: {
            minimize: false,
            splitChunks: false,
        },
        experiments: {
            topLevelAwait: false,
            asyncWebAssembly: false,
            syncWebAssembly: false,
            layers: false,
            lazyCompilation: false
        },
        cache: false,
        performance: {
            hints: false,
            maxEntrypointSize: 999999999,
            maxAssetSize: 999999999
        },
        infrastructureLogging: {
            level: 'error'
        },
        stats: {
            errorDetails: false,
            children: false
        },
        node: {
            global: true
        },
        target: 'web',
        externals: {
            fs: 'commonjs fs',
            path: 'commonjs path'
        }
    }
}
