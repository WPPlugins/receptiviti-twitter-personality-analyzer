<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

defined("RECEPTIVITI_PLUGIN_SLUG__") || define("RECEPTIVITI_PLUGIN_SLUG__", "__receptivity_");

$opts   = wp_load_alloptions();
foreach($opts as $key=>$value){
    if(strpos($key, RECEPTIVITI_PLUGIN_SLUG__) === 0) delete_option($key);
}