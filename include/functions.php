<?php
/**
 * Alias of as3cf_get_secure_attachment_url for backward compatibility
 * Will be depreated in a later version
 *
 * @since 2.0
 * @access public
 * @param mixed $post_id Post ID of the attachment or null to use the loop
 * @param int $expires Secondes for the link to live
 * @return array
 */
function wps3_get_secure_attachment_url( $post_id, $expires = 900, $deprecated = '' ) {
	return as3cf_get_secure_attachment_url( $post_id, $expires = 900 );
}

function as3cf_get_secure_attachment_url( $post_id, $expires = 900, $operation = 'GET' ) {

}