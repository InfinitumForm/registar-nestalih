<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Widget_News') ) : class Registar_Nestalih_Widget_News extends WP_Widget {	
	function __construct() {		
		parent::__construct(
			'registar_nestalih_widget_news', // Base ID
			__('Register of Missing Persons Recent News', 'registar-nestalih'),
			[
				'description' => __( 'The most recent News from the Register of Missing Persons.', 'registar-nestalih')
			]
		);
		add_image_size( 'missing_persons_news_widget_thumb', 85, 85, true );
	}
	
	function widget($args, $instance) {
		// these are the widget options
		$title = apply_filters('widget_title', $instance['title']);
		$numberOfListings = $instance['numberOfListings'] ?? 5;
		echo $args['before_widget'];
		// Check if title is set
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		$this->display_news($numberOfListings);
		echo $args['after_widget'];
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title'] ?? '');
		$instance['numberOfListings'] = absint($new_instance['numberOfListings'] ?? 5);
		return $instance;
	}	
    
    // widget form creation
	function form($instance) {

	// Check values
	if( $instance) {
		$title = esc_attr($instance['title']);
		$numberOfListings = absint($instance['numberOfListings']);
	} else {
		$title = '';
		$numberOfListings = 5;
	}
	?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'registar-nestalih'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('numberOfListings'); ?>"><?php _e('Number of posts to show:', 'registar-nestalih'); ?></label> 
			<input class="tiny-text" id="<?php echo $this->get_field_id('numberOfListings'); ?>" name="<?php echo $this->get_field_name('numberOfListings'); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( absint($numberOfListings) ); ?>" size="3">
		</p>		 
	<?php
	}
	
	function display_news($numberOfListings) { //html
		global $post;
		add_image_size( 'missing_persons_news_widget_thumb', 85, 85, true );
		echo Registar_Nestalih_Content::render('widget-recent-news', [
			'posts_per_page' => $numberOfListings
		]);
	}
	
} endif;