let path = require('path');
let appEntry = require('./appresource/javascript/entry');
let webpack = require('webpack');
let MiniCssExtractPlugin = require("mini-css-extract-plugin");
// let UglifyJsPlugin = require("uglifyjs-webpack-plugin");
let TerserPlugin = require('terser-webpack-plugin');
let OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");

let config = require(path.resolve(__dirname , 'appresource/javascript/config.js'));

module.exports = {
    watch   : config.watchMode,
    mode    : config.renderMode,
    devtool : config.sourceMap,   
    entry   : appEntry,
    watchOptions: {
        poll: true
    },
    output  : {
        filename    : '[name].js',
        path        : path.resolve(__dirname , 'public/assets/dist/js')
    },
    plugins : [
        new MiniCssExtractPlugin({
            filename : '../css/[name].css'
        }),
        new webpack.ProvidePlugin({
            "$"                 : 'jquery',
            "jQuery"            : 'jquery',
            "window.jQuery"     : "jquery",
            "window.$"          : "jquery",
            "jquery"            : 'jquery',
            "window.jquery"     : "jquery",
            "Popper"            : "popper.js"
        }),
        new webpack.DefinePlugin({
            "require.specified": "require.resolve"
        })
    ],
    optimization: {
        splitChunks: {
            cacheGroups: {
                // commons: {
                //     test: /[\/]node_modules[\/](.*)(\.js$)|[\/]resource[\/]style[\/]assets[\/](.*)(\.js$)/,
                //     name: 'common',
                //     chunks: 'all',
                //     enforce : true
                // }
                // ,
                // styles: {
                //     test: /[\/]node_modules[\/](.*)(\.css$)|[\/]resource[\/]style[\/]assets[\/](.*)(\.css$)/,
                //     name: 'common',
                //     chunks: 'all'
                // }
            }
        },
        minimizer: [
            // new UglifyJsPlugin({
            //     cache: true,
            //     parallel: true,
            //     sourceMap: true // set to true if you want JS source maps
            // }), Gak bisa untuk ES6
            new TerserPlugin({
                cache: true,
                parallel: true,
                sourceMap: config.terserSourceMap,
                terserOptions: {
                    output: {
                        comments: false,
                    },
                },
            }),
            new OptimizeCSSAssetsPlugin({
                cssProcessor: require('cssnano'),
                cssProcessorOptions: {
                    safe: true,
                        discardComments: {
                        removeAll: true,
                    },
                },                
                canPrint: true
            })
        ],
    },
    module : {
        rules : [
            {
                test : /\.css$/,
                use : [ MiniCssExtractPlugin.loader , "css-loader" ]
            },{
                test    : /\.(jpe?g|png|gif|svg)/,
                use     : [{
                    loader      : 'file-loader?limit=1000',
                    options     : {
                        name        : '[name].[ext]',
                        outputPath  : '../images/',
                        publicPath  : config.publicUrl + '/assets/dist/images/'
                    }
                }]
            },{
                test: /\.(otf|eot|svg|ttf|woff|woff2)$/,
                use : [
                    {
                        loader : 'file-loader?limit=1000',
                        options : {
                            name : '[name].[ext]',
                            outputPath : '../fonts/',
                            publicPath : function( url ){
                                let exp = url.split('/');
                                return config.publicUrl + '/assets/dist/fonts/'+ exp[exp.length -1];
                            }
                        }
                    }
                ]
            },{
                test: /datatables\.net(?!.*[.]css$).*/,
                loader: 'imports-loader?define=>false'
            },{ 
                test: require.resolve("pace-progress"),
                loader: "imports-loader?define=>false"
            },{
                test: /\.handlebars$/, 
                loader: "handlebars-loader" 
            }
        ]
    }
};