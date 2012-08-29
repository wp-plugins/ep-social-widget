<?php
/*
Plugin Name: EP Social Widget
Plugin URI: http://www.earthpeople.se
Description: Very small and easy to use widget and shortcode to display social icons on your site. Facebook, Twitter, Flickr, Google Plus, Youtube, LinkedIn, DeviantArt, Meetup, MySpace, Soundcloud, Bandcamp and RSS feed
Author: Mattias Hedman
Version: 1.0.1
Author URI: http://www.earthpeople.se
*/

// ====================
// = Plugin shortcode =
// ====================

function epsw_shortcode($args){
	// User uploaded icon url
	$wp_upload_dir = wp_upload_dir();
	$iconurl = $wp_upload_dir['baseurl'].'/epsocial_icons/';
	$icondir = $wp_upload_dir['basedir'].'/epsocial_icons/';

	// Plugin path
	$plugin_path = WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.str_replace(basename(__FILE__),"",plugin_basename(__FILE__));

	$html = '<ul class="ep_social_widget" id="epSW_shortcode">';
	foreach($args as $network => $link) {
		if($network === 'rss') {
			if($link === '1') {
				$html .= '<li>';
					$html .= '<a href="'.get_bloginfo("rss2_url").'" target="_blank"><img src="'.plugins_url("icons/icon-rss.gif", __FILE__).'" alt="" /></a>';
				$html .= '</li>';
			}
		} else {
			$pattern1 = '/^http:\/\//';
			$pattern2 = '/^https:\/\//';
			
			$l = strip_tags($link);		
			if(preg_match($pattern1, $l) || preg_match($pattern2, $l)){
				$link = $l;
			} else {
				$link = 'http://'.$l;
			}


			$html .= '<li>';

			if(file_exists($plugin_path."/icons/icon-".$network.".gif")) {
				$html .= '<a href="'.$link.'" target="_blank"><img src="'.plugins_url("icons/icon-".$network.".gif", __FILE__).'" alt="" /></a>';
			} elseif (file_exists($icondir."icon-".$network.".gif")) {
				$html .= '<a href="'.$link.'" target="_blank"><img src="'.$iconurl.'icon-'.$network.'.gif" alt="" /></a>';
			}

			$html .= '</li>';

		}
	}
	$html .= '</ul>';

	return $html;
}
add_shortcode('ep-social-widget', 'epsw_shortcode');


// =================
// = Plugin widget =
// =================
// Load stylesheet and widget
add_action('wp_head','epSocialWidgetCss');
add_action('widgets_init','load_epSocialWidget');

// Register the widget
function load_epSocialWidget() {
	register_widget('epSocialWidget');
}

// Widget stylesheet
function epSocialWidgetCss() {
	echo '<link href="'.plugins_url('style.css', __FILE__).'" type="text/css" rel="stylesheet" media="screen" />';
}

class epSocialWidget extends WP_Widget{

	function epSocialWidget() {
		//Settings
		$widget_ops = array('classname'=>'epsocialwidget','description'=>__('Display social icons on your site.','epsocialwidget'));
		
		//Controll settings
		$control_ops = array('id_base' => 'epsocialwidget');
		
		//Create widget
		$this->WP_Widget('epsocialwidget',__('EP Social Widget'),$widget_ops,$control_ops);
		
	}
	
	// Widget frontend
	function widget($args,$instance) {
		extract($args);

		// User uploaded icon url
		$wp_upload_dir = wp_upload_dir();
		$this->iconurl = $wp_upload_dir['baseurl'].'/epsocial_icons/';
		$this->icondir = $wp_upload_dir['basedir'].'/epsocial_icons/';

		// Plugin path
		$this->plugin_path = WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.str_replace(basename(__FILE__),"",plugin_basename(__FILE__));
		
		//User selected settings
		$title = $instance['title'];
		unset($instance['title']);
		
		echo $before_widget;
		?>
		
		<div class="ep_social_widget">
			
			<?php echo $before_title . $title . $after_title; ?>

			<?php
				foreach($instance as $network => $link) {
					if($network === 'rss') {
						if($link === '1') {
							echo '<a href="'.get_bloginfo("rss2_url").'" target="_blank"><img src="'.plugins_url("icons/icon-rss.gif", __FILE__).'" alt="" /></a>';
						}
					} else {
						if(file_exists($this->plugin_path."/icons/icon-".$network.".gif")) {
							echo '<a href="'.$link.'" target="_blank"><img src="'.plugins_url("icons/icon-".$network.".gif", __FILE__).'" alt="" /></a>';
						} elseif (file_exists($this->icondir."icon-".$network.".gif")) {
							echo '<a href="'.$link.'" target="_blank"><img src="'.$this->iconurl.'icon-'.$network.'.gif" alt="" /></a>';
						}
					}
				}
			?>
		</div>
		
		<?php
		echo $after_widget;
	}
	
	// Widget update
	function update($new_instance,$instance) {
		$pattern1 = '/^http:\/\//'; //
		$pattern2 = '/^https:\/\//';
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['rss'] = strip_tags($new_instance['rss']);

		unset($new_instance['title']);
		unset($new_instance['rss']);

		foreach($new_instance as $key => $new) {
			if($new) {
				$link = strip_tags($new);
				if(preg_match($pattern1,$link) || preg_match($pattern2,$link)) {
					$instance[$key] = $link;
				} else {
					$instance[$key] = 'http://'.$link;
				}
			}
		}
		
		return $instance;
	}

	// Widget backend
	function form($instance) {
		$default = array(
			'title' 		=> '',
			'twitter'		=> '',
			'facebook' 	=> '',
			'flickr' 		=> '',
			'gplus' 		=> '',
			'youtube' 	=> '',
			'linkedin' 	=> '',
			'deviantart' 	=> '',
			'meetup' 		=> '',
			'myspace' 	=> '',
			'bandcamp' 	=> '',
			'soundcloud' 	=> ''
		);
		$instance = wp_parse_args((array)$instance,$default);

		// Check for user added networks
		$wp_upload_dir = wp_upload_dir();
		$this->icondir = $wp_upload_dir['basedir'].'/epsocial_icons/';
		$icons = scandir($this->icondir);

		// Plugin path
		$this->plugin_path = WP_PLUGIN_DIR.DIRECTORY_SEPARATOR.str_replace(basename(__FILE__),"",plugin_basename(__FILE__));

		unset($icons[0]);
		unset($icons[1]);

		foreach($icons as $icon) {
			$networks[] = str_replace('icon-','',str_replace('.gif','',$icon));
		}
	?>
		<!-- TITLE -->
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" class="widefat" />
		</p>
		
		<!-- RSS -->
		<p>
			<label for="<?php echo $this->get_field_id('rss'); ?>"><?php echo __('Display rss link:'); ?></label>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" id="<?php echo $this->get_field_id('rss'); ?>" name="<?php echo $this->get_field_name('rss'); ?>" <?php if($instance['rss'] == 1): ?> checked="checked" <?php endif; ?> value="1" /> <?php echo __('Yes'); ?>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" id="<?php echo $this->get_field_id('rss'); ?>" name="<?php echo $this->get_field_name('rss'); ?>" <?php if($instance['rss'] == 0): ?> checked="checked" <?php endif; ?> value="0" /> <?php echo __('No'); ?>
		</p>

		<?php if($networks) : ?>
			<h4>User added networks</h4>
			<?php
			foreach($networks as $network) :
			?>
				<p>
					<label for="<?php echo $this->get_field_id($network); ?>"><?php echo __($network.' profile link:'); ?></label>
					<br />
					<input type="text" id="<?php echo $this->get_field_id($network); ?>" name="<?php echo $this->get_field_name($network); ?>" value="<?php echo $instance[$network]; ?>" class="widefat" />
				</p>
			<?php
			unset($instance[$network]);
			endforeach;
		endif;

		unset($instance['title']);
		unset($instance['rss']);
		unset($instance['0']);
		?>

		<h4>Default networks</h4>

		<?php
		foreach($instance as $network => $value) :
			if(file_exists($this->plugin_path."/icons/icon-".$network.".gif")) :
			?>
			<p>
				<label for="<?php echo $this->get_field_id($network); ?>"><?php echo __($network.' profile link:'); ?></label>
				<br />
				<input type="text" id="<?php echo $this->get_field_id($network); ?>" name="<?php echo $this->get_field_name($network); ?>" value="<?php echo $value; ?>" class="widefat" />
			</p>
			<?php
			endif;
		endforeach;
	}
}


// ========================
// = Plugin settings page =
// ========================

include('ep_social_settings.php');

?>