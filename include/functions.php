<?php
if ( ! function_exists( 'as3cf_get_attachment_url' ) ) {
	/**
	 * Get the url of the file from provider, may be a signed expiring URL if associated file is set as private.
	 *
	 * NOTE: Returns false if attachment is not offloaded.
	 *
	 * @param int         $post_id            Post ID of the attachment, required.
	 * @param string|null $size               Size of the image to get, optional.
	 * @param bool        $skip_rewrite_check Always return the URL regardless of the 'Rewrite File URLs' setting, optional, default: false.
	 *
	 * @return string|bool|WP_Error
	 */
	function as3cf_get_attachment_url( $post_id, $size = null, $skip_rewrite_check = false ) {
		/** @var Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		return $as3cf->get_attachment_url( $post_id, null, $size, null, array(), $skip_rewrite_check );
	}
}
if ( ! function_exists( 'as3cf_get_secure_attachment_url' ) ) {
	/**
	 * Get the signed expiring url of the file from provider.
	 *
	 * NOTE: Returns false if attachment is not offloaded.
	 *
	 * @param int         $post_id            Post ID of the attachment, required.
	 * @param int|null    $expires            Seconds for the link to live, optional, default: 900 (15 minutes).
	 * @param string|null $size               Size of the image to get, optional.
	 * @param bool        $skip_rewrite_check Always return the URL regardless of the 'Rewrite File URLs' setting, optional, default: false.
	 *
	 * @return string|bool|WP_Error
	 */
	function as3cf_get_secure_attachment_url( $post_id, $expires = 900, $size = null, $skip_rewrite_check = false ) {
		/** @var Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		return $as3cf->get_secure_attachment_url( $post_id, $expires, $size, array(), $skip_rewrite_check );
	}
}