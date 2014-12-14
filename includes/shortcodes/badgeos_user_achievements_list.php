<?php

/**
 * Register the [badgeos_user_achievements_list] shortcode.
 *
 * @since 1.0.0
 */
function badgeos_toolkit_register_user_achievements_list_shortcode() {

	badgeos_register_shortcode( array(
		'name'            => __( 'User Achievements List', 'badgeos-toolkit' ),
		'slug'            => 'badgeos_user_achievements_list',
		'output_callback' => 'badgeos_toolkit_user_achievements_list_shortcode',
		'description'     => __( 'Render a list of user achievements.', 'badgeos-toolkit' ),
		'attributes'      => array(
			'user' => array(
				'name'        => __( 'User ID', 'badgeos-toolkit' ),
				'description' => __( 'The ID of the user to render.', 'badgeos-toolkit' ),
				'type'        => 'text',
			),
			'limit' => array(
				'name'        => __( 'Limit', 'badgeos-toolkit' ),
				'description' => __( 'Number of achievements to display. "All" for all.', 'badgeos-toolkit' ),
				'type'        => 'text',
				'default'     => 5,
			)
		)
	) );
}
add_action( 'init', 'badgeos_toolkit_register_user_achievements_list_shortcode', 11 );

/**
 * Callback function for our user achievements list shortcode.
 *
 * @param array $atts Supplied shortcode attributes
 *
 * @return string $value HTML Output for the user achievements.
 */
function badgeos_toolkit_user_achievements_list_shortcode( $atts = array() ) {

	// Parse our attributes
	$atts = shortcode_atts( array(
		'user'  => get_current_user_id(),
		'limit' => 5
	), $atts );

	$output = '';

	// Grab the user's current achievements, without duplicates
	$achievement_ids = array_unique( badgeos_get_user_earned_achievement_ids( $atts['user'] ) );

	// Setup a counter
	$count = 0;

	$output .= '<div class="badgeos-user-badges-wrap">';

	$thumbnail_size    = apply_filters( 'badgeos_toolkit_user_list_thumb_size', 'badgeos-achievement' );
	$thumbnail_classes = apply_filters( 'badgeos_toolkit_user_list_classes', '' );

	// Loop through the achievements
	if ( ! empty( $achievement_ids ) ) {
		foreach ( $achievement_ids as $achievement_id ) {

			// If we've hit our limit, quit
			if ( 'all' != $atts['limit'] && $count >= $atts['limit'] ) {
				break;
			}

			// Output our achievement image and title
			$output .= '<div class="badgeos-badge-wrap">';
			$output .= badgeos_get_achievement_post_thumbnail( $achievement_id, $thumbnail_size, $thumbnail_classes );
			$output .= '<span class="badgeos-title-wrap">' . get_the_title( $achievement_id ) . '</span>';
			$output .= '</div>';

			// Increase our counter
			$count ++;
		}
	}
	$output .= '</div>';

	return apply_filters( 'badgeos_toolkit_user_list_output', $output, $atts, $achievement_ids, $thumbnail_size, $thumbnail_classes );
}
