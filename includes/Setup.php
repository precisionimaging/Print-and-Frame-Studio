<?php

namespace PrecisionImaging\PrintAndFrame;

class Setup
{
    protected Rest $rest;

    public function __construct()
    {
        $this->rest = new Rest();

        add_action('init', [ $this, 'register_post_types' ]);
        add_action('rest_api_init', [ $this, 'register_rest_routes' ]);
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_assets' ]);
    }

    public function register_post_types()
    {
        register_post_type('paf_asset', [
            'labels' => [
                'name' => __('Assets', 'print-and-frame-studio'),
                'singular_name' => __('Asset', 'print-and-frame-studio'),
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'editor', 'custom-fields'],
        ]);
    }

    public function register_rest_routes()
    {
        register_rest_route('paf/v1', '/assets', [
            'methods'  => 'GET',
            'callback' => [ $this->rest, 'get_assets' ],
            'permission_callback' => '__return_true', // Add this line
        ]);
    }

    public function enqueue_assets()
    {
        wp_enqueue_script('paf-configurator', plugin_dir_url(__FILE__) . '../build/configurator.js', ['wp-element'], '1.0.0', true);
    }
}