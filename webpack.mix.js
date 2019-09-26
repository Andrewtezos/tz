const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js([
      'resources/js/scripts.js'
    ], 'public/js/index.js')
    .js([
      'resources/js/admin.js'
    ], 'public/js/admin.js')
    .styles([
      'resources/css/style.css',
      'resources/css/night.css',
      'resources/css/datatables.css',
      'resources/css/fonts.css',
      'resources/css/icons.css'
    ], 'public/css/index.css')
    .styles([
      'resources/css/admin.css',
    ], 'public/css/admin.css')
    .styles([
      'resources/css/login.css',
  ], 'public/css/login.css')
    .copy('resources/js/jquery-3.4.1.min.js', 'public/js/jquery-3.4.1.min.js')
    .copy('resources/js/jquery.cookie.js', 'public/js/jquery.cookie.js')
    .copyDirectory('resources/css/font', 'public/css/font')
    .copyDirectory('resources/img', 'public/img')
    .copyDirectory('resources/js/ckeditor', 'public/js/ckeditor')
    ;
