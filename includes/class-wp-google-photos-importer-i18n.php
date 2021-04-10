<?php
	
	/**
	 * Define the internationalization functionality
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 *
	 * @link       https://github.com/raulmangolin/WP-Google-Photos-Importer
	 * @since      1.0.0
	 *
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/includes
	 */
	
	/**
	 * Define the internationalization functionality.
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 *
	 * @since      1.0.0
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/includes
	 * @author     Raul Mangolin <eu@raulmangolin.dev>
	 */
	class Wp_Google_Photos_Importer_i18n
	{
		
		
		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain()
		{
			
			load_plugin_textdomain(
				'wp-google-photos-importer',
				false,
				dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
			);
			
		}
		
		
	}
