<?php

/**
 * Plugin Name:       Amazeingame Bookly Extention
 * Description:       Specifique pour l'affichage du pluggin Bookly Frontend et Backend
 * Version:           1.0.0
 * Author:            Nticstudio
 * Author URI:        http://nticstudio.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       amig-bookly
 * Domain Path:       /languages
 */

// If this file is called directly, abort.

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'AMIGBOOKLY_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-AmigBookly-activator.php
 */
function activate_AmigBookly() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-amig-bookly-activator.php';
	AmigBookly_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-AmigBookly-deactivator.php
 */
function deactivate_AmigBookly() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-amig-bookly-deactivator.php';
	AmigBookly_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_AmigBookly' );
register_deactivation_hook( __FILE__, 'deactivate_AmigBookly' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-amig-bookly.php';





/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_AmigBookly() {

	$plugin = new AmigBookly();
	$plugin->run();

}
run_AmigBookly();
