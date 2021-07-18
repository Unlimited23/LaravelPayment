const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/sass/app.scss', 'public/css');
    
mix.js('resources/js/app.js', 'public/js').extract([
    'jquery',
    'popper.js',
    'bootstrap',
    'lodash',
    'vue',
    'axios',
    'feather-icons'
]);

mix.version();
mix.mergeManifest();