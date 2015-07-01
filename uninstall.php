<?php
require('manage-avatar.php');

$users = get_users( array() );
foreach ( $users as $user ) {
	$attachment_id = get_user_meta( $user->ID, 'mo_oauth_avatar_manager_custom_avatar', true );
	if ( ! empty( $attachment_id ) ) {
		mo_oauth_avatar_manager_delete_avatar($attachment_id);
	}
	delete_user_meta($user->ID, 'user_eveonline_character_name');
	delete_user_meta($user->ID, 'user_eveonline_corporation_name');
	delete_user_meta($user->ID, 'user_eveonline_alliance_name');
}

delete_option('host_name');
delete_option('mo_oauth_admin_email');
delete_option('mo_oauth_admin_phone');
delete_option('verify_customer');
delete_option('mo_oauth_admin_customer_key');
delete_option('mo_oauth_admin_api_key');
delete_option('customer_token');
delete_option('mo_oauth_google_enable');
delete_option('mo_oauth_google_scope');
delete_option('mo_oauth_google_client_id');
delete_option('mo_oauth_google_client_secret');
delete_option('mo_oauth_google_redirect_url');
delete_option('mo_oauth_google_message');
delete_option('mo_oauth_eveonline_enable');
delete_option('mo_oauth_eveonline_scope');
delete_option('mo_oauth_eveonline_client_id');
delete_option('mo_oauth_eveonline_client_secret');
delete_option('mo_oauth_eveonline_message');
delete_option('message');
delete_option('mo_eve_api_key');
delete_option('mo_eve_verification_code');
delete_option('mo_eve_allowed_corps');
delete_option('mo_eve_allowed_alliances');
delete_option('mo_eve_allowed_char_name');
delete_option('new_registration');
?>