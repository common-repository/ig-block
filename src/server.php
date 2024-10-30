<?php
function instagram_register_block() {

	// Only load if Gutenberg is available.
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	register_block_type('instagram/instagram', array(
		'render_callback' => 'instagram_render_callback',
			'attributes' => array(
					'numberCols' => array(
						'type' 		=> 'number',
						'default'	=> '4' // nb: a default is needed!
					),
					'token' => array(
							'type' 		=> 'string',
							'default' => ''
					),
					'hasEqualImages' => array(
						'type' 		=> 'boolean',
						'default' => false
					),
					'numberImages' => array(
						'type' 		=> 'number',
						'default' => 4
					),
					'gridGap' => array(
						'type' 		=> 'number',
						'default'	=> 0
					),
					'showProfile'	=> array(
						'type'		=> 'boolean',
						'default'	=> false
					),
					'backgroundColor'	=> array(
						'type'		=> 'string',
						'default'	=> 'transparent',
					),
			)
		)
	);

}

add_action('init', 'instagram_register_block');

/**
 * Generic data fetching wrapper
 * Uses the WP-API for fetching
 */
function instagram_fetchData($url) {
	$request = wp_remote_get( $url );

	if(is_wp_error( $request )) {
		return false;
	}

	return wp_remote_retrieve_body( $request );
}

/**
 * Caching functions
 * The number of images is used as a suffix in the case that the user
 * adds/removes images and expects a refreshed feed.
 */
function instagram_add_to_cache( $result, $suffix = '' ) {
	$expire = 6 * 60 * 60; // 6 hours in seconds
	set_transient( 'instagram-api_'.$suffix, $result, '', $expire );
}

function instagram_get_from_cache( $suffix = '' ) {
	return get_transient( 'instagram-api_'.$suffix );
}

/**
 * Server side rendering functions
 */
function instagram_render_callback( array $attributes ){
	$attributes = wp_parse_args(
		$attributes,
		[
			'token'           => '',
			'hasEqualImages'  => false,
			'numberImages'    => 4,
			'gridGap'         => 0,
			'showProfile'     => false,
			'backgroundColor' => '',
			'className'       => '',
		]
	);
	$token          = $attributes[ 'token' ]  ;
	$hasEqualImages = $attributes[ 'hasEqualImages' ] ? 'has-equal-images' : '';
	$numberImages   = $attributes[ 'numberImages' ];
	$numberCols     = $attributes[ 'numberCols' ];
	$gridGap        = $attributes[ 'gridGap' ];
	$showProfile    = $attributes[ 'showProfile' ];

	// get the user ID from the token
	$user 				= substr($token, 0, stripos($token, '.'));

	// create a unique id so there is no double ups
	$suffix 			= $user.'_'.$numberImages;

	if ( !instagram_get_from_cache() ) {
		// no valid cache found
		// hit the network
		$result = json_decode(instagram_fetchData("https://api.instagram.com/v1/users/self/media/recent/?access_token={$token}&count={$numberImages}"));
		if($showProfile) {
			$result->profile = json_decode(instagram_fetchData("https://api.instagram.com/v1/users/self?access_token={$token}"));
		}
		instagram_add_to_cache( $result, $suffix ); // add the result to the cache
	} else {
		$result = instagram_get_from_cache( $suffix ); // hit the cache
	}
	$thumbs 	= $result->data;
	$profile 	= ''; // our empty profile container
	$profileContainer = ''; // initialize as an empty string to prevent server errors

	if($showProfile) {
		$profile 	= $result->profile->data;

		$profileContainer = '<a href="https://instagram.com/'.$profile->username.'" target="_blank" class="instagram-profile-container display-grid">
			<div class="instagram-profile-picture-container">
				<img
					class="instagram-profile-picture"
					src="'.esc_attr($profile->profile_picture).'"
					alt="'.esc_attr($profile->full_name).'"
				/>
			</div>
			<div class="instagram-bio-container">
				<h3>'.$profile->username.'</h3>
				<p>'.$profile->bio.'</p>
			</div>
		</a>';
	}

	$imageContainer = '<div class="wp-block-cgb-instagram-instagram-for-gutenberg '.$attributes['className'].'">
	<div class="display-grid instagram-grid"
	style="grid-template-columns: repeat('.esc_attr($numberCols).', 1fr);
	margin-left: -'.esc_attr($gridGap).'px;
	margin-right: -'.esc_attr($gridGap).'px;
	grid-gap: '.esc_attr( $gridGap ).'px";
	>';

	foreach( $thumbs as $thumb ) {

		$image = esc_attr($thumb->images->standard_resolution->url);

		$imageContainer .= '
		<a class="instagram-image-wrapper '.$hasEqualImages.'" href="'.esc_attr($thumb->link).'"
		target="_blank" rel="noopener noreferrer"
		style="background-color: '.esc_attr($attributes['backgroundColor']).'">
			<img
			class="instagram-image"
			key="'.esc_attr($thumb->id).'"
			src="'.$image.'"
			alt="'.esc_attr($thumb->caption->text).'"
			/>
			<div class="instagram-image-overlay"></div>
		</a>';
	}

	return "{$profileContainer}{$imageContainer}</div></div>";
}
