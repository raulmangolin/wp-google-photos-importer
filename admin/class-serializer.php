<?php
	
	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @link       https://github.com/raulmangolin/wp-google-photos-importer
	 * @since      1.0.0
	 *
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/admin
	 */
	
	/**
	 * Performs all sanitization functions required to save the option values to
	 * the database.
	 *
	 * This will also check the specified nonce and verify that the current user has
	 * permission to save the data.
	 *
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/admin
	 * @author     Raul Mangolin <eu@raulmangolin.dev>
	 */
	class Serializer
	{
		
		/**
		 * Initializes the function by registering the save function with the
		 * admin_post hook so that we can save our options to the database.
		 */
		public function init()
		{
			add_action('admin_post', array($this, 'save'));
		}
		
		/**
		 * Validates the incoming nonce value, verifies the current user has
		 * permission to save the value from the options page and saves the
		 * option to the database.
		 */
		public function save()
		{
			
			// First, validate the nonce and verify the user as permission to save.
			if (!($this->has_valid_nonce() && current_user_can('manage_options'))) {
				// TODO: Display an error message.
			}
			
			// If the above are valid, sanitize and save the option.
			if (null !== wp_unslash($_POST['google-clientId'])) {
				$value = sanitize_text_field($_POST['google-clientId']);
				update_option('wp-google-photos-importer-google-clientId', $value);
			}
			
			if (null !== wp_unslash($_POST['google-clientSecret'])) {
				$value = sanitize_text_field($_POST['google-clientSecret']);
				update_option('wp-google-photos-importer-google-clientSecret', $value);
			}
			
			$this->redirect();
		}
		
		/**
		 * Determines if the nonce variable associated with the options page is set
		 * and is valid.
		 *
		 * @access private
		 *
		 * @return boolean False if the field isn't set or the nonce value is invalid;
		 *                 otherwise, true.
		 */
		private function has_valid_nonce()
		{
			
			// If the field isn't even in the $_POST, then it's invalid.
			if (!isset($_POST['wp-google-photos-importer-custom-message'])) { // Input var okay.
				return false;
			}
			
			$field = wp_unslash($_POST['wp-google-photos-importer-custom-message']);
			$action = 'wp-google-photos-importer-settings-save';
			
			return wp_verify_nonce($field, $action);
			
		}
		
		/**
		 * Redirect to the page from which we came (which should always be the
		 * admin page. If the referred isn't set, then we redirect the user to
		 * the login page.
		 *
		 * @access private
		 */
		private function redirect()
		{
			
			// To make the Coding Standards happy, we have to initialize this.
			if (!isset($_POST['_wp_http_referer'])) { // Input var okay.
				$_POST['_wp_http_referer'] = wp_login_url();
			}
			
			// Sanitize the value of the $_POST collection for the Coding Standards.
			$url = sanitize_text_field(
				wp_unslash($_POST['_wp_http_referer']) // Input var okay.
			);
			
			// Finally, redirect back to the admin page.
			wp_safe_redirect(urldecode($url));
			exit;
			
		}
	}
