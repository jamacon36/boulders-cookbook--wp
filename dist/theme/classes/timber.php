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

class xcellTimber extends TimberSite
{
    function __construct() {
        // Filters
        add_filter('timber_context', array($this, 'add_to_context'));
        add_filter('get_twig', array($this, 'add_to_twig'));
    }

    // BioX Specific
    // Add Subscriber to Mailerlite
    function add_to_mailerlite($subscriber) {
        require_once __DIR__ . '/../keys.php';
        $groupsApi = (new MailerLiteApi\MailerLite($keys->mailerlite['api']))->groups();
        $subscribersApi = (new MailerLiteApi\MailerLite($keys->mailerlite['api']))->subscribers();

        $mlSubscriber = $subscribersApi->find($subscriber['email']);
        if (isset($mlSubscriber->error)) {
            $gid = 9554374; // Hard set to BioXCell main for now
            $toAdd = array(
                'email' => $subscriber['email'],
                'fields' => array(
                    'name' => $subscriber['fname'],
                    'last_name' => $subscriber['lname'],
                    'company' => $subscriber['company']
                )
            );
            $subAdded = $groupsApi->addSubscriber($gid, $toAdd);
            if (isset($subAdded->error)) {
                return false;
            } else {
                return true;
            }
        } else {
            return $mlSubscriber;
        }
    }
    
    function add_to_context($context) {
        $context['primary_menu'] = new TimberMenu('primary_menu');
        $context['secondary_menu'] = new TimberMenu('secondary_menu');
        $context['footer_menu'] = new TimberMenu('footer_menu');
        $context['site'] = $this;
        $context['theme_mods'] = get_theme_mods(); // Get theme modification
        $context['user'] = xcellStore::get_user();
        $context['path'] = get_stylesheet_directory_uri();
        $context['cart'] = xcellStore::get_cart('cart');
        $context['quote'] = xcellStore::get_cart('quote');

        // Page Specific Contexts
        if (is_page( 'my-account' ) && $context['user']) {
            $context['user'] = xcellStore::get_user_addresses($context['user']);
            $context['user']->orders = xcellStore::get_cust_orders($context['user']);

        } else if (is_page( 'contract-products' )) {
            $context['contract'] = xcellStore::get_contract_products();

        } else if (is_page('checkout')) {
            if ($context['user']) {
                $context['user'] = xcellStore::get_user_addresses($context['user']);
            }

        } else if (is_page( 'request-quote' )) {
            if ($context['user']) {
                $context['user'] = xcellStore::get_user_addresses($context['user']);
            }

        } else if (is_page( 'thank-you' )) {
            $context['order'] = xcellStore::get_order(xcellTheme::get_param('order'));
        } else if (is_page('promotions')) {
            $context['posts'] = Timber::get_posts(array('posts_per_page' => -1, 'post_type' => 'promotion'));
            $context['featured_post'] = $context['posts'][0];
            unset($context['posts'][0]);
        }

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

        // Grab Carbon Field Value
        $carbonize = new Twig_SimpleFunction('carbonize', function($post, $name, $type=null){
            return carbon_get_post_meta($post->ID, $name, $type);
        });
        $twig->addFunction($carbonize);

        // Get Product Categories
        $category_list = new Twig_SimpleFunction('wc_categories', function() {
            $args = array(
                'taxonomy'     => 'product_cat',
                'orderby'      => 'name',
                'show_count'   => 0,
                'pad_counts'   => 0,
                'hierarchical' => 1,
                'title_li'     => '',
                'hide_empty'   => 0
            );
            $category_list = get_categories($args);
            $parentCats = array(
                '268' => 'Cell Type',
                '267' => 'Area of Research',
                '265' => 'Reactivity',
                '266' => 'Application'
            );
            $categories = array(
                'Cell Type' => array(),
                'Area of Research' => array(),
                'Reactivity' => array(),
                'Application' => array()
            );
            for ($c = 0; $c < count($category_list); $c++) {
                $cat = $category_list[$c];
                if ($cat->parent) {
                    $categories[$parentCats[(string)$cat->parent]][strtolower($cat->cat_name)] = $cat;
                }
            }

            return $categories;
        });
        $twig->addFunction($category_list);

        // Check for return Specific Query Args
        $getQueryArg = new Twig_SimpleFunction('check_query_args', function($params = false) {
            if ($params) {
                if (is_array($params)) {
                    $params = array();
                    for ($i = 0; $i < count($params); $i++) {
                        $params[$params[$i]] = xcellTheme::get_param($params[$i]);
                    }
                    return $params;
                } else {
                    return xcellTheme::get_param($params);
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

        // Get Reasearch Posts for Footer
        $footer_posts = new Twig_SimpleFunction('footer_posts', function() {
            $args = array(
                'posts_per_page' => 3,
                'category_name' => 'research'
            );
            return get_posts( $args );
        });
        $twig->addFunction($footer_posts);

        // Get WC Countries
        $wc_countries = new Twig_SimpleFunction('wc_countries', function() {
            $wcc = new WC_Countries();
            return $wcc->get_countries();
        });
        $twig->addFunction($wc_countries);

        // Get WC States by Country Code
        $wc_states = new Twig_SimpleFunction('wc_states', function($country) {
            $states = [];
            if (!empty($country)) {
                $wcc = new WC_Countries();
                $states = $wcc->get_states($country);
            }

            return $states;
        });
        $twig->addFunction($wc_states);

        // Get a batch of posts by array of IDs
        $twgGetPosts = new Twig_SimpleFunction('get_post_batch', function($ids = null, $rand = false) {
            if (is_array($ids)) {
                $posts = array();
                foreach ($ids as $id) {
                    $id = intval($id);
                    $posts[] = get_post( $id, 'OBJECT', 'display' );
                }
                if ($rand) {
                    shuffle($posts);
                }
                return $posts;
            }

            return false;
        });
        $twig->addFunction($twgGetPosts);
        
        // One Use CSV Import
        $csv = new Twig_SimpleFunction('csv_import', function() {
            $files = array(
                '161216InVivoMabProductInfo',
                '161216InVivoPlusProductInfo',
                '161227InVivoMabIsotypeControlProductInfo',
                '161227InVivoPlusIsotypeControlProductInfo',
                '161228 InVivoMab Fusion Protein Product Info',
                '161228 InVivoPlus Fusion Protein Product Info',
                '170228 recommended dilution buffer'
            );
            $products = array();
            for ($i = 0; $i < count($files); $i++) {
                $file = $files[$i];
                $bc_prods = array_map('str_getcsv', file(dirname(__FILE__) . '/../media/'. $file .'.csv'));
                $bc_header = $bc_prods[0];
                for ($v = 1; $v < count($bc_prods); $v++) {
                    $product = array_combine($bc_header, $bc_prods[$v]);
                    if ($product['Catalog Number']) {
                        $catNumber = $product['Catalog Number'];
                        if (!empty($catNumber)) {
                            if (isset($products[$catNumber])) {
                                foreach($product as $field => $val) {
                                    $products[$catNumber][$field] = $val;
                                }
                            } else {
                                $products[$catNumber] = $product;
                            }
                        }
                    } else if ($product['Product Code/SKU']) {
                        if ($product['Item Type'] == 'Product') {
                            $products[$product['Product Code/SKU']]['Search'] = $product['Search Keywords'];
                            $products[$product['Product Code/SKU']]['BCat'] = $product['Category'];
                            $products[$product['Product Code/SKU']]['Brand'] = $product['Brand Name'];
                        }
                    }
                }
            }
            
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => '-1',
                'tax_query' => array(
                    array(
                        'taxonomy'      => 'product_cat',
                        'terms'         => 152, // Igonore Custom Products
                        'operator'      => 'NOT IN' // Possible values are 'IN', 'NOT IN', 'AND'.
                    )
                )
            );

            $products_WP = new WP_Query($args);

            while($products_WP->have_posts()) {
                $products_WP->the_post();
                $meta = get_post_meta(get_the_ID());
                $sku = $meta['_sku'][0];
                $details = $products[$sku];
                if ($details) {
                    foreach ($details as $field => $val) {
                        $field = str_replace(':', '', $field);
                        if ($field == 'Immunogen') {
                            if ($meta) {
                                foreach ($meta as $mfield => $mval) {
                                    $key = null;
                                    if ($mval[0] == 'Immunogen') {
                                        $key = substr($mfield, -2);
                                        if (is_int($key)) {
                                            $key = substr($mfield, -1);
                                            $key = '_product_table_-_row_content_' . $key;
                                        } else {
                                            $key = '_product_table_-_row_content_' . $key;
                                        }
                                        update_post_meta( get_the_ID(), $key, $val );
                                    }
                                }
                            }
                        } 
                    }
                }
            }
        });
        $twig->addFunction($csv);

        // Get WC Order Object
        $get_order = new Twig_SimpleFunction('get_order', function($id) {
            return xcellStore::get_order($id);
        });
        $twig->addFunction($get_order);

        // Get WC Line Item Product Object
        $get_line_prod = new Twig_SimpleFunction('get_line_item', function($item) {
            return new WC_Order_Item_Product($item);
        });
        $twig->addFunction($get_line_prod);

        // One use Custom Amounts Variation Creator
        $cust = new Twig_SimpleFunction('cust_amounts', function() {
            $args = array(
                'post_type' => 'product',
                'posts_per_page' => '-1',
                'tax_query' => array(
                    array(
                        'taxonomy'      => 'product_cat',
                        'terms'         => 308, // Igonore Custom Products
                        'operator'      => 'NOT IN' // Possible values are 'IN', 'NOT IN', 'AND'.
                    )
                )
            );
            $products_raw = new WP_Query($args);

            while($products_raw->have_posts()) {
                $products_raw->the_post();
                $product = new WC_Product_Variable(get_the_ID());
                $parent_sku = $product->get_sku();
                $attributes = $product->get_attributes();
                $variations = $product->get_available_variations();
                $variantsExistinCount = count($variations);
                // Only catch products with the 8 core variants
                if ($variantsExistinCount == 8) {
                    $termsArray = explode(' | ', $attributes['price']['value']);
                    array_push($termsArray, 'Custom Amount: (Quote Only)');
                    $newTerms = implode(' | ', $termsArray);
                    $metaArgs = array(
                        wc_attribute_taxonomy_name('price') => array(
                            'name' => wc_attribute_taxonomy_name('price'),
                            'is_taxonomy' => 0,
                            'is_variation' => 1,
                            'is_visible' => 0,
                            'position' => '12',
                            'value' => $newTerms
                        )
                    );
                    wp_set_object_terms( get_the_ID(), $termsArray, wc_attribute_taxonomy_name('price') );
                    update_post_meta(get_the_ID(), '_product_attributes', $metaArgs);
                    $skutionary = array(
                        'A005mg' => 'Discounted Academic or Non profit: 5mg',
                        'A025mg' => 'Discounted Academic or Non profit: 25mg',
                        'A050mg' => 'Discounted Academic or Non profit: 50mg',
                        'A100mg' => 'Discounted Academic or Non profit: 100mg',
                        'A200mg' => 'Discounted Academic or Non profit: 200mg',
                        'R005mg' => 'Regular: 5mg',
                        'R025mg' => 'Regular: 25mg',
                        'R050mg' => 'Regular: 50mg',
                        'R100mg' => 'Regular: 100mg',
                        'R200mg' => 'Regular: 200mg',
                        'CUST' => 'Custom Amount: (Quote Only)'
                    );
                    foreach ($variations as $variant) {
                        $sku = $variant['sku'];
                        $variantType = explode('-', $sku);
                        $variantType = $variantType[count($variantType) - 1];
                        if (isset($skutionary[$variantType])) {
                            // Update Attributes Via Call
                            update_post_meta($variant['id'], 'attribute_' . wc_attribute_taxonomy_name('price'), $skutionary[$variantType]);
                            update_post_meta($variant['id'], 'name', $skutionary[$variantType]);
                        }
                    }
                    $custom_var = array(
                        'post_title'   => 'Product #' . get_the_ID() . ' Variation',
                        'post_content' => '',
                        'post_status'  => 'publish',
                        'post_parent'  => get_the_ID(),
                        'post_type'    => 'product_variation'
                    );

                    $custom_var_id = wp_insert_post($custom_var);
                    update_post_meta( $custom_var_id, 'name', $skutionary['CUST'] );
                    update_post_meta( $custom_var_id, '_regular_price', 0 );
                    update_post_meta( $custom_var_id, '_price', 0 );
                    update_post_meta( $custom_var_id, '_sku', $parent_sku . '-CUST' );
                    update_post_meta( $custom_var_id, 'attribute_' . wc_attribute_taxonomy_name('price'), $skutionary['CUST'] );

                    WC_Product_Variable::sync( get_the_ID() );
                }
            }
        });
        $twig->addFunction($cust);

        // Return Brands
        $brands = new Twig_SimpleFunction('xcell_brands', function (){
            return xcellStore::xcell_brands();
        });
        $twig->addFunction($brands);
        
        // Get WC Prodcut Link By ID
        $getProductById = new Twig_SimpleFunction('get_product_by_id', function($id) {
            return array('product' => new WC_Product(intval($id)), 'post' => get_post(intval($id)));
        });
        $twig->addFunction($getProductById);

        // Get WC Product Link by SKU
        $getProductBySku = new Twig_SimpleFunction('get_product_by_sku', function($skus) {
            $products = [];
            $skus = explode('<br/>', $skus);
            for ($i = 0; $i < count($skus); $i++) {
                $sku = $skus[$i];
                $products[] = get_post(wc_get_product_id_by_sku($sku));
            }
            return $products;
        });
        $twig->addFunction($getProductBySku);

        // Gravity Form
        $gravity_form = new Twig_SimpleFunction('gravity_form', function (
            $id_or_title,
            $display_title = true,
            $display_description = true,
            $display_inactive = false,
            $field_values = false,
            $ajax = false,
            $tabindex = 0,
            $echo = false) {
            return gravity_form(
                $id_or_title,
                $display_title,
                $display_description,
                $display_inactive,
                $field_values,
                $ajax,
                $tabindex,
                $echo);
        });
        $twig->addFunction($gravity_form);
        $twig->getExtension('core')->setDateFormat('F d, Y', '%d days');
        return $twig;
    }
}