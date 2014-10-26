<?php
if (!defined('WP_UNINSTALL_PLUGIN'))
    exit();

if ( !is_multisite() ) {
    delete_option('outpostrocks_options');
}