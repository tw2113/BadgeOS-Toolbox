<?php

/**
 * Class toolkit_achievement_earners_widget.
 *
 * Create our achievement earners list.
 *
 * @since 1.0.0
 */
class toolkit_achievement_earners_widget extends WP_Widget {

	public $directory_url = '';

	/**
	 * Put everything together.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $toolbox;
		$this->directory_url  = $toolbox->directory_url;

		$widget_ops = array(
			'classname' => 'badgeos_toolkit_achievement_earners_class',
			'description' => __( 'Displays all earners of specified achievements.', 'badgeos-toolkit' )
		);
		parent::__construct( 'toolkit_achievement_earners_widget', __( 'BadgeOS ToolKit Achievement Earners List', 'badgeos-toolkit' ), $widget_ops );

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'styles_scripts' ) );
		}
	}

	/**
	 * Enqueue any necessary stylesheets or scripts.
	 *
	 * @since 1.0.0
	 */
	public function styles_scripts() {

	}

	/**
	 * Helper function for widget form() method display.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments to be used with the input type output.
	 *
	 * @return string $value HTML output for the input type.
	 */
	public function form_input_text( $args = array() ) {
		$label = esc_attr( $args['label'] );
		$name  = esc_attr( $args['name'] );
		$id    = esc_attr( $args['id'] );
		$value = esc_attr( $args['value'] );

		printf(
			'<p><label for="%1$s">%2$s</label><input type="text" class="widefat" name="%3$s" id="%1$s" value="%4$s" /></p>',
			$id,
			$label,
			$name,
			$value
		);
	}

	/**
	 * Output our form for the WP Admin.
	 *
	 * @param array $instance Values for our current widget instance.
	 *
	 * @return string HTML output for the form method.
	 */
	public function form( $instance ) {
		$defaults = array(
			'title' => __( 'Achievement Earners', 'badgeos' ),
			'achievement_ids' => '',
		);
		$instance = wp_parse_args( (array) $instance, $defaults );

		$this->form_input_text(
			array(
				'label' => __( 'Title:', 'badgeos-toolkit'),
				'name' => $this->get_field_name( 'title' ),
				'id' => $this->get_field_id( 'title' ),
				'value' => $instance['title']
			)
		);
		$this->form_input_text(
			array(
				'label' => __( 'Achievement IDs to display, separated by commas:', 'badgeos-toolkit'),
				'name' => $this->get_field_name( 'achievement_ids' ),
				'id' => $this->get_field_id( 'achievement_ids' ),
				'value' => $instance['achievement_ids']
			)
		);
	}

	/**
	 * Update our widget values.
	 *
	 * @param array $new_instance Newly entered widget data.
	 * @param array $old_instance Current values for widget data.
	 *
	 * @return array $instance Sanitized new data to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']            = sanitize_text_field( $new_instance['title'] );
		$instance['achievement_ids']  = sanitize_text_field( $new_instance['achievement_ids'] );

		return $instance;
	}

	/**
	 * Output the frontend widget display.
	 *
	 * @param array $args     Widget arguments from theme settings.
	 * @param array $instance Values for our current widget instance.
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		};

		# user must be logged in to view earned badges and points
		if ( is_user_logged_in() ) {

			$achievement_ids = array_map( 'trim', explode( ',', $instance['achievement_ids'] ) );

			if ( is_array( $achievement_ids ) && ! empty( $achievement_ids ) ) {

				$output = '';
				foreach ( $achievement_ids as $achievement_id ) {

					$output .= '<h3>' . get_the_title( $achievement_id ) . '</h3>';
					$earners = badgeos_get_achievement_earners( $achievement_id );

					$output .= '<ul class="badgeos-toolkit-achievement-earners-list achievement-' . $achievement_id . '-earners-list">';
					foreach ( $earners as $user ) {
						$user_content = '<li><a href="' . get_author_posts_url( $user->ID ) . '">' . get_avatar( $user->ID ) . '</a></li>';

						/**
						 * Fitlers the markup for the individual user being rendered.
						 *
						 * @since 1.0.0
						 *
						 * @param string $user_content HTML markup being rendered.
						 * @param int    $ID           User ID being rendered.
						 */
						$output .= apply_filters( 'badgeos_toolkit_get_achievement_earners_list_user', $user_content, $user->ID );
					}
					$output .= '</ul>';

					/**
					 * Filters the markup for the individual achievement list.
					 *
					 * @since 1.0.0
					 *
					 * @param string $output HTML markup being rendered fr the achievement.
					 * @param int    $achievement_id ID of the achievement the HTML was for.
					 * @param array  $achievement_ids Array of all IDs specified for the widget.
					 * @param array  $earners Array of users who earned the specified achievement.
					 */
					$output = apply_filters( 'badgeos_toolkit_get_achievement_earners_achievement_list', $output, $achievement_id, $achievement_ids, $earners );
				}

				/**
				 * Filters the markup for the complete achievements list.
				 *
				 * @since 1.0.0
				 *
				 * @param string $output HTML markup being rendered fr the achievement.x
				 * @param array  $achievement_ids Array of all IDs specified for the widget.
				 */
				echo apply_filters( 'badgeos_toolkit_get_achievement_earners_list', $output, $achievement_ids );
			} else {
				_e( 'No one has earned any of the specified achievements yet.', 'badgeos-toolkit' );
			}

		} else {
			_e( 'You must be logged in to view achievement earners', 'badgeos-toolkit' );
		}

		echo $args['after_widget'];
	}
}
