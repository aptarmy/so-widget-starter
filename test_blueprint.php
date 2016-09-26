<?php
/*
Plugin Name: test blueprint
Description: just for testing
Version: 0.0.1
Author: Arm
License: GPLv2 or later
*/
/**
 * Actvie all theme-defined widgets
 */
function apt_widgets_active_widgets($active_widgets) {
	$active_widgets['apt_accordion_menu'] = true;
	$active_widgets['apt_big_post_banner'] = true;
	$active_widgets['apt_main_menu'] = true;
	$active_widgets['apt_mmenu'] = true;
	$active_widgets['apt_posts_thumbnail'] = true;
	$active_widgets['apt_running_text'] = true;
	$active_widgets['apt_search'] = true;
	$active_widgets['apt_site_branding'] = true;
	$active_widgets['apt_wc_cart_icon'] = true;
	$active_widgets['apt_wood_menu'] = true;
	return $active_widgets;
}
add_filter('siteorigin_widgets_active_widgets', 'apt_widgets_active_widgets');

/**
 * Add new widget grouop in widget dialog
 */
function apt_add_widget_tabs($tabs) {
    $tabs[] = array(
        'title' => __('Theme\'s widgets', 'aptnews'),
        'filter' => array(
            'groups' => array('apt_widgets')
        )
    );

    return $tabs;
}
add_filter('siteorigin_panels_widget_dialog_tabs', 'apt_add_widget_tabs', 20);

/**
 * Tell Siteorigin where widgets folder is stored
 */
function apt_add_widgets_collection($folders){
	$folders[] = plugin_dir_path(__FILE__) . 'widgets/';
	return $folders;
}
add_filter('siteorigin_widgets_widget_folders', 'apt_add_widgets_collection');

// Setup wp_filesystem api
require_once ABSPATH . 'wp-admin/includes/file.php';
if( !WP_Filesystem() ) {
	return;
}
/**
 * it works the samwe way as plugin_dir_url but it's intended to be used in theme
 */
if ( !function_exists("theme_dir_url") ) :
	function theme_dir_url($file) {
		$theme_dir_url = "";
		if (is_string($file) && $file !== "") {
			// $file /home/apt/public/wp/wp-content/theme/seed/inc/some-folder/some-file.php
			$dirname = wp_normalize_path(trailingslashit(dirname($file))); // /home/apt/public/wp/wp-content/theme/seed/inc/some-folder/
			$template_path = wp_normalize_path(get_template_directory()); // /home/apt/public/wp/wp-content/theme/seed
			$template_uri = get_template_directory_uri(); // http://www.example.com/wp-content/theme/seed
			$theme_dir_url = str_replace($template_path, '', $dirname); // /inc/some-folder/
			$theme_dir_url = $template_uri . $theme_dir_url; // http://www.example.com/wp-content/theme/seed/inc/some-folder/
			$theme_dir_url = set_url_scheme($theme_dir_url);
		}
		return $theme_dir_url;
	}
endif;
/**
 * Automatically adding prerbuild layout to siteorigin editor. All layout folders should be stored in 'layouts' folder
 * In 'layouts' folder, A layout folder should be structured like this.
 *  [Folder Name] folder name will be used as a layout name
 * 		|
 *   	+---- [any-file-name.json] This file should look like this.
 *  	|  		{
		|	        'widgets' : [...],
		|	        'grids' : [...],
		|	        'grid_cells' : [...],
		|        }
 *  	+---- [screenshot.png|jpg|gif] This image file should be named screenshot. The file extension can be any format like .jpg .png etc.
 *  		
 */
function apt_prebuild_layout($layouts) {
	
	$folders = scandir(plugin_dir_path( __FILE__ ) . 'layouts' );
	
	foreach ($folders as $folder) {
		
		if(!($folder == '.' || $folder == '..')) {
			
			$current_path = plugin_dir_path( __FILE__ ) . 'layouts/' . $folder . '/';
			$current_url =  theme_dir_url( __FILE__ ) . 'layouts/' . $folder . '/';
			
			$matches = glob($current_path . '*.json');
			$json_file_path = $matches[0];
			$matches = glob($current_path . 'screenshot.*');
			$screenshot_file_url = theme_dir_url($matches[0]) . basename($matches[0]);

			$json_file_content = $GLOBALS['wp_filesystem']->get_contents($json_file_path);
			$so_prebuild_layout = json_decode($json_file_content, true);
			
			$so_prebuild_layout['screenshot'] = $screenshot_file_url;
			$so_prebuild_layout['name'] = $folder;

			$layouts['apt_' . $folder] = $so_prebuild_layout;
		}
	}
	return $layouts;
}
add_filter('siteorigin_panels_prebuilt_layouts','apt_prebuild_layout');

/**
 * Fix Siteorigin import layout error
 */
add_filter('siteorigin_panels_css_row_margin_bottom', 'apt_siteorigin_panels_css_row_margin_bottom', 5);
function apt_siteorigin_panels_css_row_margin_bottom() {
	return "0px";
}

/**
 * Add APT_Widget class
 */
function apt_widget_init() {

	/**
	 * As this class depends on Siteorigin widget bundle
	 * wew have to check if users have already installed Siteorigin widget bundle
	 * before we do the other thing
	 */
	if (!class_exists('SiteOrigin_Widget')) {
		return;
	}

	abstract class APT_Widget extends SiteOrigin_Widget {

		public $widget_id;

		public static $media_query_section_id = 'media_query_section';
		public static $float_section_id = 'float_section';
		public static $float_id = 'float';

		public function widget($args, $instance) {
			parent::widget($args, $instance);
			$this->add_widget_classes($instance);
		}
		function add_widget_classes($instance) {
			?>
				<script type="text/javascript">
					(function($){
						var $current_widget = $(".so-widget-<?php echo $this->id_base; ?>:last");
						$current_widget_wrapper = $current_widget.closest(".so-panel, .widget");
						<?php if(trim( $this->get_media_query_css_class($instance) ) ) : ?>
							$current_widget_wrapper.addClass("<?php echo $this->get_media_query_css_class($instance); ?>");
						<?php endif; ?>
						<?php if( $this->get_float_class($instance) ) : ?>
							$current_widget_wrapper.addClass("<?php echo $this->get_float_class($instance); ?>");
						<?php endif; ?>
					})(jQuery);
				</script>
			<?php
		}

		//
		// ================= MEDIA QUERY ====================
		// to use this option in a widget, add this array member to $form_options array
		// $this->get_media_query_id() => $this->get_media_query_options()
		// 
		protected function get_media_query_options() {
			$media_query = array(
				self::$media_query_section_id => array(
					'type' => 'section',
					'label' => __( 'Hide your widget on specified screen width.' , 'aptnews' ),
					'hide' => true,
					'fields' => array(
						'hidden_xs' => array(
							'type' => 'checkbox',
							'label' => __( 'Hide this widget on screen width < 781px', 'aptnews' )
						),
						'hidden_sm' => array(
							'type' => 'checkbox',
							'label' => __( 'Hide this widget on screen width > 780px and < 992px', 'aptnews' )
						),
						'hidden_md' => array(
							'type' => 'checkbox',
							'label' => __( 'Hide this widget on screen width > 991px and < 1200px', 'aptnews' )
						),
						'hidden_lg' => array(
							'type' => 'checkbox',
							'label' => __( 'Hide this widget on screen width > 1199', 'aptnews' )
						),
					),
				)
			);
			return $media_query[self::$media_query_section_id];
		}
		protected function get_media_query_id() {
			return self::$media_query_section_id;
		}
		private function get_media_query_css_class($instance) {
			$css_class_string = '';
			if(isset($instance[self::$media_query_section_id])) {
				foreach ($instance[self::$media_query_section_id] as $css_class => $value) {
					if($value && ($css_class !== 'so_field_container_state')) {
						$css_class_string .= $css_class . ' ';
					}
				}
			}
			return $css_class_string;
		}

		//
		// ==================== FLOAT =====================
		// to use this option in a widget, add this array member to $form_options array
		// $this->get_float_id() => $this->get_float_options()
		// 
		protected function get_float_options() {
			$float_options = array(
				'type' => 'section',
				'label' => __( 'Float this widget', 'aptnews' ),
				'hide' => true,
				'fields' => array(
					self::$float_id => array (
						'type' => 'radio',
						'default' => 'float_none',
						'options' => array(
							'float_none' => __( 'None', 'aptnews' ),
							'float_left' => __( 'Left', 'aptnews' ),
							'float_right' => __( 'Right', 'aptnews' )
						)
					)
				)
			);
			return $float_options;
		}
		protected function get_float_id() {
			return self::$float_section_id;
		}
		private function get_float_class($instance) {
			$float_class = isset ($instance[self::$float_section_id][self::$float_id]) ? $instance[self::$float_section_id][self::$float_id] : '';
			return $float_class;
		}
	}

	/**
	* APT menu widget. It add menu feild to widget form.
	* this class extends APT_Widget class
	* that means all widgets that extends this class will have responsive options
	* in widget form by default.
	* 
	* to use this in form copy and paste this code
	* in $form_options array.
		
		'menu' => array(
			'type' => 'select',
			'label' => __('description.', 'aptnews'),
			'default' => 'not_selected',
			'options' => $this->get_all_menus()
		)

	* to show menu in template file, use this code

		wp_nav_menu(array(
			'menu' => $instance['menu']
		));
	
	* to check if user select menu or not use this code
		
		if($instance['menu'] !== "not_selected") { ... }

	* 
	*/
	abstract class APT_Widget_Menu extends APT_Widget {
		
		/**
		 * Get all menu created in wordpress.
		 * @return array key is menu slug. value is menu name.
		 */
		protected function get_all_menus(){
			// Get all menus crerated by users
			$all_menus_obj = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
			$all_menu = array();
			$all_menu["not_selected"] = "-- Select Menu --";
			foreach($all_menus_obj as $menu){
				$all_menu[$menu->slug] = $menu->name;
			}
			return $all_menu;
		}
	}
}
add_action('after_setup_theme', 'apt_widget_init', 10);

function apt_widget_enqueue_script() {
	wp_enqueue_style( 'apt_widget', theme_dir_url(__FILE__) . 'css/apt_widget.css' );
}
add_action('wp_enqueue_scripts', 'apt_widget_enqueue_script');
