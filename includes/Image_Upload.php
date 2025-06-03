<?php
namespace PFS;

use WP_REST_Request;
use WP_REST_Server;

final class Image_Upload {

    private static $instance;

    public static function instance(): self {
        return self::$instance ?: ( self::$instance = new self() );
    }

    private function __construct() {
        add_action( 'rest_api_init', [ $this, 'register_route' ] );
    }

    public function register_route(): void {
        register_rest_route(
            'pfs/v1',
            '/upload',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'permission_callback' => '__return_true',  // tighten later
                'callback'            => [ $this, 'handle_upload' ],
            ]
        );
    }

	public function handle_upload( WP_REST_Request $req ) {

		if ( empty( $_FILES['file'] ) ) {
			return new \WP_Error( 'pfs_no_file', 'No file in request', [ 'status' => 400 ] );
		}

		/* 1 — pull in the helper that defines wp_handle_upload() */
		require_once ABSPATH . 'wp-admin/includes/file.php';

		/* 2 — call the global namespaced function */
		$handled = \wp_handle_upload( $_FILES['file'], [ 'test_form' => false ] );

		if ( isset( $handled['error'] ) ) {
			return new \WP_Error( 'pfs_upload_error', $handled['error'], [ 'status' => 500 ] );
		}

		return [
			'url'  => $handled['url'],
			'type' => $handled['type'],
		];
	}

}
