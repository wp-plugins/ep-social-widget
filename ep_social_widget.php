<?php
/*
Plugin Name: EP Social Widget
Plugin URI: http://earthpeople.se/labs/2012/01/ep-social-widget-tiny-social-widget-for-wordpress/
Description: Very small and easy to use widget to display social icons on your site. Facebook, twitter, flicker and RSS feed
Author: Mattias Hedman, Earth People AB
Version: 0.1.1
Author URI: http://www.earthpeople.se
*/



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
		
		//User selected settings
		$title = $instance['title'];
		$facebook= $instance['facebook'];
		$twitter = $instance['twitter'];
		$flickr = $instance['flickr'];
		$rss = $instance['rss'];
		
		echo $before_widget;
		?>
		
		<div class="ep_social_widget">
			
			<?php echo $before_title . $title . $after_title; ?>
			
			<?php if($rss == 1) : ?>
			<a href="<?php bloginfo('rss2_url'); ?>" target="_blank"><img src="<?php echo plugins_url('icon-rss.gif', __FILE__); ?>" alt="" /></a>
			<?php endif; ?>
			
			<?php if($twitter) : ?>
			<a href="<?php echo $twitter; ?>" target="_blank"><img src="<?php echo plugins_url('icon-twitter.gif', __FILE__); ?>" alt="" /></a>
			<?php endif; ?>
			
			<?php if($facebook) : ?>
			<a href="<?php echo $facebook; ?>" target="_blank"><img src="<?php echo plugins_url('icon-facebook.gif', __FILE__); ?>" alt=""/></a>
			<?php endif; ?>
			
			<?php if($flickr) : ?>
			<a href="<?php echo $flickr; ?>" target="_blank"><img src="<?php echo plugins_url('icon-flickr.gif', __FILE__); ?>" alt="" /></a>
			<?php endif; ?>
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
		
		if(!empty($new_instance['twitter'])) {
			$tw = strip_tags($new_instance['twitter']);		
			if(preg_match($pattern1, $tw) || preg_match($pattern2, $tw)){
				$instance['twitter'] = $tw;
			} else {
				$instance['twitter'] = 'http://'.$tw;
			}
		} else {
			$instance['twitter'] = '';	
		}
		
		if(!empty($new_instance['facebook'])) {
			$fb = strip_tags($new_instance['facebook']);		
			if(preg_match($pattern1, $fb) || preg_match($pattern2, $fb)){
				$instance['facebook'] = $fb;
			} else {
				$instance['facebook'] = 'http://'.$fb;
			}
		} else {
			$instance['facebook'] = '';
		}
		
		if(!empty($new_instance['flickr'])) {
			$fl = strip_tags($new_instance['flickr']);		
			if(preg_match($pattern1, $fl) || preg_match($pattern2, $fl)){
				$instance['flickr'] = $fl;
			} else {
				$instance['flickr'] = 'http://'.$fl;
			}		
		} else {
			$instance['flickr'] = '';
		}
		
		return $instance;
	}

	// Widget backend
	function form($instance) {
		$default = array('title' =>'', 'twitter'=>'','facebook'=>'','flickr'=>'','rss'=>'');
		$instance = wp_parse_args((array)$instance,$default);
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
		
		<!-- Twitter -->
		<p>
			<label for="<?php echo $this->get_field_id('twitter'); ?>"><?php echo __('Twitter link:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" value="<?php echo $instance['twitter']; ?>" class="widefat" />
		</p>
		
		<!-- Facebook -->
		<p>
			<label for="<?php echo $this->get_field_id('facebook'); ?>"><?php echo __('Facebook link:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('facebook'); ?>" name="<?php echo $this->get_field_name('facebook'); ?>" value="<?php echo $instance['facebook']; ?>" class="widefat" />
		</p>
		
		<!-- Flickr -->
		<p>
			<label for="<?php echo $this->get_field_id('flickr'); ?>"><?php echo __('Flickr link:'); ?></label>
			<br />
			<input type="text" id="<?php echo $this->get_field_id('flickr'); ?>" name="<?php echo $this->get_field_name('flickr'); ?>" value="<?php echo $instance['flickr']; ?>" class="widefat" />
		</p>
	
	<?php
	
	}

}


?>