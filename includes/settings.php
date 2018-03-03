<?php

use nigiri\themes\Theme;

return [
    'theme' => new Theme(),
    'params' => [
        /** Website's Email address, used as sender of the emails and webmaster's contact */
        'email' => '',

        'site_name' => 'My Site Name',

        /** Set to true if URL Rewriting is active */
        'clean_urls' => true,

        /** A prefix for the URL. Useful if the site is in a subdirectory */
        'url_prefix' => '',

        /** the home page, the one to show if there is no page requested */
        'default_page' => 'site/home',

        /** An array of enabled languages in the website */
        'languages' => ['it'],

        /** The default language to be used if none is specified */
        'default_language' => 'it',

        /** An array of parameters to pass to the set_locale function, for each configured language */
        'locales' => [
          'it' => ['it_IT.utf8','ita.utf8', 'it_IT.utf-8','ita.utf-8','it_IT','ita']
        ],

        /** The timezone to use in the website */
        'timezone' => 'Europe/Rome',

        'debug' => true,

        /** Defines views to be used to render each type of Exception.
         * Keys of the array must be Exception names (with full namespace) values must be in the format "ThemeClass:ViewFileName */
        'exceptions_views' => []
    ]
];
