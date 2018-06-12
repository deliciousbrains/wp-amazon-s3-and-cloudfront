<?php

namespace DeliciousBrains\WP_Offload_S3\Providers\Streams;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\S3\StreamWrapper;
use DeliciousBrains\WP_Offload_S3\Providers\AWS_Provider;

class AWS_S3_Stream_Wrapper extends StreamWrapper {

	/**
	 * Overrides so we don't check for stat on directories
	 *
	 * @param string $path
	 * @param int    $flags
	 *
	 * @return array
	 */
	public function url_stat( $path, $flags ) {
		$extension = pathinfo( $path, PATHINFO_EXTENSION );
		// If the path is a directory then return it as always existing.
		if ( ! $extension ) {
			return array(
				0         => 0,
				'dev'     => 0,
				1         => 0,
				'ino'     => 0,
				2         => 16895,
				'mode'    => 16895,
				3         => 0,
				'nlink'   => 0,
				4         => 0,
				'uid'     => 0,
				5         => 0,
				'gid'     => 0,
				6         => -1,
				'rdev'    => -1,
				7         => 0,
				'size'    => 0,
				8         => 0,
				'atime'   => 0,
				9         => 0,
				'mtime'   => 0,
				10        => 0,
				'ctime'   => 0,
				11        => -1,
				'blksize' => -1,
				12        => -1,
				'blocks'  => -1,
			);
		}

		return parent::url_stat( $path, $flags );
	}

	/**
	 * Override the S3 Put Object arguments
	 *
	 * @return bool
	 */
	public function stream_flush() {
		$context = stream_context_get_default();

		if ( null !== $this->context ) {
			$context = $this->context;
		}

		$options = stream_context_get_options( $context );

		// Set the ACL as public by default
		$options['ACL'] = AWS_Provider::DEFAULT_ACL;

		$options = apply_filters( 'wpos3_stream_flush_params', $options );

		stream_context_set_option( $context, $options );

		return parent::stream_flush();
	}
}