<?php
/*
Plugin Name: BuddyPress Mandatory Groups
Plugin URI: http://www.jerseyconnect.net/development/buddypress-mandatory-groups/
Description: Allows admins to prevent users from leaving groups
Version: 1.2
Revision Date: 09/19/2011
Requires at least: WP 3.0, BuddyPress 1.2
Tested up to: WP 3.2.1 , BuddyPress 1.5-RC-1
License: Example: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
Author: David Dean
Author URI: http://www.generalthreat.com/
*/

define ( 'BP_MANDATORY_GROUPS_IS_INSTALLED', 1 );
define ( 'BP_MANDATORY_GROUPS_VERSION', '1.2' );
define ( 'BP_MANDATORY_GROUPS_SLUG', 'mandatory-groups' );

//load localization files if present
if ( file_exists( dirname( __FILE__ ) . '/' . get_locale() . '.mo' ) )
	load_textdomain( 'buddypress-mandatory-groups', dirname( __FILE__ ) . '/languages/' . get_locale() . '.mo' );

require ( dirname( __FILE__ ) . '/functions.php' );

/*************************************************************************
*********************SETUP AND INSTALLATION*******************************
*************************************************************************/

/**
 * Install and/or upgrade the database
 */
function bp_mandatory_groups_install() {
	global $wpdb, $bp;
}

register_activation_hook( __FILE__, 'bp_mandatory_groups_install' );

/**
 * Set up global variables
 */
function bp_mandatory_groups_setup_globals() {
	global $bp, $wpdb;

	/* For internal identification */
	$bp->mandatory_groups->id = 'mandatory_groups';
	$bp->mandatory_groups->slug = BP_MANDATORY_GROUPS_SLUG;
	$bp->mandatory_groups->meta_names = array(
		'group_ids'		=>	'bp_mandatory_group_ids',
		'lock_nonce'	=>	'bp_mandatory_group_lock',
		'unlock_nonce'	=>	'bp_mandatory_group_unlock'
	);
	
	/* Register this in the active components array */
	$bp->active_components[$bp->mandatory_groups->slug] = $bp->mandatory_groups->id;
	
	do_action('bp_mandatory_groups_globals_loaded');
}
add_action( 'plugins_loaded', 'bp_mandatory_groups_setup_globals', 10 );
add_action( 'admin_menu', 'bp_mandatory_groups_setup_globals', 2 );

?>