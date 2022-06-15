<?php

if ( ! defined( 'WPINC' ) ) { die( "Don't mess with us." ); }
if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists('Registar_Nestalih_Widget_Latest_Missing') ) : class Registar_Nestalih_Widget_Latest_Missing extends WP_Widget {	
	// The construct part
	function __construct() {
		parent::__construct(
			// Base ID of your widget
			'registar_nestalih_widget_latest_missing', 
			// Widget name will appear in UI
			__('Missing People (recently added)', 'registar-nestalih'), 
			// Widget description
			[
				'description' => __('Show recently added missing people', 'registar-nestalih'),
			]
		);
	}
	 
	// Creating widget front-end
	public function widget( $args, $instance ) {
		$title = $instance['title'] ?? NULL;
		
		$response = Registar_Nestalih_API::get( [
			'paginate'	=> 'true',
			'per_page'	=> absint($instance['per_page'] ?? 5)
		] );
		
		wp_enqueue_style( 'registar-nestalih' );
		wp_enqueue_script( 'registar-nestalih' );

		echo $args['before_widget'];
		
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		$response->widget = $instance;
		
		echo Registar_Nestalih_Content::render('sidebar', $response);
		
		echo $args['after_widget'];
	}
	 
	// Creating widget Backend
	public function form( $instance ) {
		$title = $instance[ 'title' ] ?? __( 'Missing Persons', 'registar-nestalih' );
		$per_page = $instance[ 'per_page' ] ?? 5;
		$see_others = $instance[ 'see_others' ] ?? 1;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_html_e( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'per_page' ) ); ?>"><?php _e( 'Number of posts to show:', 'registar-nestalih' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'per_page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'per_page' ) ); ?>" type="number" step="1" min="1"  value="<?php echo esc_attr( $per_page ); ?>" size="3">
		</p>
		<p>
			<input class="checkbox " id="<?php echo esc_attr( $this->get_field_id( 'see_others' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'see_others' ) ); ?>" type="checkbox" value="1" <?php checked($see_others, 1); ?>>
			<label for="<?php echo esc_attr( $this->get_field_id( 'see_others' ) ); ?>"><?php _e( 'Display "look at the others" link.', 'registar-nestalih' ); ?></label>
		</p>
		<?php
	}
	 
	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = [];
		$instance['title'] = ( ( ! empty( $new_instance['title'] ?? NULL ) ) ? strip_tags( $new_instance['title'] ) : '' );
		$instance['per_page'] = ( ( ! empty( $new_instance['per_page'] ?? NULL ) ) ? absint( $new_instance['per_page'] ) : 5 );
		$instance['see_others'] = absint( $new_instance['see_others'] ?? 0 );
		return $instance;
	}
} endif;