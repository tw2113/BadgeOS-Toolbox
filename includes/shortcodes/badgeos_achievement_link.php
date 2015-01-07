<?php

/**
 * Register the [badgeos_user_achievements_list] shortcode.
 *
 * @since 1.0.0
 */
function badgeos_toolkit_register_achievement_link_shortcode() {

	badgeos_register_shortcode( array(
		'name'            => __( 'Achievement Link', 'badgeos-toolkit' ),
		'slug'            => 'achievement_link',
		'output_callback' => 'badgeos_toolkit_achievement_link_shortcode',
		'description'     => __( 'Render an HTML link for an achievement.', 'badgeos-toolkit' ),
		'attributes'      => array(
			'id' => array(
				'name'        => __( 'Achievement ID', 'badgeos-toolkit' ),
				'description' => __( 'The ID of the achievement to link.', 'badgeos-toolkit' ),
				'type'        => 'text',
			)
		)
	) );
}
add_action( 'init', 'badgeos_toolkit_register_achievement_link_shortcode', 11 );

/**
 * Callback function for our user achievements list shortcode.
 *
 * @param array $atts Supplied shortcode attributes
 *
 * @return string $value HTML Output for the user achievements.
 */
function badgeos_toolkit_achievement_link_shortcode( $atts = array() ) {

	$args = shortcode_atts( array(
		'id' => 0,
	), $atts );

	$markup = '<a href="%s">%s</a>';
	$achievement = get_post( $args['id'] );
	if ( empty( $args['id'] ) || ! badgeos_is_achievement( $args['id'] ) ) {
		return sprintf(
			$markup,
			home_url(),
			get_bloginfo( 'name' )
		);
	} else {
		return sprintf(
			$markup,
			get_permalink( $achievement->ID ),
			$achievement->post_title
		);
	}
}
