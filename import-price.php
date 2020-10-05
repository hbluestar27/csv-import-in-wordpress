<?php
/**
* Plugin Name: Import Price Data
* Plugin URI: 
* Description: This is a plugin to import data from the csv file.
* Version: 1.0
* Author: hbluestar@outlook.com
* Author URI: 
**/

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'MAIN_DIR', plugin_dir_path( __FILE__ ) );

require_once(MAIN_DIR . '/admin/class.menu.php');
require_once(MAIN_DIR . '/admin/class.homepage.php');
require_once(MAIN_DIR . '/admin/class.serializer.php');
require_once(MAIN_DIR . '/admin/class.import.php');

add_action( 'plugins_loaded', 'plugin_init' );

function plugin_init() {
    global $wpdb;

    $import = new Import($wpdb);

    $serializer = new Serializer($import);
    $serializer->init();

    $plugin = new Menu(new HomePage());
    $plugin->init();
}

?>