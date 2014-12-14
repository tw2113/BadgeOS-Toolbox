<?php

class toolkit_earned_user_achievements_grid_widget extends WP_Widget {

	public $directory_url = '';

	//process the new widget
	public function __construct() {
		global $toolbox;
		$this->directory_url  = $toolbox->directory_url;

		$widget_ops = array(
			'classname' => 'badgeos_toolkit_earned_user_achievements_class',
			'description' => __( 'Displays all achievements earned by the logged in user in a grid layout', 'badgeos-toolkit' )
		);
		parent::__construct( 'badgeos_toolkit_earned_user_achievements_widget', __( 'BadgeOS ToolKit Grid Earned User Achievements', 'badgeos-toolkit' ), $widget_ops );

		if ( is_active_widget( false, false, $this->id_base ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'styles_scripts' ) );
		}
	}

	public function styles_scripts() {
		wp_register_style( 'badgeos-toolkit', $this->directory_url . '/css/badgeos-toolkit.css', array( 'badgeos-widget' ) );

		wp_enqueue_script( 'badgeos-achievements' );
		wp_enqueue_style( 'badgeos-widget' );
		wp_enqueue_style( 'badgeos-toolkit' );
	}

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

	public function form( $instance ) {
		$defaults = array(
			'title' => __( 'My Achievements', 'badgeos' ),
			'number' => '10',
			'point_total' => '',
			'set_achievements' => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$set_achievements = ( isset( $instance['set_achievements'] ) ) ? (array) $instance['set_achievements'] : array();

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
				'label' => __( 'Number to display (0 = all):', 'badgeos-toolkit'),
				'name' => $this->get_field_name( 'number' ),
				'id' => $this->get_field_id( 'number' ),
				'value' => $instance['number']
			)
		);
		?>
			<p>
				<label>
					<input type="checkbox" id="<?php echo $this->get_field_name( 'point_total' ); ?>" name="<?php echo $this->get_field_name( 'point_total' ); ?>" <?php checked( $instance['point_total'], 'on' ); ?> />
					<?php _e( 'Display user\'s total points', 'badgeos-toolkit' ); ?>
				</label>
			</p>
			<p>
				<?php _e( 'Display only the following Achievement Types:', 'badgeos-toolkit' ); ?><br />
				<?php
				$achievements = badgeos_get_achievement_types();
				foreach ( $achievements as $achievement_slug => $achievement ) {

					if ( $achievement['single_name'] == 'step' ) {
						continue;
					}

					//if achievement displaying exists in the saved array it is enabled for display
					$checked = checked( in_array( $achievement_slug, $set_achievements ), true, false );

					printf(
						'<label for="%s"><input type="checkbox" name="%s[]" id="%s" value="%s" %s /> %s</label><br/>',
						$this->get_field_name( 'set_achievements' ) . '_' . esc_attr( $achievement_slug ),
						$this->get_field_name( 'set_achievements' ),
						$this->get_field_name( 'set_achievements' ) . '_' . esc_attr( $achievement_slug ),
						esc_attr( $achievement_slug ),
						$checked,
						esc_html( ucfirst( $achievement[ 'plural_name' ] ) )
					);
				}
				?>
			</p>
        <?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title']            = sanitize_text_field( $new_instance['title'] );
		$instance['number']           = absint( $new_instance['number'] );
		$instance['point_total']      = ( ! empty( $new_instance['point_total'] ) ) ? sanitize_text_field( $new_instance['point_total'] ) : '';
		$instance['set_achievements'] = array_map( 'sanitize_text_field', $new_instance['set_achievements'] );

		return $instance;
	}

	public function widget( $args, $instance ) {
		global $user_ID;

		#$user_output = apply_filters( 'badgeos_toolkit_user_achievements_widget_output', '', $args, $instance );

		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( !empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		};

		//user must be logged in to view earned badges and points
		if ( is_user_logged_in() ) {

			//display user's points if widget option is enabled
			if ( $instance['point_total'] == 'on' ) {
				printf( '<p class="badgeos-total-points">%s</p>',
					sprintf(
						__( 'My Total Points: %s', 'badgeos-toolkit' ),
						'<strong>' . number_format( badgeos_get_users_points() ) . '</strong>'
					)
				);
			}

			$achievements = badgeos_get_user_achievements();

			if ( is_array( $achievements ) && ! empty( $achievements ) ) {

				$number_to_show = absint( $instance['number'] );
				$thecount       = 0;

				//load widget setting for achievement types to display
				$set_achievements = ( isset( $instance['set_achievements'] ) ) ? $instance['set_achievements'] : '';

				//show most recently earned achievement first
				$achievements = array_reverse( $achievements );

				echo '<ul class="widget-achievements-listing grid">';
				foreach ( $achievements as $achievement ) {

					//exclude step CPT entries from displaying in the widget
					if ( get_post_type( $achievement->ID ) == 'step' ) {
						continue;
					}

					//verify achievement type is set to display in the widget settings
					//if $set_achievements is not an array it means nothing is set so show all achievements
					if ( ! is_array( $set_achievements ) || in_array( $achievement->post_type, $set_achievements ) ) {

						$permalink      = get_permalink( $achievement->ID );
						$title          = get_the_title( $achievement->ID );
						$img_dimensions = apply_filters( 'badgeos_toolkit_widget_grid_thumb_width',
							array(
								'width' => '50',
								'height' => '50',
								'unit' => 'px'
							)
						);

						if ( !is_array( $img_dimensions ) ) {
							$img_dimensions = array( 'width' => '50', 'height' => '50' );
						}
						$img = badgeos_get_achievement_post_thumbnail( $achievement->ID, array(
							$img_dimensions['width'],
							$img_dimensions['height']
						), 'wp-post-image' );

						$class = 'widget-badgeos-item-title';

						// Setup credly data if giveable
						$giveable = credly_is_achievement_giveable( $achievement->ID, $user_ID );
						$item_class = $giveable ? 'share-credly addCredly' : '';
						$credly_ID = $giveable ? 'data-credlyid="' . absint( $achievement->ID ) . '"' : '';
						$style = sprintf( 'style="width: %s; height: %s;"',
							$img_dimensions['width'] . $img_dimensions['unit'],
							$img_dimensions['height'] . $img_dimensions['unit']
						);

						printf( '<li id="widget-achievements-listing-item-%s" %s class="widget-achievements-listing-item %s" %s>%s</li>',
							absint( $achievement->ID ),
							$credly_ID,
							esc_attr( $item_class ),
							$style,
							sprintf(
								'<a class="widget-badgeos-item %s" href="%s" title="%s">%s</a>',
								esc_attr( $class ),
								esc_url( $permalink ),
								esc_attr( $title ),
								$img
							)
						);

						$thecount ++;

						if ( $thecount == $number_to_show && $number_to_show != 0 ) {
							break;
						}

					}
				}

				echo '</ul><!-- widget-achievements-listing -->';

			}

		} else {

			//user is not logged in so display a message
			_e( 'You must be logged in to view earned achievements', 'badgeos-toolkit' );

		}

		echo $args['after_widget'];
	}

}
