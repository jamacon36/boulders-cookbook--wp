<?php
// ini upload and execution time shoe leather
@ini_set('upload_max_size', '64M');
@ini_set('post_max_size', '64M');
@ini_set('max_execution_time', '300');
@ini_set('memory_limit', '500M');

class bouldersTheme
{
    public $scriptsVersion;
    
    function __construct() {
        // Theme Supports
        add_theme_support('post-formats');
        add_theme_support('post-thumbnails');
        add_theme_support('menus');

        // Scripts Version Number
        // Structure: Site Wide Version.Core Functionality.Feature Set.Revision Count
        $this->scriptsVersion = '0.1.0';

        // Filters
        add_filter('login_redirect', array($this, 'clean_login_redirect'), 10, 3);
        add_filter('style_loader_src', array($this, 'override_wp_script_version'), 10, 1 );
        add_filter('script_loader_src', array($this, 'override_wp_script_version'), 10, 1 );

        // Actions and Theme Setup
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'short_codes'));
        add_action('init', array($this, 'register_custom_taxonomies'));
        add_action('customize_register', array($this, 'theme_customizer'));
        add_action('wp_enqueue_scripts', array($this, 'theme_enqueue_scripts_and_styles'));

        // User Triggered Actions
        add_action('wp_ajax_get_blog_posts', array($this, 'get_blog_posts'));
        add_action('wp_ajax_nopriv_get_blog_posts', array($this, 'get_blog_posts'));
        add_action('login_enqueue_scripts', array($this, 'customize_login'));
        add_action('wp_login_failed', array($this, 'failed_login_handling'));
        
        // Initiate Timber
        new bouldersTimber();
    }

    // Failed Login Redirect
    function failed_login_handling($username) {
        $referer = $_SERVER['HTTP_REFERER'];
        if (!empty($referer) && !strstr($referer, 'wp-login') && !strstr($referer, 'wp-admin')) {
            wp_redirect( add_query_arg('login', 'failed', $referer) );
            exit();
        }
    }

    // Personalize WP Login
    function customize_login() {
        ?>
        <style>
        </style>
        <?php
    }
    
    // Successful Login after Failure
    function clean_login_redirect($redirect, $request, $user) {
        $url = parse_url($redirect);
        return $url['path'];
    }
    
    // Custom Post Type
    function add_post_type($name, $names, $dashicon, $position, $public = true, $add_support = false) {
        $typeArgs = array(
            'labels' => array(
                'name' => $names,
                'singular_name' => $name,
                'add_new' => 'Add ' . $name,
                'add_new_item' => 'Add New ' . $name,
                'edit_item' => 'Edit ' . $name,
                'new_item' => 'New ' . $name,
                'view_item' => 'View ' . $name,
                'search_items' => 'Search ' . $names,
                'not_found' => 'No '. $names .' found',
                'not_found_in_trash' => 'No '. $names .' in the trash',
                'all_items' => 'All ' . $names,
                'archives' => 'Archived ' . $names,
                'insert_into_item' => 'Insert into ' . $name,
                'uploaded_to_this_item' => 'Upload to ' . $name,
                'featured_image' => 'Featured Image',
                'set_featured_image' => 'Set Featured Image',
                'remove_featured_image' => 'Remove Featured Image',
                'use_featured_image' => 'Use as featured image'
            ),
            'description' => 'An object that includes all details about a '. $name .' type',
            'exclude_from_search' => false,
            'publicly_queryable' => $public,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_in_menu' => true,
            'show_in_admin_bar' => false,
            'menu_position' => $position,
            'menu_icon' => $dashicon,
            'taxonomies' => array(
                'category'
            ),
            'supports' => array('title')
        );
        if (is_array($add_support)) {
            foreach ($add_support as $support) {
                $typeArgs['supports'][] = $support;
            }
        }
        $type = array(
            'name' => $name,
            'args' => $typeArgs
        );
        register_post_type($type['name'], $type['args']);
    }

    // Custom Taxonomy
    function custom_taxonomy($name, $names, $desc, $obj_assoc = 'post', $public = true, $has_kids = false) {
        $tax_args = array(
            'labels' => array(
                'name' => $names,
                'singular_name' => $name,
                'all_items' => 'All ' . strtolower($names),
                'edit_item' => 'Edit ' . strtolower($name),
                'view_item' => 'View ' . strtolower($name),
                'add_new_item' => 'Add new ' . strtolower($name),
                'new_item_name' => 'New ' . strtolower($name),
                'search_items' => 'Search ' . strtolower($names),
                'popular_items' => 'Popular ' . strtolower($names),
                'not_found' => 'No ' . strtolower($names) . ' found'
            ),
            'public' => $public,
            'hierarchical' => $has_kids,
            'show_admin_column' => true
        );
        if ($has_kids) {
            $tax_args['labels']['parent_item'] = 'Parent ' . strtolower($name);
        } else {
            array_merge($tax_args['labels'], array(
                'separate_items_with_commas' => 'Separate ' . strtolower($names) . ' with commas',
                'add_or_remove_items' => 'Add or remove ' . strtolower($names),
                'choose_from_most_used' => 'Choose from most used ' . strtolower($names)
            ));
        }
        register_taxonomy( strtolower(str_replace(' ', '_', $name)), $obj_assoc, $tax_args );
    }

    // Add Short Codes
    function short_codes() {

    }

    // Enforce Custom Scripts Version
    function override_wp_script_version($src) {
        if (strpos($src, '/boulders/js/main.js') || strpos($src, '/boulders/style.css')){
            if (strpos($src, 'ver=' . get_bloginfo( 'version' ))){
                $src = remove_query_arg( 'ver', $src );
                $src = add_query_arg( 'ver' , $this->scriptsVersion, $src );
            }
        }
        return $src;
    }

    // Localize and enqueue scripts and styles
    function theme_enqueue_scripts_and_styles() {
        global $params;

        // Base Level JS Localizations
        $js_localized = array('user' => wp_get_current_user()->ID, 'ajaxUrl' => admin_url('admin-ajax.php'));

        // Globally used styles and scripts
        wp_enqueue_style('font_awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.6.3/css/font-awesome.min.css');
        wp_enqueue_style('main_stylesheet', get_stylesheet_directory_uri() . '/style.css');

        // Page Specific Scripts

        // This is the final, minified script
        wp_register_script('vendor_bundle', get_stylesheet_directory_uri() . '/js/bundle.js');
        wp_register_script('main_js_file',   get_stylesheet_directory_uri() . '/js/main.js', array('jquery'), false, true);
        wp_localize_script('main_js_file', 'wpTheme', $js_localized);
        wp_enqueue_script('main_js_file');
    }

    // Add Custom Post Types
    function register_post_types() {
        self::add_post_type('Recipe', 'Recipes', 'dashicons-carrot', 25, true, $add_support = ['title', 'editor', 'author', 'revisions', 'thumbnail']);
    }

    // Add Custom Taxonomies
    function register_custom_taxonomies() {
        
    }

    function gather_cust_sections() {
        // Customize Theme Visual Editor
        // Create New Sections to house controls
        $sections = array();

        return $sections;
    }

    // Create Settings for each controls
    function gather_cust_settings() {
        $settings = array();

        return $settings;
    }

    function gather_cust_controls() {
        // Create controls
        $controls = array();

        return $controls;
    }

    function theme_customizer($wp_customize) {
        // Where the magic happens. Runs loops on all of the cusomizer gather functions and adds sections and controls to WP.
        $sections = self::gather_cust_sections();
        $settings = self::gather_cust_settings();
        $controls = self::gather_cust_controls();

        foreach ($sections as $section) {
            $wp_customize->add_section($section['id'], $section['args']);
        }

        foreach ($settings as $setting) {
            $wp_customize->add_setting($setting['id'], $setting['args']);
        }

        foreach ($controls as $control) {
            $wp_customize->add_control(
                new WP_Customize_Control(
                    $wp_customize,
                    $control['args']['settings'],
                    $control['args']
                )
            );
        }
    }
    
    function post_query($query) {
        if ($query->is_main_query() && !is_admin()) {
            $query->set('post_type', array('movie', 'post'));
        }
    }

    function get_param($name, $source = 'GET', $format = false) {
        $_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
        $value = false;
        if ($source == 'GET') {
            $value = $format ? json_decode(stripslashes($_GET[$name]), true) : $_GET[$name];
        } else if ($source == 'POST') {
            $value = $format ? json_decode(stripslashes($_POST[$name]), true) : $_POST[$name];
        }
        return $value;
    }

    function get_blog_taxonomies() {
        return get_categories('taxonomy=category&type=post');
    }
}
