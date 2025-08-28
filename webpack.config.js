const path = require('path');
const webpack = require('webpack');
const ManifestPlugin = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
require('graceful-fs').gracefulify(require('fs'));

let ASSET_PATH = '';
module.exports = env => {

    let DOMAIN = JSON.stringify('http://localhost/embalabag-almoxarifado');
    if (env.NODE_ENV === 'sampel') {
        DOMAIN = JSON.stringify('https://embalabag.com.br/almoxarifado');
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
        },
        plugins: [
            new webpack.DefinePlugin({
                DOMAIN,
            }),
            new webpack.ProvidePlugin({
                $: 'jquery',
                jQuery: 'jquery'
            }),
            new ManifestPlugin(),
            new MiniCssExtractPlugin({
                filename: '[name].[contenthash].css',
            }),
        ],
        devtool: false,
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /nodemodules/,
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
    }
}