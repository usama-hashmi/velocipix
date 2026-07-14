<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Remove plugin settings
|--------------------------------------------------------------------------
*/

delete_option(
    'velocipix_settings'
);

/*
|--------------------------------------------------------------------------
| Remove plugin transients
|--------------------------------------------------------------------------
*/

delete_transient(
    'velocipix_frontend_analysis'
);

delete_transient(
    'velocipix_bulk_running'
);