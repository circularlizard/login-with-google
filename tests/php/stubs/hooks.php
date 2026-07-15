<?php
/**
 * Stubs for non-existent functions in WP.
 */

if ( ! function_exists( 'remove_filter' ) ) {

	function remove_filter( $tag, $callback, $priority = 10, $args = 0 ) { }
}

if ( ! function_exists( 'apply_filters_deprecated' ) ) {
	function apply_filters_deprecated( string $tag, array $args, string $version, string $replacement = '', string $message = '' ) {
		return $args[0] ?? [];
	}
}

if ( ! function_exists( 'wp_safe_remote_get' ) ) {
	function wp_safe_remote_get( $url, $args = null ) {
		if ( func_num_args() > 1 ) {
			return wp_remote_get( $url, $args );
		}
		return wp_remote_get( $url );
	}
}

if ( ! function_exists( 'wp_safe_remote_post' ) ) {
	function wp_safe_remote_post( $url, $args = null ) {
		if ( func_num_args() > 1 ) {
			return wp_remote_post( $url, $args );
		}
		return wp_remote_post( $url );
	}
}
