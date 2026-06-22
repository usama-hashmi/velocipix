<?php

if (
    ! defined(
        'WP_UNINSTALL_PLUGIN'
    )
) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Remove plugin settings
|--------------------------------------------------------------------------
*/

delete_option(
    'smp_settings'
);

/*
|--------------------------------------------------------------------------
| Remove plugin transients
|--------------------------------------------------------------------------
*/

delete_transient(
    'smp_frontend_analysis'
);

delete_transient(
    'smp_bulk_running'
);