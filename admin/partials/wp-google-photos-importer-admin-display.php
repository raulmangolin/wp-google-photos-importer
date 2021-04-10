<?php
	/**
	 * Provide a admin area view for the plugin
	 *
	 * This file is used to markup the admin-facing aspects of the plugin.
	 *
	 * @link       https://github.com/raulmangolin/WP-Google-Photos-Importer
	 * @since      1.0.0
	 *
	 * @package    Wp_Google_Photos_Importer
	 * @subpackage Wp_Google_Photos_Importer/admin/partials
	 */
	
	$clientId = get_option('wp-google-photos-importer-google-clientId');
	$clientSecret = get_option('wp-google-photos-importer-google-clientSecret');
	$clientToken = get_option('wp-google-photos-importer-google-token');
	
	$client = new Google\Client();
	$client->setClientId($clientId);
	$client->setClientSecret($clientSecret);
	
	$client->addScope(Google_Service_PhotosLibrary::PHOTOSLIBRARY);
	$service = new Google_Service_PhotosLibrary($client);
	
	$redirect_uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
	$redirect_uri .= "://$_SERVER[HTTP_HOST]$_SERVER[PHP_SELF]";
	$redirect_uri .= "?page={$_GET['page']}";
	
	$client->setRedirectUri($redirect_uri);
	
	$authUrl = $client->createAuthUrl();
	
	if (isset($_GET['code'])) {
		$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
		
		if (isset($token['access_token'])) {
			update_option('wp-google-photos-importer-google-token', $token['access_token']);
			update_option('wp-google-photos-importer-google-token-expires', $token['expires_in']);
			
			$clientToken = $token['access_token'];
			
			echo '<script>window.top.location.href = "' . $redirect_uri . '";</script>';
			exit();
		} else {
			update_option('wp-google-photos-importer-google-token', false);
			$expires_in = get_option('wp-google-photos-importer-google-token-expires');
			if (!$clientToken || time() >= $expires_in) {
				$clientToken = null;
			}
		}
	} else {
		$clientToken = get_option('wp-google-photos-importer-google-token');
	}
?>

<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
	
	<div class="card card-big">
		<h2 class="title">1. Configure your Google Photos API tokens</h2>
		
		<p class="description">
			<a href="https://developers.google.com/photos/library/guides/get-started" target="_blank">Click here</a> to
			generate your Google Photos API tokens.
		</p>
		
		<br>
		<p class="description">On Google API Console, set your <strong>Authorized redirect URI</strong> as:
			<code><?= $redirect_uri; ?></code></p>
		
		<form method="post" action="<?php echo esc_html(admin_url('admin-post.php')); ?>" autocomplete="off">
			<input type="hidden" name="action" value="admin_plugin_setup_menu_save_ids">
			
			<table class="form-table" role="presentation">
				<tbody>
				<tr>
					<th scope="row">
						<label for="google-clientId">Client ID</label>
					</th>
					<td>
						<input type="text" name="google-clientId" id="google-clientId" class="regular-text"
									 value="<?= $clientId; ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="google-clientSecret">Client Secret</label>
					</th>
					<td>
						<input type="password" name="google-clientSecret" id="google-clientSecret" class="regular-text"
									 autocomplete="new-password" value="<?= $clientSecret; ?>">
					</td>
				</tr>
				</tbody>
			</table>
			
			<?php
				wp_nonce_field('wp-google-photos-importer-settings-save', 'wp-google-photos-importer-custom-message');
				submit_button();
			?>
		</form>
	</div>
	
	<?php if ($clientId && $clientSecret): ?>
		<div class="card card-big">
			<h2 class="title">2. Connect with your Google Account</h2>
			
			<p>To list your Google Photos albums we need permissions to read it. So, click on the button bellow:</p>
			
			<a href="<?= $authUrl; ?>" id="login-google1">
				<img src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/btn-google-dark.png'; ?>"
						 srcset="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/btn-google-dark@2x.png'; ?> 2x"
						 alt="Login with Google">
			</a>
		</div>
	<?php endif; ?>
	
	<?php if ($clientId && $clientSecret && $clientToken): ?>
		<div class="card card-big">
			<h2 class="title">3. Select your album</h2>
			
			<p>Select bellow the album that you want to import</p>
			
			<input type="hidden" name="wp-gpi-clientToken" id="wp-gpi-clientToken" value="<?= $clientToken; ?>">
			
			<div class="container">
				<div class="content">
					<ul id="wp-gpi-albums" class="wp-gpi-albums"></ul>
					
					<div class="wp-gpi-loadmore" id="wp-gpi-loadmore">Load more albums</div>
				</div>
			</div>
		</div>
		
		<div class="card card-big">
			<h2 class="title">4. Select your photos</h2>
			
			<p>Select all photos that you want to import to your Wordpress media gallery</p>
			
			<div class="container">
				<div class="content">
					<ul id="wp-gpi-albums-photos" class="wp-gpi-albums"></ul>
					
					<div class="wp-gpi-loadmore" id="wp-gpi-loadmore-photos">Load more photos</div>
				</div>
			</div>
		</div>
		
		<div class="card card-big">
			<h2 class="title">5. Import your photos</h2>
			
			<p>You're importing <strong><span class="wp-gpi-total-import">0</span></strong> photos from Google Photos</p>
			
			<div class="container">
				<div class="content">
					<div class="wp-gpi-loadmore" id="wp-gpi-import-photos">Import Photos</div>
					
					<ul class="wp-gpi-importing-list"></ul>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>


<div class="wp-gpi-loading">Loading&#8230;</div>