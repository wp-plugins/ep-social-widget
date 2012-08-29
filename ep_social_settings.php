<?php

class epSocialSettings {

	function __construct() {
		// Folder for user uploaded icons
		$wp_upload_dir = wp_upload_dir();
		$this->icondir = $wp_upload_dir['basedir'].'/epsocial_icons/';
		$this->iconurl = $wp_upload_dir['baseurl'].'/epsocial_icons/';
	}

	function epsocial_panel() {
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
	?>
		<div class="wrap ep-social">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>EP Social Widget settings</h2>
			
			<?php if(!empty($_POST)) : ?>
				<?php
					if(!empty($_POST['submit'])) {
						$response = $this->epsocial_save($_POST);
					} elseif (!empty($_POST['delete'])) {
						$response = $this->epsocial_delete($_POST);
					}
				?>
				<div class="<?php echo $response['status']; ?>">
					<ul>
					<?php foreach($response['msg'] as $msg) : ?>
						<li><?php echo $msg; ?></li>
					<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			
			<?php

			?>
			
			<h2>Add new network</h2>
			<p>
				The default icon is 25x25 pixels. The upload does NOT resize your images so if you want your icons in the same size you have to resize them yourself in an application like photoshop. If you wish to have larger icons for you own added networks that is possible and your are welcome to use it.
			</p>
			<form method="post" enctype="multipart/form-data">
				<div class="form-row">
					Network name: <input type="text" name="network_name" />
					Icon: <input type="file" name="icon" />
					<input type="submit" name="submit" value="Save" />
				</div>
			</form>

			<h2>Your added networks</h2>
			<p>Icon is show with a max height of 70px, so don't be alarmed if your icon it not in ful size in the list, it will be on the site</p>
			<div id="ep-social-networks">
				<table class="widefat">
					<thead>
						<th width="20%">Network name</th>
						<th width="80%">Icon</th>
						<th></th>					
					</thead>
					<?php
						$networks = $this->get_user_networks();
						if($networks) :
							foreach($networks as $network) :
							?>
								<tr>
									<td><?php echo $network; ?></td>
									<td><img src="<?php echo $this->iconurl; ?>icon-<?php echo $network; ?>.gif" alt="<?php echo $network; ?>" style="max-height:70px"></td>
									<td>
										<div class="row-actions">
											<span class="delete">
												<form method="post">
													<input type="hidden" name="network" value="<?php echo $network; ?>">
													<input type="submit" value="delete" name="delete">
												</form>
											</span>
										</div>
									</td>
								</tr>
							<?php
							endforeach;
						else :
						?>

						<tr>
							<td>No networks added</td>
						</tr>

						<?php
						endif;
					?>
				</tabel>
			</div>
		</div>
	<?php
	}

	private function get_user_networks() {
		if(!file_exists($this->icondir)) return NULL;

		$icons = scandir($this->icondir);

		unset($icons[0]);
		unset($icons[1]);

		foreach($icons as $icon) {
			$networks[] = str_replace('icon-','',str_replace('.gif','',$icon));
		}

		return $networks;
	}

	private function epsocial_delete($data) {
		$network = $data['network'];

		if (unlink($this->icondir.'icon-'.$network.'.gif')) {
			return array(
				'status' => 'updated',
				'msg' => array(
					0 => 'Your network is deleted.'
				)
			);
		} else {
			return array(
				'status' => 'error',
				'msg' => array(
					0 => 'Could not delete the network.'
				)
			);
		}


	}
	
	private function epsocial_save($data) {

		// Clean network name
		$network = $this->get_slug($data['network_name']);

		// Icon
		$icon = $_FILES['icon'];

		// Validate if the icon is .gif and not larger then 1MB in size
		if(!preg_match('![a-z0-9\-\.\/]+\.(?:gif)!Ui' , $icon['name'])) {
			$error[] = 'Only gif images are allowed';
		} else if($icon['size'] > 1000000) {
			$error[] = 'Maximum size allowed is 1MB';
		} else {
			$filecheck = 'true';
		}

		// Check if we have any error and if so, return them and stop the script, else, continue
		if(count($error) > 0) {
			return array(
				'status' 	=> 'error',
				'msg'	=> $error
			);
			die();
		} else if($filecheck === 'true') {
			// $wp_upload_dir = wp_upload_dir();
			// $icondir = $wp_upload_dir['basedir'].'/epsocial_icons/';

			if(!is_dir($this->icondir)) {
				mkdir($this->icondir);
				chmod($this->icondir, 0755);
			} else {
				chmod($this->icondir, 0755);
			}

			$new_name = 'icon-'.$network.'.gif';
			$uploadfile = $this->icondir.basename($new_name);
			$movefile = move_uploaded_file($icon['tmp_name'],$uploadfile);
			if($movefile) {
				return array(
					'status' => 'updated',
					'msg' => array(
						0 => 'Your network is added.'
					)
				);
			}
		}
	}

	private function get_slug($str, $replace=array(), $delimiter='_') {
		setlocale(LC_ALL, 'sv_SE.UTF8');
		if(!empty($replace)) {
			$str = str_replace((array)$replace, ' ', $str);
		}

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

		return $clean;
	}
}

function epsocial_settings() {
	$settings_panel = new epSocialSettings;
	return $settings_panel->epsocial_panel();
}

function epsocial_menu() {
	add_submenu_page('options-general.php', 'EP Social Widget Settings', 'EP Social Widget', 'manage_options', 'ep-social-widget', 'epsocial_settings');
}
add_action('admin_menu','epsocial_menu');

function epsocial_admin_css() {
	wp_register_style('epsocial_css', plugins_url('css/admin.css', __FILE__));
	wp_enqueue_style('epsocial_css');
}
add_action('admin_init','epsocial_admin_css');
?>