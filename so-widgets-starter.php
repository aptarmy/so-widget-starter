<?php
/*
Plugin Name: APT widget
Plugin URL: http://google.com
Description: Widget framework build on top of Siteorigin
Author: Pakpoom Tiwaakornkit
Version: 0.1
Author URL: http://google.com
*/

/**
 * Tell Siteorigin where widgets folder is stored.
 */
function apt_add_widgets_collection($folders){
	$folders[] = plugin_dir_path(__FILE__) . 'widgets/';
	return $folders;
}
add_filter('siteorigin_widgets_widget_folders', 'apt_add_widgets_collection');


/**
 * Add APT_Widget class
 */
function apt_widget_init() {

	abstract class APT_Widget extends SiteOrigin_Widget {
		
		public $instance;
		public $widget_id;

		const SECTION_ID ='media_query';

		public function widget($args, $instance) {
			parent::widget($args, $instance);
			$this->instance = $instance;
			$this->widget_id = $args['widget_id'];
			?>
			<script>
				(function($){
					$(document).ready(function(){
						$("<?php echo $this->get_jquery_selector(); ?>").addClass("<?php echo $this->get_media_query_css_class(); ?>");
					});
				})(jQuery);
			</script>
			<?php
		}
		public function get_media_query_options() {
			$media_query = array(
				self::SECTION_ID => array(
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
			return $media_query[self::SECTION_ID];
		}
		public function get_media_query_id() {
			return self::SECTION_ID;
		}
		private function get_media_query_css_class() {
			$css_class_string = '';
			if(isset($this->instance[self::SECTION_ID])) {
				foreach ($this->instance[self::SECTION_ID] as $css_class => $value) {
					if($value && ($css_class !== 'so_field_container_state')) {
						$css_class_string .= $css_class . ' ';
					}
				}
			}
			return $css_class_string;
		}

		private function get_jquery_selector() {
			$wp_widget = $this->widget_id;
			$so_widget = str_replace('widget-', '', $this->widget_id);
			return '#' . $wp_widget . ', [id$=' . $so_widget . ']';
		}
	}
}
add_action('plugins_loaded', 'apt_widget_init');


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

function apt_widget_enqueue_script() {
	wp_enqueue_style( 'apt_widget_media_querey', plugin_dir_url(__FILE__) . 'css/media_query.css' );
}
add_action('wp_enqueue_scripts', 'apt_widget_enqueue_script');