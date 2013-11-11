<?php
/**
 * Plugin Name: A simple Widget
 * Description: A widget that displays authors name.
 * Version: 0.1
 * Author: Bilal Shaheen
 * Author URI: http://gearaffiti.com/about
 */


add_action( 'widgets_init', 'tiny_toc_widget' );


function tiny_toc_widget() {
	register_widget( 'Tiny_TOC_Widget' );
}

class Tiny_TOC_Widget extends WP_Widget {

	function Tiny_TOC_Widget() {
		$widget_ops = array( 'classname' => 'tinyTOC', 'description' => __('A widget that displays a TOC for the post', 'tinyTOC') );
		
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'tinytoc-widget' );
		
		$this->WP_Widget( 'tinytoc-widget', __('Tiny TOC Widget', 'tinyTOC'), $widget_ops, $control_ops );
	}
	
	function widget( $args, $instance ) {
		// only show widget on single pages
		if( !is_single() ) return;

		extract( $args );

		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		$min = $instance['min'];

		echo $before_widget;

		// Display the widget title 
		if ( $title )
			echo $before_title . $title . $after_title;

		list($toc, $toc_list) = tiny_toc_create($GLOBALS['posts'][0]->post_content, $instance['min']);
		print $toc_list;
		
		echo $after_widget;
	}

	//Update the widget 
	 
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		//Strip tags from title and name to remove HTML 
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['min'] = strip_tags( $new_instance['min'] );

		return $instance;
	}

	
	function form( $instance ) {

		//Set up some default widget settings.
		$defaults = array( 'title' => __('Table of Contents', 'tinyTOC'), 'min' => __('3', 'tinyTOC'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'tinyTOC'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'min' ); ?>"><?php _e('Minimum entries for TOC:', 'tinyTOC'); ?></label>
			<input id="<?php echo $this->get_field_id( 'min' ); ?>" name="<?php echo $this->get_field_name( 'min' ); ?>" value="<?php echo $instance['min']; ?>" style="width:100%;" />
		</p>

	<?php
	}
}

?>