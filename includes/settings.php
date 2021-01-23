<?php

return [
    'default_theme' => [
      'class' => '\nigiri\themes\Theme',
      'config' => []
    ],
    'exception_db_writer' => null,
    'permanent_plugins' => [

    ],
    'params' => [
        /** Website's Email address, used as sender of the emails and webmaster's contact */
        NIGIRI_PARAM_EMAIL => '',

        NIGIRI_PARAM_TECH_EMAIL => '',

        NIGIRI_PARAM_SITE_NAME => 'My Site Name',

        /** Set to true if URL Rewriting is active */
        NIGIRI_PARAM_CLEAN_URL => true,

        /** A prefix for the URL. Useful if the site is in a subdirectory */
        NIGIRI_PARAM_URL_PREFIX => '',

        /** the home page, the one to show if there is no page requested */
        NIGIRI_PARAM_DEFAULT_PAGE => 'site/home',

        NIGIRI_PARAM_LOGIN_URL => 'site/home',

        /** An array of enabled languages in the website */
        NIGIRI_PARAM_SUPPORTED_LANGUAGES => ['it'],

        /** The default language to be used if none is specified */
        NIGIRI_PARAM_DEFAULT_LANGUAGE => 'it',

        /** An array of parameters to pass to the set_locale function, for each configured language */
        NIGIRI_PARAM_LOCALES => [
          'it' => ['it_IT.utf8','ita.utf8', 'it_IT.utf-8','ita.utf-8','it_IT','ita']
        ],

        /** The timezone to use in the website */
        NIGIRI_PARAM_TIMEZONE => 'Europe/Rome',

        NIGIRI_PARAM_EMAIL_SMTP => false,

        NIGIRI_PARAM_EMAIL_SMTP_CONFIG => [
          'host' => '',
            'port' => 25,
            'user' => '',
            'psw' => '',
            'secure' => 1
        ],

        NIGIRI_PARAM_DEBUG => true,

        /** Defines views to be used to render each type of Exception.
         * Keys of the array must be Exception names (with full namespace) values must follow the format for Exception::$theme */
        NIGIRI_PARAM_EXCEPTIONS_VIEWS => []
    ]
];
