<?php
	
	/**
	 * The file that defines the core plugin class
	 *
	 * A class definition that includes attributes and functions used across both the
	 * public-facing side of the site and the admin area.
	 *
	 * @link       https://github.com/raulmangolin/WP-Google-Photos-Importer
	 * @since      1.0.0
	 *
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/includes
	 */
	
	/**
	 * The core plugin class.
	 *
	 * This is used to define internationalization, admin-specific hooks, and
	 * public-facing site hooks.
	 *
	 * Also maintains the unique identifier of this plugin as well as the current
	 * version of the plugin.
	 *
	 * @since      1.0.0
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/includes
	 * @author     Raul Mangolin <eu@raulmangolin.dev>
	 */
	class Wp_Google_Photos_Importer
	{
		
		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Wp_Google_Photos_Importer_Loader $loader Maintains and registers all hooks for the plugin.
		 */
		protected $loader;
		
		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $plugin_name The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;
		
		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $version The current version of the plugin.
		 */
		protected $version;
		
		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct()
		{
			if (defined('WP_GOOGLE_PHOTOS_IMPORTER_VERSION')) {
				$this->version = WP_GOOGLE_PHOTOS_IMPORTER_VERSION;
			} else {
				$this->version = '1.0.0';
			}
			$this->plugin_name = 'wp-google-photos-importer';
			
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
			
		}
		
		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Wp_Google_Photos_Importer_Loader. Orchestrates the hooks of the plugin.
		 * - Wp_Google_Photos_Importer_i18n. Defines internationalization functionality.
		 * - Wp_Google_Photos_Importer_Admin. Defines all hooks for the admin area.
		 * - Wp_Google_Photos_Importer_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies()
		{
			
			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-google-photos-importer-loader.php';
			
			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-google-photos-importer-i18n.php';
			
			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-google-photos-importer-admin.php';
			
			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wp-google-photos-importer-public.php';
			
			$this->loader = new Wp_Google_Photos_Importer_Loader();
			
		}
		
		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Wp_Google_Photos_Importer_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale()
		{
			
			$plugin_i18n = new Wp_Google_Photos_Importer_i18n();
			
			$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
			
		}
		
		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks()
		{
			
			$plugin_admin = new Wp_Google_Photos_Importer_Admin($this->get_plugin_name(), $this->get_version());
			
			$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
			$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
			
			$this->loader->add_action('admin_menu', $plugin_admin, 'plugin_setup_menu');
			
			$this->loader->add_action('admin_post_admin_plugin_setup_menu_save_ids', $plugin_admin, 'plugin_setup_menu_save_ids');
			$this->loader->add_action('wp_ajax_admin_get_google_photos_albums', $plugin_admin, 'get_google_photos_albums');
			$this->loader->add_action('wp_ajax_admin_get_google_photos_albums_photos', $plugin_admin, 'get_google_photos_albums_photos');
			$this->loader->add_action('wp_ajax_admin_add_google_photos_albums_photos', $plugin_admin, 'add_google_photos_albums_photos');
		}
		
		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @return    string    The name of the plugin.
		 * @since     1.0.0
		 */
		public function get_plugin_name()
		{
			return $this->plugin_name;
		}
		
		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @return    string    The version number of the plugin.
		 * @since     1.0.0
		 */
		public function get_version()
		{
			return $this->version;
		}
		
		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks()
		{
			
			$plugin_public = new Wp_Google_Photos_Importer_Public($this->get_plugin_name(), $this->get_version());
			
			$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
			$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		}
		
		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run()
		{
			$this->loader->run();
		}
		
		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @return    Wp_Google_Photos_Importer_Loader    Orchestrates the hooks of the plugin.
		 * @since     1.0.0
		 */
		public function get_loader()
		{
			return $this->loader;
		}
		
	}
