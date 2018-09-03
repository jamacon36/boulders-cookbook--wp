<?php
$context            = Timber::get_context();
$context['sidebar'] = Timber::get_widgets('shop-sidebar');

if (is_singular('product')) {

    $context['post'] = Timber::get_post();
    $product = new stdClass;
    $product->base = new WC_Product_Variable(get_the_ID());
    $product->meta = get_post_meta(get_the_ID());
    $variations = $product->base->get_available_variations();
    $academic_vars = array();
    $standard_vars = array();
    $other_vars = array();

    foreach ($variations as $variation) {
        $name = isset($variation['attributes']['attribute_pa_price']) ? $variation['attributes']['attribute_pa_price'] : $variation['attributes']['attribute_price'];
        $name = str_replace( '-', ' ', $name);
        $name = strtolower($name);
        if (strpos($name, 'academic') > -1) {
            $variation['attributes']['attribute_pa_price'] = str_replace( '-', ' ', $name);
            array_push($academic_vars, $variation);
        } else if (strpos($name, 'regular') > -1) { 
            $variation['attributes']['attribute_pa_price'] = str_replace( '-', ' ', $name);
            array_push($standard_vars, $variation);
        } else if (strpos($name, 'custom') > -1) {
            $variation['attributes']['attribute_pa_price'] = 'AMOUNT GREATER THAN 100 MG: (Quote Only)';
            array_push($other_vars, $variation);
        } else {
            $variation['attributes']['attribute_pa_price'] = str_replace( '-', ' ', $name);
            array_push($other_vars, $variation);
        }
    }
    function sortvars($a, $b) {
        $var1 = array();
        $var2 = array();
        preg_match('/([0-9]+)(?=mg)/', $a['attributes']['attribute_pa_price'], $var1);
        preg_match('/([0-9]+)(?=mg)/', $b['attributes']['attribute_pa_price'], $var2);

        $var1 = $var1[0];
        $var2 = $var2[0];

        if ($var1 == $var2) {
            return 0;
        }

        return ($var1 < $var2) ? -1 : 1;
    }

    usort($academic_vars, 'sortvars');
    usort($standard_vars, 'sortvars');
    sort($other_vars);

    $product->variations = array(
        'academic' => $academic_vars,
        'regular' => $standard_vars,
        'other' => $other_vars
    );
    $product->brand = wp_get_post_terms(get_the_ID(), 'brand')[0]->name;
    $product->description = get_the_excerpt( $context['post'] );
    $product->related = array(
        'dilution' => carbon_get_post_meta(get_the_ID(), 'product_dilution', 'relationship') ? xcellStore::make_product(false, intval(carbon_get_post_meta(get_the_ID(), 'product_dilution', 'relationship')[0])) : false,
        'isotype' => carbon_get_post_meta(get_the_ID(), 'product_isotype', 'relationship') ? xcellStore::make_product(false, intval(carbon_get_post_meta(get_the_ID(), 'product_isotype', 'relationship')[0])) : false,
        'research' => false, // @TODO: Convert to array of suggestions with matching research cat
        'application' => false // @TODO: Convert to array of suggestions with matching application cat
    );
    $product->image_gallery = !empty($product->base->get_image_id()) ? [wp_get_attachment_image_src(intval($product->base->get_image_id()), 'medium')[0]] : false;
    $context['product'] = $product;

    Timber::render('templates/single/single-product.twig', $context);

}