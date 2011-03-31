=== BuddyPress Mandatory Groups ===
Contributors: ddean
Tags: buddypress, groups, require, lock, mandatory, users, membership, enforcement
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 1.0

Allows site / network administrators to enforce BuddyPress group membership by locking users into groups

== Description ==

Network administrators can enforce group membership policy with BuddyPress by using the BuddyPress Mandatory Groups plugin.

Users locked in to a group or group are unable to leave that group.
Functions are available for administrators to add membership enforcement to other processes.

== Installation ==

1. Extract the plugin archive 
1. Upload plugin files to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I enforce group membership? =
* Either from the `Manage Members` tab in the group Admin section, or through BP Group Management (but see Known Issues)

== Changelog ==

= 1.0 =
* Initial release

== Known Issues ==

Currently known issues:

* BP Group Management (0.42) has a bug so it doesn't handle the member action hook properly.  Links from the Group Management menu won't work until that is fixed, or until you modify the `bp-group-management-bp-functions.php` file.
