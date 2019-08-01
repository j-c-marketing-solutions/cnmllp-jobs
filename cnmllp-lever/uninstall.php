<?php

//this check makes sure that this file is called manually.
if (!defined("WP_UNINSTALL_PLUGIN"))
    exit();

global $wpdb;

$table_name = $wpdb->prefix . 'cnmllp_jobs';

$wpdb->query("DROP TABLE IF EXISTS $table_name" );

delete_option( 'cnmllp_db_version' );
delete_option( 'cnmllp_db_last_update');
delete_option( 'lever_api_settings');