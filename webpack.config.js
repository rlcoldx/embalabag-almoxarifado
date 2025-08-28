const path = require('path');
const webpack = require('webpack');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
require('graceful-fs').gracefulify(require('fs'));

let ASSET_PATH = '';
module.exports = env => {

    let DOMAIN = JSON.stringify('http://localhost/embalabag-almoxarifado');
    if (env.NODE_ENV === 'erp') {
        DOMAIN = JSON.stringify('https://erp.embalabag.com.br');
        ASSET_PATH = '';
    }

    return {
        entry: {
            main: './view/assets/js/init.js',
        },
        output: {
            filename: "[name].[contenthash].js",
            path: path.resolve(__dirname, 'view/assets/dist'),
            publicPath: ASSET_PATH + '/view/assets/dist/',
            clean: true,
        },
        plugins: [
            new webpack.DefinePlugin({
                DOMAIN,
            }),
            new webpack.ProvidePlugin({
                $: 'jquery',
                jQuery: 'jquery'
            }),
            new WebpackManifestPlugin(),
            new MiniCssExtractPlugin({
                filename: '[name].[contenthash].css',
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
                            presets: ['@babel/preset-env']
                        }
                    }
                },
                {
                    test: /\.css$/i,
                    use: [MiniCssExtractPlugin.loader, 'css-loader'],
                },
            ]
        },
        resolve: {
            extensions: ['.js'],
        },
        optimization: {
            minimize: true,
        },
        experiments: {
            topLevelAwait: true,
        },
    }
}