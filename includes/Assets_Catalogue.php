<?php
// /includes/Assets_Catalogue.php


namespace PFS;
final class Assets_Catalogue {
    private static $instance;
    public static function instance() { return self::$instance ?: ( self::$instance = new self() ); }
    private function __construct() {
        add_action( 'rest_api_init', function () {
            register_rest_route( 'pfs/v1', '/assets', [
                'methods' => 'GET',
                'callback' => [ $this, 'get_assets' ],
                'permission_callback' => '__return_true',
            ] );
        } );
    }
    public function get_assets() {
        $base = PFS_PLUGIN_URL . 'assets/frames/';
        $dirs = glob( PFS_PLUGIN_DIR . 'assets/frames/*', GLOB_ONLYDIR );
        $frames = [];
        foreach ( $dirs as $dir ) {
            $slug = basename( $dir );
            $pngs = [];
            foreach ( ['t','r','b','l','tl','tr','bl','br'] as $p ) {
                $pngs[$p] = $base . "$slug/{$slug}_{$p}.png";
            }
            $frames[] = [
                'slug'  => $slug,
                'name'  => ucwords( str_replace('-', ' ', $slug) ),
                'png'   => $pngs,
                'rate_ui' => 6.5,         // placeholder; later from CPT meta
            ];
        }
        $mats = [
            [ 'slug'=>'snow',   'name'=>'Snow White',    'hex'=>'#fefefe' ],
            [ 'slug'=>'char',   'name'=>'Charcoal',      'hex'=>'#2b2b2b' ],
            [ 'slug'=>'museum', 'name'=>'Museum White',  'hex'=>'#f7f6f2' ],
        ];
        return [ 'frames'=>$frames, 'mats'=>$mats ];
    }
}
