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
                process: false,
                os: false,
                assert: false,
                constants: false,
                domain: false,
                events: false,
                http: false,
                https: false,
                querystring: false,
                url: false,
                zlib: false
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
            lazyCompilation: false,
            outputModule: false,
            newNextPlugins: false
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
            children: false,
            modules: false,
            chunks: false,
            chunkModules: false,
            chunkOrigins: false,
            reasons: false,
            source: false,
            publicPath: false,
            entrypoints: false,
            performance: false,
            timings: false,
            builtAt: false,
            version: false,
            hash: false
        },
        node: {
            global: true
        },
        target: 'web',
        externals: {
            fs: 'commonjs fs',
            path: 'commonjs path',
            crypto: 'commonjs crypto',
            stream: 'commonjs stream',
            util: 'commonjs util',
            buffer: 'commonjs buffer',
            process: 'commonjs process'
        }
    }
}
