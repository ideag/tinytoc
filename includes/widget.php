<?php
/* Bilal Shaheen | http://gearaffiti.com/about */

class TinyTOC_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'tinytoc tiny_toc', 'description' => __('A widget that displays a TOC for the post', 'tinytoc') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'tinytoc-widget' );
		parent::__construct( 'tinytoc-widget', __('Tiny TOC Widget', 'tinytoc'), $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		// only show widget on single pages
		if( !is_singular() ) return;
		$content = apply_filters('tinytoc_widget_content', $GLOBALS['posts'][0]->post_content );
		$min = $instance['min'];
		$toc = tinyTOC::create($content, $min);
		if ( !$toc ) {
			return;
		}
		extract( $args );
		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		// Display the widget title
		if ( $title )
			echo $before_title . $title . $after_title;
		echo $toc;
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
		$defaults = array( 'title' => __('Table of Contents', 'tinytoc'), 'min' => tinyTOC::$options['general_min']);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'tinytoc'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'min' ); ?>"><?php _e('Minimum entries for TOC:', 'tinytoc'); ?></label>
			<input id="<?php echo $this->get_field_id( 'min' ); ?>" name="<?php echo $this->get_field_name( 'min' ); ?>" value="<?php echo $instance['min']; ?>" style="width:100%;" />
		</p>
	<?php
	}
}
?>