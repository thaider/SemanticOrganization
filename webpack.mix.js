const mix = require('laravel-mix');

mix.webpackConfig({
  externals: { 'jquery': 'jQuery' }
});

/*
 |--------------------------------------------------------------------------
 | Default SemanticOrganization extension styles and scripts
 |--------------------------------------------------------------------------
 |
 | Those mix functions are compiling the default scripts and styles for
 | the extension. Files compiled from with this mix functions do
 | overwirte the default semorg assets. Those files will also be
 | overwritten with any git update from the extension repository.
 |
 * /
mix.sass('resources/default/styles/semorg.scss', 'modules/default/css')
   .options({
      processCssUrls: false
   })
   .copyDirectory('resources/default/fonts', 'modules/default/fonts');


/*
 |--------------------------------------------------------------------------
 | Custom SemanticOrganization extension styles
 |--------------------------------------------------------------------------
 |
 | Those functions are meant to be used for custom assets. If you would like
 | to create your own extension assets create the listed files and use the npm
 | scripts to create custom assets. Custom assets have to be activated
 | from whitin the extension settings to be used by your installation.
 |
 | You can rename the given files as follows to have a basic setup for your
 | own extension styles and scripts:
 |
 | - ./resources/scripts/example.custom.js     => ./resources/scripts/custom.js
 | - ./resources/styles/example.custom.scss    => ./resources/styles/custom.scss
 |
 */
mix.sass('resources/custom/styles/custom.scss', 'modules/custom/css')
   .options({
      processCssUrls: false
   })
   .copyDirectory('resources/default/fonts', 'modules/custom/fonts')
   .copyDirectory('resources/custom/fonts', 'modules/custom/fonts');

mix.browserSync({ proxy: process.env.MIX_LOCAL_PROXY_URL });

/**/
