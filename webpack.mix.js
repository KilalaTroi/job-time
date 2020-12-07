let mix = require('laravel-mix');

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

mix.js('resources/assets/js/app.js', 'public/js').version();
mix.sass('resources/assets/sass/app.scss', 'public/css').version();

mix.js('resources/assets/js/app-admin.js', 'public/js').version();
mix.sass('resources/assets/sass/app-admin.scss', 'public/css').version();

mix.js('resources/assets/js/app-planner.js', 'public/js').version();
mix.js('resources/assets/js/app-japanese_planner.js', 'public/js').version();
mix.js('resources/assets/js/app-employee.js', 'public/js').version();