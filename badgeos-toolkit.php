<?php
/**
 * Plugin Name: BadgeOS Toolkit
 * Plugin URI: http://michaelbox.net
 * Description: This BadgeOS add-on adds handy tools and helpers for use with your BadgeOS system.
 * Author: Michael Beckwith
 * Version: 1.0.0
 * Author URI: http://michaelbox.net
 * License: GNU AGPLv3
 * License URI: http://www.gnu.org/licenses/agpl-3.0.html
 * Text Domain: badgeos-toolkit
 */

/*
 * Copyright © 2014 Michael Beckwith
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General
 * Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>;.
*/

/**
 * Our main plugin instantiation class
 *
 * @since 1.0.0
 */
class BadgeOS_Toolkit {

	public $basename = '';
	public $directory_path = '';
	public $directory_url = '';

	/**
	 * Get everything running.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		# Define plugin constants
		$this->basename       = plugin_basename( __FILE__ );
		$this->directory_path = plugin_dir_path( __FILE__ );
		$this->directory_url  = plugins_url( dirname( $this->basename ) );

		# Load translations
		load_plugin_textdomain( 'badgeos-toolkit', false, dirname( $this->basename ) . '/languages' );

		add_action( 'admin_notices', array( $this, 'maybe_disable_plugin' ) );
		add_action( 'plugins_loaded', array( $this, 'includes' ) );
	}

	/**
	 * Check if BadgeOS is available
	 *
	 * @since  1.0.0
	 *
	 * @return bool True if BadgeOS is available, false otherwise
	 */
	public static function meets_requirements() {

		if ( class_exists('BadgeOS') ) {

			return true;

		}

		return false;

	}

	/**
	 * Potentially output a custom error message and deactivate
	 * this plugin, if we don't meet requriements.
	 *
	 * This fires on admin_notices.
	 *
	 * @since 1.0.0
	 */
	public function maybe_disable_plugin() {

		if ( ! $this->meets_requirements() ) {

			# Display our error
			?>
			<div id="message" class="error">
			<p><?php printf( __( 'BadgeOS Toolkit requires BadgeOS and has been <a href="%s">deactivated</a>. Please install and activate BadgeOS and then reactivate this plugin.', 'badgeos-toolkit' ), admin_url( 'plugins.php' ) ); ?></p>
			</div>

			<?php
			# Deactivate our plugin
			deactivate_plugins( $this->basename );

		}

	}

	public function includes() {
		require_once $this->directory_path . 'includes/achievement-functions.php';

		# Shortcodes
		require_once $this->directory_path . 'includes/shortcodes/badgeos_user_achievements_list.php';
		require_once $this->directory_path . 'includes/shortcodes/badgeos_achievement_link.php';

		# Widgets
		require_once $this->directory_path . 'includes/widgets.php';
	}

}

$toolbox = new BadgeOS_Toolkit();
