<?php
function set_avatar($user_id, $character_id) {
	require ( ABSPATH . 'wp-admin/includes/image.php' );
	if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}
	//echo "Set avatar<br />";
	$upload_dir = wp_upload_dir();
	
	$characterurl="https://image.eveonline.com/Character/" . $character_id . "_512.jpg";
	$directory = $upload_dir['basedir'] . '/avatars/' . $user_id;
	$img = $directory . '/' . $character_id . '.jpg';
	$url = $upload_dir['baseurl'] . '/avatars/' . $user_id . '/' . $character_id . '.jpg';
	echo '<br />URL: ' . $url;
	if( ! file_exists( $directory ) ) {
		mkdir($directory, 0777, true);
	}
	file_put_contents($img, file_get_contents($characterurl));
	$avatar = array (
		'url' => $url,
		'type'=> 'image/jpeg',
		'file'=> $img
	);
	$custom_avatar = get_user_meta( $user_id, 'mo_oauth_avatar_manager_custom_avatar', true );
	if ( ! empty( $custom_avatar ) ) {
		mo_oauth_avatar_manager_delete_avatar($custom_avatar);
	}
	
	$attachment = array(
        'guid'           => $avatar['url'],
        'post_content'   => $avatar['url'],
        'post_mime_type' => $avatar['type'],
        'post_title'     => basename( $avatar['file'] )
    );
	
	// Inserts the attachment into the media library.
    $attachment_id = wp_insert_attachment( $attachment, $avatar['file'] );
 
    // Generates metadata for the attachment.
    $attachment_metadata = wp_generate_attachment_metadata( $attachment_id, $avatar['file'] );
 
    // Updates metadata for the attachment.
    wp_update_attachment_metadata( $attachment_id, $attachment_metadata );
 
    $custom_avatar = array();
 
    // Generates a resized copy of the avatar image.
    $custom_avatar[ $options['default_size'] ] = mo_oauth_avatar_manager_avatar_resize( $avatar['url'], $options['default_size'] );
 
    // Updates attachment meta fields based on attachment ID.
    update_post_meta( $attachment_id, '_mo_oauth_avatar_manager_custom_avatar', $custom_avatar );
    update_post_meta( $attachment_id, '_mo_oauth_avatar_manager_custom_avatar_rating', 'G' );
    update_post_meta( $attachment_id, '_mo_oauth_avatar_manager_is_custom_avatar', true );
	//echo 'ID:' . $user_id;
	// Updates user meta fields based on user ID.
    update_user_meta( $user_id, 'mo_oauth_avatar_manager_avatar_type', 'custom' );
    update_user_meta( $user_id, 'mo_oauth_avatar_manager_custom_avatar', $attachment_id );
	
	//exit();
}

/**
 * Generates a resized copy of the specified avatar image.
 *
 * @uses wp_upload_dir() For retrieving path information on the currently
 * configured uploads directory.
 * @uses wp_basename() For i18n friendly version of basename().
 * @uses wp_get_image_editor() For retrieving a WP_Image_Editor instance and
 * loading a file into it.
 * @uses is_wp_error() For checking whether the passed variable is a WordPress
 * Error.
 * @uses do_action() For calling the functions added to an action hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param string $url URL of the avatar image to resize.
 * @param int $size Size of the new avatar image.
 * @return array Array with the URL of the new avatar image.
 */
function mo_oauth_avatar_manager_avatar_resize( $url, $size ) {
    // Retrieves path information on the currently configured uploads directory.
    $upload_dir = wp_upload_dir();
 
    $filename  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
    $pathinfo  = pathinfo( $filename );
    $dirname   = $pathinfo['dirname'];
    $extension = $pathinfo['extension'];
 
    // i18n friendly version of basename().
    $basename = wp_basename( $filename, '.' . $extension );
 
    $suffix    = $size . 'x' . $size;
    $dest_path = $dirname . '/' . $basename . '-' . $suffix . '.' . $extension;
    $avatar    = array();
 
    if ( file_exists( $dest_path ) ) {
        $avatar['url']  = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $dest_path );
        $avatar['skip'] = true;
    } else {
        // Retrieves a WP_Image_Editor instance and loads a file into it.
        $image = wp_get_image_editor( $filename );
 
        if ( ! is_wp_error( $image ) ) {
            // Resizes current image.
            $image->resize( $size, $size, true );
 
            // Saves current image to file.
            $image->save( $dest_path );
 
            $avatar['url']  = str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $dest_path );
            $avatar['skip'] = false;
        }
    }
 
    // Calls the functions added to mo_oauth_avatar_manager_avatar_resize action hook.
    do_action( 'mo_oauth_avatar_manager_avatar_resize', $url, $size );
 
    return $avatar;
}

/**
 * Returns user custom avatar based on user ID.
 *
 * @uses get_option() For getting values for a named option.
 * @uses mo_oauth_avatar_manager_get_options() For retrieving plugin options.
 * @uses get_userdata() For retrieving user data by user ID.
 * @uses is_ssl() For checking if SSL is being used.
 * @uses add_query_arg() For retrieving a modified URL (with) query string.
 * @uses esc_attr() For escaping HTML attributes.
 * @uses get_user_meta() For retrieving user meta fields.
 * @uses get_post_meta() For retrieving attachment meta fields.
 * @uses wp_get_attachment_image_src() For retrieving an array with the image
 * attributes "url", "width" and "height", of an image attachment file.
 * @uses mo_oauth_avatar_manager_avatar_resize() For generating a resized copy of the
 * specified avatar image.
 * @uses update_post_meta() For updating attachment meta fields.
 * @uses apply_filters() For calling the functions added to a filter hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param int $user_id User to update.
 * @param int $size Size of the avatar image
 * @param string $default URL to a default image to use if no avatar is
 * available.
 * @param string $alt Alternative text to use in image tag. Defaults to blank.
 * @return string <img> tag for the user's avatar.
 */
function mo_oauth_avatar_manager_get_custom_avatar( $user_id, $size = '', $default = '', $alt = false ) {
    // Returns if showing avatars is not enabled.
    if ( ! get_option( 'show_avatars' ) )
        return false;
 
    // Retrieves plugin options.
    //$options = mo_oauth_avatar_manager_get_options();
 
    if ( empty( $size ) || ! is_numeric( $size ) ) {
        //$size = $options['avatar-manager-default-size'];
		$size = 512;
    } else {
        $size = absint( $size );
 
        if ( $size < 1 )
            $size = 1;
        elseif ( $size > 512 )
            $size = 512;
    }
 
    // Retrieves user data by user ID.
    $user = get_userdata( $user_id );
 
    // Returns if no user data was retrieved.
    if ( empty( $user ) )
        return false;
 
    $email = $user->user_email;
 
    if ( empty( $default ) ) {
        // Retrieves values for the named option.
        $avatar_default = get_option( 'avatar_default' );
 
        if ( empty( $avatar_default ) )
            $default = 'mystery';
        else
            $default = $avatar_default;
    }
 
    $email_hash = md5( strtolower( trim( $email ) ) );
 
    if ( is_ssl() )
        $host = 'https://secure.gravatar.com';
    else
        $host = sprintf( 'http://%d.gravatar.com', ( hexdec( $email_hash[0] ) % 2 ) );
 
    if ( $default == 'mystery' )
        $default = $host . '/avatar/ad516503a11cd5ca435acc9bb6523536?s=' . $size;
    elseif ( $default == 'gravatar_default' )
        $default = '';
    elseif ( strpos( $default, 'http://' ) === 0 )
        // Retrieves a modified URL (with) query string.
        $default = add_query_arg( 's', $size, $default );
 
    if ( $alt === false )
        $alt = '';
    else
        // Escapes HTML attributes.
        $alt = esc_attr( $alt );
 
    // Retrieves values for the named option.
    $avatar_rating = get_option( 'avatar_rating' );
 
    // Retrieves user meta field based on user ID.
    $custom_avatar = get_user_meta( $user_id, 'mo_oauth_avatar_manager_custom_avatar', true );
 
    // Returns if no attachment ID was retrieved.
    if ( empty( $custom_avatar ) )
        return false;
 
    // Retrieves attachment meta field based on attachment ID.
    $custom_avatar_rating = get_post_meta( $custom_avatar, '_mo_oauth_avatar_manager_custom_avatar_rating', true );
 
    $ratings['G']  = 1;
    $ratings['PG'] = 2;
    $ratings['R']  = 3;
    $ratings['X']  = 4;
 
    if ( $ratings[ $custom_avatar_rating ] <= $ratings[ $avatar_rating ] ) {
        // Retrieves attachment meta field based on attachment ID.
        $avatar = get_post_meta( $custom_avatar, '_mo_oauth_avatar_manager_custom_avatar', true );
 
        if ( empty( $avatar[ $size ] ) ) {
            // Retrieves an array with the image attributes "url", "width"
            // and "height", of the image attachment file.
            $url = wp_get_attachment_image_src( $custom_avatar, 'full' );
 
            // Generates a resized copy of the avatar image.
            $avatar[ $size ] = mo_oauth_avatar_manager_avatar_resize( $url[0], $size );
 
            // Updates attachment meta field based on attachment ID.
            update_post_meta( $custom_avatar, '_mo_oauth_avatar_manager_custom_avatar', $avatar );
        }
 
        $src    = $avatar[ $size ]['url'];
        $avatar = '<img alt="' . $alt . '" class="avatar avatar-' . $size . ' photo avatar-default" height="' . $size . '" src="' . $src . '" width="' . $size . '">';
    } else {
        $src  = $host . '/avatar/';
        $src .= $email_hash;
        $src .= '?s=' . $size;
        $src .= '&d=' . urlencode( $default );
        $src .= '&forcedefault=1';
 
        $avatar = '<img alt="' . $alt . '" class="avatar avatar-' . $size . ' photo avatar-default" height="' . $size . '" src="' . $src . '" width="' . $size . '">';
    }
 
    // Calls the functions added to mo_oauth_avatar_manager_get_custom_avatar
    // filter hook.
    return apply_filters( 'mo_oauth_avatar_manager_get_custom_avatar', $avatar, $user_id, $size, $default, $alt );
}

/**
 * Returns the avatar for a user who provided a user ID or email address.
 *
 * @uses get_option() For getting values for a named option.
 * @uses mo_oauth_avatar_manager_get_options() For retrieving plugin options.
 * @uses get_userdata() For retrieving user data by user ID.
 * @uses mo_oauth_avatar_manager_get_custom_avatar() For retrieving user custom avatar
 * based on user ID.
 * @uses apply_filters() For calling the functions added to a filter hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param int|string|object $id_or_email A user ID, email address, or comment
 * object.
 * @param int $size Size of the avatar image
 * @param string $default URL to a default image to use if no avatar is
 * available.
 * @param string $alt Alternative text to use in image tag. Defaults to blank.
 * @return string <img> tag for the user's avatar.
 */
function mo_oauth_avatar_manager_get_avatar( $avatar = '', $id_or_email, $size = '', $default = '', $alt = false ) {
    // Returns if showing avatars is not enabled.
    if ( ! get_option( 'show_avatars' ) )
        return false;
 
    // Retrieves plugin options.
    //$options = mo_oauth_avatar_manager_get_options();
 
    if ( empty( $size ) || ! is_numeric( $size ) ) {
        //$size = $options['avatar-manager-default-size'];
		$size = 512;
    } else {
        $size = absint( $size );
 
        if ( $size < 1 )
            $size = 1;
        elseif ( $size > 512 )
            $size = 512;
    }
 
    $email = '';
 
    if ( is_numeric( $id_or_email ) ) {
        $id = (int) $id_or_email;
 
        // Retrieves user data by user ID.
        $user = get_userdata( $id );
 
        if ( $user )
            $email = $user->user_email;
    } elseif ( is_object( $id_or_email ) ) {
        if ( ! empty( $id_or_email->user_id ) ) {
            $id = (int) $id_or_email->user_id;
 
            // Retrieves user data by user ID.
            $user = get_userdata( $id );
 
            if ( $user )
                $email = $user->user_email;
        } elseif ( ! empty( $id_or_email->comment_author_email ) ) {
            $email = $id_or_email->comment_author_email;
        }
    } else {
        $email = $id_or_email;
 
        if ( $id = email_exists( $email ) )
            // Retrieves user data by user ID.
            $user = get_userdata( $id );
    }
 
    if ( isset( $user ) )
        $avatar_type = $user->mo_oauth_avatar_manager_avatar_type;
    else
        return $avatar;
 
    if ( $avatar_type == 'custom' )
        // Retrieves user custom avatar based on user ID.
        $avatar = mo_oauth_avatar_manager_get_custom_avatar( $user->ID, $size, $default, $alt );
 
    // Calls the functions added to mo_oauth_avatar_manager_get_avatar filter hook.
    return apply_filters( 'mo_oauth_avatar_manager_get_avatar', $avatar, $id_or_email, $size, $default, $alt );
}
 
add_filter( 'get_avatar', 'mo_oauth_avatar_manager_get_avatar', 10, 5 );

/**
 * Prevents custom avatars from being applied to the Default Avatar setting.
 *
 * @uses remove_filter() For removing a function attached to a specified action
 * hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param array $avatar_defaults An associative array with default avatars.
 * @return array An associative array with default avatars.
 */
function mo_oauth_avatar_manager_avatar_defaults( $avatar_defaults ) {
    // Removes the mo_oauth_avatar_manager_get_avatar function attached to get_avatar
    // action hook.
    remove_filter( 'get_avatar', 'mo_oauth_avatar_manager_get_avatar' );
 
    return $avatar_defaults;
}
 
add_filter( 'avatar_defaults', 'mo_oauth_avatar_manager_avatar_defaults', 10, 1 );

/**
 * Deletes an avatar image based on attachment ID.
 *
 * @uses get_post_meta() For retrieving attachment meta fields.
 * @uses wp_upload_dir() For retrieving path information on the currently
 * configured uploads directory.
 * @uses delete_post_meta() For deleting attachment meta fields.
 * @uses get_users() For retrieving an array of users.
 * @uses delete_user_meta() For deleting user meta fields.
 * @uses do_action() For calling the functions added to an action hook.
 *
 * @since Avatar Manager 1.0.0
 *
 * @param int $attachment_id An attachment ID
 */
function mo_oauth_avatar_manager_delete_avatar( $attachment_id ) {
    // Retrieves attachment meta field based on attachment ID.
    $is_custom_avatar = get_post_meta( $attachment_id, '_mo_oauth_avatar_manager_is_custom_avatar', true );
 
    if ( ! $is_custom_avatar )
        return;
 
    // Retrieves path information on the currently configured uploads directory.
    $upload_dir = wp_upload_dir();
 
    // Retrieves attachment meta field based on attachment ID.
    $custom_avatar = get_post_meta( $attachment_id, '_mo_oauth_avatar_manager_custom_avatar', true );
	
    if ( is_array( $custom_avatar ) ) {
        foreach ( $custom_avatar as $file ) {
            if ( ! $file['skip'] ) {
                $file = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file['url'] );
                @unlink( $file );
            }
        }
    }
 
    // Deletes attachment meta fields based on attachment ID.
    delete_post_meta( $attachment_id, '_mo_oauth_avatar_manager_custom_avatar' );
    delete_post_meta( $attachment_id, '_mo_oauth_avatar_manager_custom_avatar_rating' );
    delete_post_meta( $attachment_id, '_mo_oauth_avatar_manager_is_custom_avatar' );
	delete_post_meta( $attachment_id, '_wp_attached_file' );
	delete_post_meta( $attachment_id, '_wp_attachment_metadata' );
 
    // An associative array with criteria to match.
    $args = array(
        'meta_key'   => 'mo_oauth_avatar_manager_custom_avatar',
        'meta_value' => $attachment_id
    );
 
    // Retrieves an array of users matching the criteria given in $args.
    $users = get_users( $args );
 
    foreach ( $users as $user ) {
        // Deletes user meta fields based on user ID.
        delete_user_meta( $user->ID, 'mo_oauth_avatar_manager_avatar_type' );
        delete_user_meta( $user->ID, 'mo_oauth_avatar_manager_custom_avatar' );
    }
	wp_delete_post( $attachment_id, true );
    // Calls the functions added to mo_oauth_avatar_manager_delete_avatar action hook.
    do_action( 'mo_oauth_avatar_manager_delete_avatar', $attachment_id );
}
 
add_action( 'delete_attachment', 'mo_oauth_avatar_manager_delete_avatar' );
?>