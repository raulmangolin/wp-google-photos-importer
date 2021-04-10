<?php
	
	/**
	 * The plugin bootstrap file
	 *
	 * This file is read by WordPress to generate the plugin information in the plugin
	 * admin area. This file also includes all of the dependencies used by the plugin,
	 * registers the activation and deactivation functions, and defines a function
	 * that starts the plugin.
	 *
	 * @link              https://github.com/raulmangolin/WP-Google-Photos-Importer
	 * @since             1.0.0
	 * @package           Wp_Google_Photos_Importer
	 *
	 * @wordpress-plugin
	 * Plugin Name:       Wordpress Google Photos Importer
	 * Plugin URI:        https://github.com/raulmangolin/WP-Google-Photos-Importer
	 * Description:       Import your Google Photos albums to your Wordpress media gallery
	 * Version:           1.0.0
	 * Author:            Raul Mangolin
	 * Author URI:        https://raulmangolin.dev
	 * License:           GPL-2.0+
	 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain:       wp-google-photos-importer
	 * Domain Path:       /languages
	 */
	
	if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
		require __DIR__ . '/vendor/autoload.php';
	}
	
	// If this file is called directly, abort.
	if (!defined('WPINC')) {
		die;
	}
	
	/**
	 * Currently plugin version.
	 * Start at version 1.0.0 and use SemVer - https://semver.org
	 * Rename this for your plugin and update it as you release new versions.
	 */
	define('WP_GOOGLE_PHOTOS_IMPORTER_VERSION', '1.0.0');
	
	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-wp-google-photos-importer-activator.php
	 */
	function activate_wp_google_photos_importer()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-wp-google-photos-importer-activator.php';
		Wp_Google_Photos_Importer_Activator::activate();
	}
	
	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-wp-google-photos-importer-deactivator.php
	 */
	function deactivate_wp_google_photos_importer()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-wp-google-photos-importer-deactivator.php';
		Wp_Google_Photos_Importer_Deactivator::deactivate();
	}
	
	register_activation_hook(__FILE__, 'activate_wp_google_photos_importer');
	register_deactivation_hook(__FILE__, 'deactivate_wp_google_photos_importer');
	
	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path(__FILE__) . 'includes/class-wp-google-photos-importer.php';
	
	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_wp_google_photos_importer()
	{
		
		$plugin = new Wp_Google_Photos_Importer();
		$plugin->run();
		
	}
	
	run_wp_google_photos_importer();
