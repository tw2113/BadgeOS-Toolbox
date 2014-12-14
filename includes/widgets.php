<?php

 //register our widget
function badgeos_toolkit_register_widgets() {
	require_once( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/widgets/earned-user-achievements-widget.php' );
	require_once( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/widgets/achievement-earners-widget.php' );

	register_widget( 'toolkit_earned_user_achievements_grid_widget' );
	register_widget( 'toolkit_achievement_earners_widget' );

}
add_action( 'widgets_init', 'badgeos_toolkit_register_widgets', 10 );
