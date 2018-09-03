<?php
if (!class_exists('Timber')) {
    //Timber debug message
    add_action('admin_notices', function () {
        echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url(admin_url('plugins.php#timber')) . '">' . esc_url(admin_url('plugins.php')) . '</a></p></div>';
    });
    return;
}

// This tells Timber which directory our views live in
Timber::$dirname = array('templates');

class bouldersTimber extends TimberSite
{
    function __construct() {
        // Filters
        add_filter('timber_context', array($this, 'add_to_context'));
        add_filter('get_twig', array($this, 'add_to_twig'));
    }

    // Theme Specific
    function add_to_context($context) {
        $context['primary_menu'] = new TimberMenu('primary_menu');
        $context['secondary_menu'] = new TimberMenu('secondary_menu');
        $context['footer_menu'] = new TimberMenu('footer_menu');
        $context['site'] = $this;
        $context['theme_mods'] = get_theme_mods(); // Get theme modification

        // Page Specific Contexts

        return $context;
    }
    
    function add_to_twig($twig) {
        // Register custom twig functions
        $twig->addExtension(new Twig_Extension_StringLoader());
        $twig->addExtension(new Twig_Extension_Debug());

        // Dumps objects to console log
        $consoleLog = new Twig_SimpleFunction('console', function ($obj = null) {
            //Console Log a mixed var, only on localhost
            $is_local = !strpos($_SERVER['HTTP_HOST'], 'local'); //returns 0 if localhost
            if ($is_local && $obj) {
                echo '<script type="text/javascript">console.log(' . json_encode($obj) . ');</script>';
            }
        });
        $twig->addFunction($consoleLog);

        // Check for return Specific Query Args
        $getQueryArg = new Twig_SimpleFunction('check_query_args', function($params = false) {
            if ($params) {
                if (is_array($params)) {
                    $params = array();
                    for ($i = 0; $i < count($params); $i++) {
                        $params[$params[$i]] = bouldersTheme::get_param($params[$i]);
                    }
                    return $params;
                } else {
                    return bouldersTheme::get_param($params);
                }
            }

            return $params;
        });
        $twig->addFunction($getQueryArg);

        // Apply WP Auto P
        $format = new Twig_SimpleFunction('parag', function($text) {
            return do_shortcode(wpautop( $text, false ));
        });
        $twig->addFunction($format);

        // Display WP Login Form
        $wc_login = new Twig_SimpleFunction('wc_login', function() {
            $args = array(
                'echo' => false
            );
            return wp_login_form($args);
        });
        $twig->addFunction($wc_login);

        return $twig;
    }
}