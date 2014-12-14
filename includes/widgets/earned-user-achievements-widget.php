<?php

class toolkit_earned_user_achievements_grid_widget extends WP_Widget {

	public $directory_url = '';

	//process the new widget
	function __construct() {
		$widget_ops = array(
			'classname' => 'badgeos_toolkit_earned_user_achievements_class',
			'description' => __( 'Displays all achievements earned by the logged in user', 'badgeos-toolkit' )
		);
		parent::__construct( 'badgeos_toolkit_earned_user_achievements_widget', __( 'BadgeOS ToolKit Earned User Achievements', 'badgeos-toolkit' ), $widget_ops );
	}

	function form( $instance ) {
		$defaults = array(
			'title' => __( 'My Achievements', 'badgeos' ),
			'number' => '10',
			'point_total' => '',
			'set_achievements' => ''
		);
		$instance = wp_parse_args( (array) $instance, $defaults );
		$set_achievements = ( isset( $instance['set_achievements'] ) ) ? (array) $instance['set_achievements'] : array();
		?>
            <p>
	            <label>
		            <?php _e( 'Title', 'badgeos-toolkit' ); ?>:
		            <input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>"  type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
	            </label>
            </p>
			<p>
				<label>
					<?php _e( 'Number to display (0 = all)', 'badgeos-toolkit' ); ?>:
					<input class="widefat" name="<?php echo $this->get_field_name( 'number' ); ?>"  type="text" value="<?php echo absint( $instance['number'] ); ?>" />
				</label>
			</p>
			<p>
				<label
					><input type="checkbox" id="<?php echo $this->get_field_name( 'point_total' ); ?>" name="<?php echo $this->get_field_name( 'point_total' ); ?>" <?php checked( $instance['point_total'], 'on' ); ?> /> <?php _e( 'Display user\'s total points', 'badgeos-toolkit' ); ?>
				</label>
			</p>
			<p><?php _e( 'Display only the following Achievement Types:', 'badgeos-toolkit' ); ?><br />
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

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
		$instance['point_total'] = ( ! empty( $new_instance['point_total'] ) ) ? sanitize_text_field( $new_instance['point_total'] ) : '';
		$instance['set_achievements'] = array_map( 'sanitize_text_field', $new_instance['set_achievements'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		global $user_ID;

		#$user_output = apply_filters( 'badgeos_toolkit_user_achievements_widget_output', '', $args, $instance );

		echo $args['before_widget'];

		$title = apply_filters( 'widget_title', $instance['title'] );

		if ( !empty( $title ) ) { echo $args['before_title'] . $title . $args['after_title']; };

		//user must be logged in to view earned badges and points
		if ( is_user_logged_in() ) {

			//display user's points if widget option is enabled
			if ( $instance['point_total'] == 'on' )
				echo '<p class="badgeos-total-points">' . sprintf( __( 'My Total Points: %s', 'badgeos-toolkit' ), '<strong>' . number_format( badgeos_get_users_points() ) . '</strong>' ) . '</p>';

			$achievements = badgeos_get_user_achievements();

			if ( is_array( $achievements ) && ! empty( $achievements ) ) {

				$number_to_show = absint( $instance['number'] );
				$thecount = 0;

				wp_enqueue_script( 'badgeos-achievements' );
				wp_enqueue_style( 'badgeos-widget' );

				//load widget setting for achievement types to display
				$set_achievements = ( isset( $instance['set_achievements'] ) ) ? $instance['set_achievements'] : '';

				//show most recently earned achievement first
				$achievements = array_reverse( $achievements );

				echo '<ul class="widget-achievements-listing">';
				foreach ( $achievements as $achievement ) {

					//verify achievement type is set to display in the widget settings
					//if $set_achievements is not an array it means nothing is set so show all achievements
					if ( ! is_array( $set_achievements ) || in_array( $achievement->post_type, $set_achievements ) ) {

						//exclude step CPT entries from displaying in the widget
						if ( get_post_type( $achievement->ID ) != 'step' ) {

							$permalink  = get_permalink( $achievement->ID );
							$title      = get_the_title( $achievement->ID );
							$img        = badgeos_get_achievement_post_thumbnail( $achievement->ID, array( 50, 50 ), 'wp-post-image' );
							$thumb      = $img ? '<a style="margin-top: -25px;" class="badgeos-item-thumb" href="'. esc_url( $permalink ) .'">' . $img .'</a>' : '';
							$class      = 'widget-badgeos-item-title';
							$item_class = $thumb ? ' has-thumb' : '';

							// Setup credly data if giveable
							$giveable   = credly_is_achievement_giveable( $achievement->ID, $user_ID );
							$item_class .= $giveable ? ' share-credly addCredly' : '';
							$credly_ID  = $giveable ? 'data-credlyid="'. absint( $achievement->ID ) .'"' : '';

							echo '<li id="widget-achievements-listing-item-'. absint( $achievement->ID ) .'" '. $credly_ID .' class="widget-achievements-listing-item'. esc_attr( $item_class ) .'">';
							echo $thumb;
							echo '<a class="widget-badgeos-item-title '. esc_attr( $class ) .'" href="'. esc_url( $permalink ) .'">'. esc_html( $title ) .'</a>';
							echo '</li>';

							$thecount++;

							if ( $thecount == $number_to_show && $number_to_show != 0 )
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
