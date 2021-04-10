<?php
	
	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @link       https://github.com/raulmangolin/WP-Google-Photos-Importer
	 * @since      1.0.0
	 *
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/admin
	 */
	
	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the admin-specific stylesheet and JavaScript.
	 *
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/admin
	 * @author     Raul Mangolin <eu@raulmangolin.dev>
	 */
	class Wp_Google_Photos_Importer_Admin
	{
		
		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $plugin_name The ID of this plugin.
		 */
		private $plugin_name;
		
		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $version The current version of this plugin.
		 */
		private $version;
		
		/**
		 * Initialize the class and set its properties.
		 *
		 * @param string $plugin_name The name of this plugin.
		 * @param string $version The version of this plugin.
		 * @since    1.0.0
		 */
		public function __construct($plugin_name, $version)
		{
			
			$this->plugin_name = $plugin_name;
			$this->version = $version;
		}
		
		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles()
		{
			
			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Wp_Google_Photos_Importer_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Wp_Google_Photos_Importer_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */
			
			wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-google-photos-importer-admin.css', array(), $this->version, 'all');
			
		}
		
		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts()
		{
			
			/**
			 * This function is provided for demonstration purposes only.
			 *
			 * An instance of this class should be passed to the run() function
			 * defined in Wp_Google_Photos_Importer_Loader as all of the hooks are defined
			 * in that particular class.
			 *
			 * The Wp_Google_Photos_Importer_Loader will then create the relationship
			 * between the defined hooks and the functions defined in this
			 * class.
			 */
			
			wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp-google-photos-importer-admin.js', array('jquery'), $this->version, false);
			
		}
		
		public function plugin_setup_menu()
		{
			add_menu_page(
				'Google Photos Importer',
				'Google Photos',
				'manage_options',
				'wp-google-photos-importer-settings',
				[$this, 'plugin_setup_menu_index']
			);
		}
		
		public function plugin_setup_menu_index()
		{
			require_once plugin_dir_path(__FILE__) . 'partials/wp-google-photos-importer-admin-display.php';
		}
		
		public function plugin_setup_menu_save_ids()
		{
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
		
		public function get_google_photos_albums()
		{
			$data = ['albums' => [], 'next' => false];
			
			try {
				$client = new Google\Client();
				$client->setClientId(get_option('wp-google-photos-importer-google-clientId'));
				$client->setClientSecret(get_option('wp-google-photos-importer-google-clientSecret'));
				
				$client->addScope(Google_Service_PhotosLibrary::PHOTOSLIBRARY);
				$client->refreshToken($_POST['access_token']);
				
				$service = new Google_Service_PhotosLibrary($client);
				
				$params = ['pageSize' => 50];
				if (isset($_POST['nextPage'])) {
					$params['pageToken'] = $_POST['nextPage'];
				}
				
				$albums = $service->albums->listAlbums($params);
				
				$data['albums'] = $albums->getAlbums();
				$data['next'] = $albums->getNextPageToken();
				
			} catch (Exception $e) {
				// do nothing
			}
			
			header('Content-type: application/json');
			echo json_encode($data);
			exit();
		}
		
		public function get_google_photos_albums_photos()
		{
			$data = ['photos' => [], 'next' => false];
			
			try {
				$client = new Google\Client();
				$client->setClientId(get_option('wp-google-photos-importer-google-clientId'));
				$client->setClientSecret(get_option('wp-google-photos-importer-google-clientSecret'));
				
				$client->addScope(Google_Service_PhotosLibrary::PHOTOSLIBRARY);
				$client->refreshToken($_POST['access_token']);
				
				$service = new Google_Service_PhotosLibrary($client);
				
				$body = new Google_Service_PhotosLibrary_SearchMediaItemsRequest();
				$body->setAlbumId($_POST['albumId']);
				$body->setPageSize(50);
				
				if (isset($_POST['nextPage'])) {
					$body->setPageToken($_POST['nextPage']);
				}
				
				$album = $service->mediaItems->search($body);
				
				$data['photos'] = $album->getMediaItems();
				$data['next'] = $album->getNextPageToken();
				
			} catch (Exception $e) {
				// do nothing
			}
			
			header('Content-type: application/json');
			echo json_encode($data);
			exit();
		}
		
		public function add_google_photos_albums_photos()
		{
			$data = ['success' => false];
			
			try {
				$client = new Google\Client();
				$client->setClientId(get_option('wp-google-photos-importer-google-clientId'));
				$client->setClientSecret(get_option('wp-google-photos-importer-google-clientSecret'));
				
				$client->addScope(Google_Service_PhotosLibrary::PHOTOSLIBRARY);
				$client->refreshToken($_POST['access_token']);
				
				$service = new Google_Service_PhotosLibrary($client);
				
				$media = $service->mediaItems->get($_POST['mediaId']);
				$mediaUrl = $media->getBaseUrl() . '=w3000-h3000';
				$description = $media->getDescription();
				$mime = $media->getMimeType();
				
				if ($mediaUrl) {
					$upload_dir = wp_upload_dir();
					
					$image_data = file_get_contents($mediaUrl);
					$filename = $_POST['mediaId'] . '.' . $this->get_file_extension($mime);
					
					if (wp_mkdir_p($upload_dir['path'])) {
						$file = $upload_dir['path'] . '/' . $filename;
					} else {
						$file = $upload_dir['basedir'] . '/' . $filename;
					}
					
					$save = file_put_contents($file, $image_data);
					
					$attachment = array(
						'post_mime_type' => $mime,
						'post_title' => $description,
						'post_content' => '',
						'post_status' => 'inherit'
					);
					
					$attach_id = wp_insert_attachment($attachment, $file);
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					$attach_data = wp_generate_attachment_metadata($attach_id, $file);
					wp_update_attachment_metadata($attach_id, $attach_data);
					
					$data = ['success' => true, 'attach_id' => $attach_id, 'mime' => $mime];
				}
			} catch (Exception $e) {
				// do nothing
			}
			
			header('Content-type: application/json');
			echo json_encode($data);
			exit();
		}
		
		public function get_file_extension($mime)
		{
			$all_mimes = '{"png":["image\/png","image\/x-png"],"bmp":["image\/bmp","image\/x-bmp","image\/x-bitmap","image\/x-xbitmap","image\/x-win-bitmap","image\/x-windows-bmp","image\/ms-bmp","image\/x-ms-bmp","application\/bmp","application\/x-bmp","application\/x-win-bitmap"],"gif":["image\/gif"],"jpeg":["image\/jpeg","image\/pjpeg"],"xspf":["application\/xspf+xml"],"vlc":["application\/videolan"],"wmv":["video\/x-ms-wmv","video\/x-ms-asf"],"au":["audio\/x-au"],"ac3":["audio\/ac3"],"flac":["audio\/x-flac"],"ogg":["audio\/ogg","video\/ogg","application\/ogg"],"kmz":["application\/vnd.google-earth.kmz"],"kml":["application\/vnd.google-earth.kml+xml"],"rtx":["text\/richtext"],"rtf":["text\/rtf"],"jar":["application\/java-archive","application\/x-java-application","application\/x-jar"],"zip":["application\/x-zip","application\/zip","application\/x-zip-compressed","application\/s-compressed","multipart\/x-zip"],"7zip":["application\/x-compressed"],"xml":["application\/xml","text\/xml"],"svg":["image\/svg+xml"],"3g2":["video\/3gpp2"],"3gp":["video\/3gp","video\/3gpp"],"mp4":["video\/mp4"],"m4a":["audio\/x-m4a"],"f4v":["video\/x-f4v"],"flv":["video\/x-flv"],"webm":["video\/webm"],"aac":["audio\/x-acc"],"m4u":["application\/vnd.mpegurl"],"pdf":["application\/pdf","application\/octet-stream"],"pptx":["application\/vnd.openxmlformats-officedocument.presentationml.presentation"],"ppt":["application\/powerpoint","application\/vnd.ms-powerpoint","application\/vnd.ms-office","application\/msword"],"docx":["application\/vnd.openxmlformats-officedocument.wordprocessingml.document"],"xlsx":["application\/vnd.openxmlformats-officedocument.spreadsheetml.sheet","application\/vnd.ms-excel"],"xl":["application\/excel"],"xls":["application\/msexcel","application\/x-msexcel","application\/x-ms-excel","application\/x-excel","application\/x-dos_ms_excel","application\/xls","application\/x-xls"],"xsl":["text\/xsl"],"mpeg":["video\/mpeg"],"mov":["video\/quicktime"],"avi":["video\/x-msvideo","video\/msvideo","video\/avi","application\/x-troff-msvideo"],"movie":["video\/x-sgi-movie"],"log":["text\/x-log"],"txt":["text\/plain"],"css":["text\/css"],"html":["text\/html"],"wav":["audio\/x-wav","audio\/wave","audio\/wav"],"xhtml":["application\/xhtml+xml"],"tar":["application\/x-tar"],"tgz":["application\/x-gzip-compressed"],"psd":["application\/x-photoshop","image\/vnd.adobe.photoshop"],"exe":["application\/x-msdownload"],"js":["application\/x-javascript"],"mp3":["audio\/mpeg","audio\/mpg","audio\/mpeg3","audio\/mp3"],"rar":["application\/x-rar","application\/rar","application\/x-rar-compressed"],"gzip":["application\/x-gzip"],"hqx":["application\/mac-binhex40","application\/mac-binhex","application\/x-binhex40","application\/x-mac-binhex40"],"cpt":["application\/mac-compactpro"],"bin":["application\/macbinary","application\/mac-binary","application\/x-binary","application\/x-macbinary"],"oda":["application\/oda"],"ai":["application\/postscript"],"smil":["application\/smil"],"mif":["application\/vnd.mif"],"wbxml":["application\/wbxml"],"wmlc":["application\/wmlc"],"dcr":["application\/x-director"],"dvi":["application\/x-dvi"],"gtar":["application\/x-gtar"],"php":["application\/x-httpd-php","application\/php","application\/x-php","text\/php","text\/x-php","application\/x-httpd-php-source"],"swf":["application\/x-shockwave-flash"],"sit":["application\/x-stuffit"],"z":["application\/x-compress"],"mid":["audio\/midi"],"aif":["audio\/x-aiff","audio\/aiff"],"ram":["audio\/x-pn-realaudio"],"rpm":["audio\/x-pn-realaudio-plugin"],"ra":["audio\/x-realaudio"],"rv":["video\/vnd.rn-realvideo"],"jp2":["image\/jp2","video\/mj2","image\/jpx","image\/jpm"],"tiff":["image\/tiff"],"eml":["message\/rfc822"],"pem":["application\/x-x509-user-cert","application\/x-pem-file"],"p10":["application\/x-pkcs10","application\/pkcs10"],"p12":["application\/x-pkcs12"],"p7a":["application\/x-pkcs7-signature"],"p7c":["application\/pkcs7-mime","application\/x-pkcs7-mime"],"p7r":["application\/x-pkcs7-certreqresp"],"p7s":["application\/pkcs7-signature"],"crt":["application\/x-x509-ca-cert","application\/pkix-cert"],"crl":["application\/pkix-crl","application\/pkcs-crl"],"pgp":["application\/pgp"],"gpg":["application\/gpg-keys"],"rsa":["application\/x-pkcs7"],"ics":["text\/calendar"],"zsh":["text\/x-scriptzsh"],"cdr":["application\/cdr","application\/coreldraw","application\/x-cdr","application\/x-coreldraw","image\/cdr","image\/x-cdr","zz-application\/zz-winassoc-cdr"],"wma":["audio\/x-ms-wma"],"vcf":["text\/x-vcard"],"srt":["text\/srt"],"vtt":["text\/vtt"],"ico":["image\/x-icon","image\/x-ico","image\/vnd.microsoft.icon"],"csv":["text\/x-comma-separated-values","text\/comma-separated-values","application\/vnd.msexcel"],"json":["application\/json","text\/json"]}';
			$all_mimes = json_decode($all_mimes, true);
			foreach ($all_mimes as $key => $value)
				if (array_search($mime, $value) !== false) return $key;
			return false;
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
