<?php

/*
Widget Name: APT Blueprint
Description: This widget is intended to be overrided
Author: Me
Author URI: http://example.com
Widget URI: http://example.com/hello-world-widget-docs,
Video URI: http://example.com/hello-world-widget-video
*/

// this class will be used to register a widget
// siteorigin_wiget class was extended from WP_widget class
// so we can use constructor available in WP_widge class
class APT_Blueprint extends APT_Widget {
	
	function __construct() {
		//Here you can do any preparation required before calling the parent constructor, such as including additional files or initializing variables.

		//Call the parent constructor with the required arguments.
		parent::__construct(
			// The unique id for your widget.
			'apt_blueprint',

			// The name of the widget for display purposes.
			__('APT Blueprint', 'textdomain'),

			// The $widget_options array, which is passed through to WP_Widget.
			// It has a couple of extras like the optional help URL, which should link to your sites help or support page.
			array(
				'description' => __('This widget is intended to be overrided', 'textdomain'),
				'help'        => 'http://example.com/hello-world-widget-docs',
				'panels_groups' => array('apt_widgets'),
			),

			//The $control_options array, which is passed through to WP_Widget
			array(
			),

			//The $form_options array, which describes the form fields used to configure SiteOrigin widgets. We'll explain these in more detail later.
			array(
				'text' => array(
					'type' => 'text',
					'label' => __('text.', 'textdomain'),
					'default' => 'default text'
				),
				'background_color' => array(
					'type' => 'color',
					'label' => __('background color.', 'textdomain'),
					'default' => '#f1f1f1'
				),
				$this->get_float_id() => $this->get_float_options(),
				$this->get_media_query_id() => $this->get_media_query_options(),
			),

			//The $base_folder path string.
			plugin_dir_path(__FILE__)
		);
	}

	/* Get template file */
    function get_template_name($instance) {
        return 'template';
    }

	/* Get less file */
    function get_style_name($instance) {
        return 'style';
    }
	
	/* set less variable */
	function get_less_variables($instance){
		$less_vars = array();
		if(isset($instance["background_color"])) $less_vars["background_color"] = $instance["background_color"];
		return $less_vars;
	}
}
// siteorigin_widget_register($desired_widget_id, $path_to_widget, $class_used_to_create_widget)
siteorigin_widget_register('apt_blueprint', __FILE__, 'APT_Blueprint');

/**
 * Widget Image profile
 */
function apt_so_widget_blueprint_image_profile( $banner_url, $widget_meta ) {
    if( $widget_meta['ID'] == 'apt_blueprint') {
        $banner_url = theme_dir_url(__FILE__) . 'widget-image-profile.svg';
    }
    return $banner_url;
}
add_filter( 'siteorigin_widgets_widget_banner', 'apt_so_widget_blueprint_image_profile', 10, 2);