<?php

/**
 * Register the [badgeos_user_achievements_list] shortcode.
 *
 * @since 1.0.0
 */
function badgeos_toolkit_register_earn_achievement_for_page_view_shortcode() {

	badgeos_register_shortcode( array(
		'name'            => __( 'Earn Achievement For Page View', 'badgeos-toolkit' ),
		'slug'            => 'badgeos_toolkit_earn_achievement_for_page_view',
		'output_callback' => 'badgeos_toolkit_earn_achievement_for_page_view_shortcode',
		'description'     => __( 'Award an achievement for viewing a specific post or page.', 'badgeos-toolkit' ),
		'attributes'      => array(
			'achievement_id' => array(
				'name'        => __( 'Achievement ID', 'badgeos-toolkit' ),
				'description' => __( 'The ID of the achievement to award.', 'badgeos-toolkit' ),
				'type'        => 'text',
			),
			'user_id' => array(
				'name'        => __( 'User ID', 'badgeos-toolkit' ),
				'description' => __( 'The ID of the user to award achievement to.', 'badgeos-toolkit' ),
				'type'        => 'text',
			)
		)
	) );
}
add_action( 'init', 'badgeos_toolkit_register_earn_achievement_for_page_view_shortcode', 11 );

/**
 * Callback function for our user achievements list shortcode.
 *
 * @param array $atts Supplied shortcode attributes
 *
 * @return string $value HTML Output for the user achievements.
 */
function badgeos_toolkit_earn_achievement_for_page_view_shortcode( $atts = array() ) {

	$atts = shortcode_atts( array(
		'achievement_id' => 0,
		'user_id' => get_current_user_id()
	), $atts );

	badgeos_maybe_award_achievement_to_user( $atts['achievement_id'], $atts['user_id'] );
}
