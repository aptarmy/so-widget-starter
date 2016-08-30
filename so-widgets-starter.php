<?php
/*
Plugin Name: My Test Plugin
Plugin URL: http://google.com
Description: This widget will be shown on Site Origin admin page
Author: Pakpoom Tiwaakornkit
Version: 0.1
Author URL: http://google.com
*/

/**
 * Tell Siteorigin where widgets folder is stored
 */
function apt_add_widgets_collection($folders){
	$folders[] = plugin_dir_path(__FILE__) . 'widgets/';
	return $folders;
}
add_filter('siteorigin_widgets_widget_folders', 'apt_add_widgets_collection');

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
			$current_url =  plugin_dir_url( __FILE__ ) . 'layouts/' . $folder . '/';
			
			$matches = glob($current_path . '*.json');
			$json_file_path = $matches[0];
			$matches = glob($current_path . 'screenshot.*');
			$screenshot_file_url = plugin_dir_url($matches[0]) . basename($matches[0]);

			$json_file_content = file_get_contents($json_file_path);
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
 * Add APT_Widget class
 */
function apt_widget_init() {

	abstract class APT_Widget extends SiteOrigin_Widget {
		
		public $instance;
		public $widget_id;

		public static $media_query_section_id = 'media_query';
		public static $float_section_id = 'float';

		public function widget($args, $instance) {
			parent::widget($args, $instance);
			if ($args['widget_id'] == "") { return false; }
			$this->instance = $instance;
			$this->widget_id = $args['widget_id'];
			?>
			<script>
				jQuery("<?php echo $this->get_jquery_selector(); ?>")
					.addClass("<?php echo $this->get_media_query_css_class(); ?>")
					.css({"float": "<?php echo $instance[self::$float_section_id]; ?>"});
			</script>
			<?php
		}
		private function get_jquery_selector() {
			$wp_widget = $this->widget_id;
			$so_widget = str_replace('widget-', '', $this->widget_id);
			return '#' . $wp_widget . ', .so-panel[id$=' . $so_widget . ']';
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
					'label' => __( 'Hide your widget on specified screen width.' , 'textdomain' ),
					'hide' => true,
					'fields' => array(
						'hidden_xs' => array(
							'type' => 'checkbox',
							'label' => __( 'Hide this widget on screen width < 781px', 'textdomain' )
						),
						'hidden_sm' => array(
							'type' => 'checkbox',
							'label' => __( 'Hide this widget on screen width > 780px and < 992px', 'textdomain' )
						),
						'hidden_md' => array(
							'type' => 'checkbox',
							'label' => __( 'Hide this widget on screen width > 991px and < 1200px', 'textdomain' )
						),
						'hidden_lg' => array(
							'type' => 'checkbox',
							'label' => __( 'Hide this widget on screen width > 1199', 'textdomain' )
						),
					),
				)
			);
			return $media_query[self::$media_query_section_id];
		}
		protected function get_media_query_id() {
			return self::$media_query_section_id;
		}
		private function get_media_query_css_class() {
			$css_class_string = '';
			if(isset($this->instance[self::$media_query_section_id])) {
				foreach ($this->instance[self::$media_query_section_id] as $css_class => $value) {
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
				self::$float_section_id => array(
			        'type' => 'radio',
			        'label' => __( 'Float this widget', 'textdomain' ),
			        'default' => 'none',
			        'options' => array(
			            'none' => __( 'None', 'textdomain' ),
			            'left' => __( 'Left', 'textdomain' ),
			            'right' => __( 'Right', 'textdomain' )
			        )
			    )
			);
			return $float_options[self::$float_section_id];
		}
		protected function get_float_id() {
			return self::$float_section_id;
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
			'label' => __('description.', 'textdomain'),
			'options' => $this->get_all_menus()
		)

	* to show menu in template file, use this code

		wp_nav_menu(array(
			'menu' => $instance['menu']
		));
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
			foreach($all_menus_obj as $menu){
				$all_menu[$menu->slug] = $menu->name;
			}
			return $all_menu;
		}
	}
}
add_action('plugins_loaded', 'apt_widget_init');

function apt_widget_enqueue_script() {
	wp_enqueue_style( 'apt_widget_media_querey', plugin_dir_url(__FILE__) . 'css/media_query.css' );
}
add_action('wp_enqueue_scripts', 'apt_widget_enqueue_script');
