<?php

/**
 * Check whether specified user is allowed to leave the specified group
 */
function bp_mandatory_groups_is_user_locked_in( $user_id, $group_id ) {
	global $bp;
	$user_id = (int)$user_id;

	if($mandatory_groups = wp_cache_get( $bp->mandatory_groups->meta_names['group_ids'] . $user_id )) {
		
	} else {
		$mandatory_groups = get_user_meta( $user_id, $bp->mandatory_groups->meta_names['group_ids'], true );
		wp_cache_add( $bp->mandatory_groups->meta_names['group_ids'] . $user_id, $mandatory_groups );
	}
	
	if( is_array($mandatory_groups) && in_array( $group_id, $mandatory_groups ) ) {
		return true;
	}
	return false;
}

/**
 * Lock the specified user into the specified group
 */
function bp_mandatory_groups_lock_user_in( $user_id, $group_id ) {
	global $bp;
	$mandatory_groups = get_user_meta( $user_id, $bp->mandatory_groups->meta_names['group_ids'], true );
	if(is_array($mandatory_groups)) {
		if(in_array($group_id, $mandatory_groups)) {
			return true;
		} else {
			$mandatory_groups[] = $group_id;
		}
	} else {
		$mandatory_groups = array($group_id);
	}
	update_user_meta( $user_id, $bp->mandatory_groups->meta_names['group_ids'], $mandatory_groups );
	wp_cache_set( $bp->mandatory_groups->meta_names['group_ids'] . $user_id, $mandatory_groups );
	return true;
}

/**
 * Allow the user to leave the specified group
 */
function bp_mandatory_groups_let_user_out( $user_id, $group_id ) {
	global $bp;
	$mandatory_groups = get_user_meta( $user_id, $bp->mandatory_groups->meta_names['group_ids'], true );
	if(is_array($mandatory_groups)) {
		if(!in_array($group_id, $mandatory_groups)) {
			return true;
		} else {
			
			$new_groups = array();
			foreach($mandatory_groups as $group) {
				if($group != $group_id) {
					$new_groups[] = $group;
				}
			}
			$mandatory_groups = $new_groups;
			
			update_user_meta( $user_id, $bp->mandatory_groups->meta_names['group_ids'], $mandatory_groups );
			wp_cache_set( $bp->mandatory_groups->meta_names['group_ids'] . $user_id, $mandatory_groups );
		}
	}
	return true;
}

/**
 * Remove the 'Leave Group' buttons from around the site for locked-in users
 */
function bp_mandatory_groups_remove_leave_button( $button ) {
	global $bp;

	$group_id = substr($button['wrapper_id'],12);
	$user_id  = $bp->loggedin_user->id;

	if($button['id'] == 'leave_group' && bp_mandatory_groups_is_user_locked_in( $user_id, $group_id )) {
		$button['link_href'] = '/denied/';
		$button['link_text'] = __( 'Group Mandatory', 'buddypress-mandatory-groups' );
		$button['link_title'] = __( 'An administrator has made membership in this group mandatory for you. You are not permitted to leave it.', 'buddypress-mandatory-groups' );
		$button['wrapper_class'] .= ' pending';
	}
	return $button;
}
add_filter( 'bp_get_group_join_button', 'bp_mandatory_groups_remove_leave_button' );


/**
 * Allow admins to lock / unlock users through the 'Manage Members' panel or BP Group Management plugin
 */
function bp_mandatory_groups_add_mandatory_option() {

	global $members_template, $bp;
	$member = $members_template->members[$members_template->current_member];

	if(is_admin()) {
		$group_id = (int)$_GET['id'];
		$lock_url = $_SERVER['REQUEST_URI'] . '&amp;member_id=' . $member->user_id . '&amp;member_action=lock';
		$unlock_url = $_SERVER['REQUEST_URI'] . '&amp;member_id=' . $member->user_id . '&amp;member_action=unlock';
	} else {
		$group_id = $bp->groups->current_group->id;
		$lock_url = bp_get_group_permalink( $bp->groups->current_group ) . 'admin/manage-members/lock/' . $member->user_id;
		$unlock_url = bp_get_group_permalink( $bp->groups->current_group ) . 'admin/manage-members/unlock/' . $member->user_id;;
	}


	if(bp_mandatory_groups_is_user_locked_in( $member->user_id, $group_id )) {
		echo '| <a href="' . wp_nonce_url( $unlock_url, $bp->mandatory_groups->meta_names['unlock_nonce'] ) . '">' . __('Unlock User','buddypress-mandatory-groups') . '</a>';
	} else {
		echo '| <a href="' . wp_nonce_url( $lock_url, $bp->mandatory_groups->meta_names['lock_nonce'] ) . '">' . __('Lock User In', 'buddypress-mandatory-groups') . '</a>';
	}
}
add_action( 'bp_group_manage_members_admin_item', 'bp_mandatory_groups_add_mandatory_option' );

/**
 * Handle requests from the fronted member admin links
 */
function bp_mandatory_groups_frontend_admin() {
	global $bp, $members_template;
	
	if($bp->action_variables[1] == 'lock' && check_ajax_referer($bp->mandatory_groups->meta_names['lock_nonce'])) {

		$user_id = (int)$bp->action_variables[2];
		$user_data = get_userdata( $user_id );
		
		if( is_super_admin() ) {
			bp_mandatory_groups_lock_user_in( $user_id, $bp->groups->current_group->id );
			?><div id="message" class="updated"><p><?php printf( __( 'User %s has been locked in.', 'buddypress-mandatory-groups' ), $user_data->user_login ) ?></p></div><?php
		} else {
			?><div id="message" class="error"><p>Only super admins can lock/unlock users.</p></div><?php
		}
		
	} else if($bp->action_variables[1] == 'unlock' && check_ajax_referer($bp->mandatory_groups->meta_names['unlock_nonce'])) {

		$user_id = (int)$bp->action_variables[2];
		$user_data = get_userdata( $user_id );
		
		if( is_super_admin() ) {
			bp_mandatory_groups_let_user_out( $user_id, $bp->groups->current_group->id );
			?><div id="message" class="updated"><p><?php printf( __( 'User %s has been unlocked.', 'buddypress-mandatory-groups' ), $user_data->user_login ) ?></p></div><?php
		} else {
			?><div id="message" class="error"><p>Only super admins can lock/unlock users.</p></div><?php
		}
		
	}
	
}
add_action( 'bp_before_group_manage_members_admin', 'bp_mandatory_groups_frontend_admin' );

/**
 * Handle requests from BP Group Management
 */
function bp_mandatory_groups_gm_admin( $group, $id, $action ) {
	
	global $bp;
	
	$user_id = (int)$_GET['member_id'];
	
	if( $action == 'lock' && check_admin_referer($bp->mandatory_groups->meta_names['lock_nonce']) && is_super_admin() ) {
		bp_mandatory_groups_lock_user_in( $user_id, $id );
	}
	if( $action == 'unlock' && check_admin_referer($bp->mandatory_groups->meta_names['unlock_nonce']) && is_super_admin() ) {
		bp_mandatory_groups_let_user_out( $user_id, $id );
	}
}
add_action( 'bp_gm_member_action', 'bp_mandatory_groups_gm_admin', 10, 3 );

?>