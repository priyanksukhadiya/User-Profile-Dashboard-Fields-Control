<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://profiles.wordpress.org/priyanksukhadiya/
 * @since             1.0.0
 * @package           UPDFC_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       User Profile & Dashboard Fields Control
 * Plugin URI:        https://wordpress.org/plugins/user-profile-dashboard-fields-control
 * Description:       The User Profile & Dashboard Fields Control plugin makes it simple to manage user profile and dashboard fields, improving user experience according to roles.
 * Version:           1.0.0
 * Author:            Priyank Sukhadiya
 * Author URI:        https://profiles.wordpress.org/priyanksukhadiya//
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       user-profile-dashboard-fields-control
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define( 'UPDFC_CURRENT_VERSION', '1.0.0' );
// Include the main plugin class.
require_once plugin_dir_path(__FILE__) . 'includes/class-updfc-plugin.php';

// Initialize the plugin.
$updfc_plugin = new UPDFC_Plugin();
add_action('admin_menu', array($updfc_plugin, 'updfc_add_admin_menu'));
add_action('admin_init', array($updfc_plugin, 'updfc_declare_settings'));
add_action('admin_enqueue_scripts', array($updfc_plugin, 'updfc_hide_fields'));
add_action('admin_enqueue_scripts', array($updfc_plugin, 'updfc_enqueue_styles'));
