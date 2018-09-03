<?php
/**
 * The template for displaying the front page.
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = new TimberPost();
//This will won't be loaded as the home page is static, but still throws error
//IMPORTANT NOTICE:: Put logic here, even if template is setup, context is loaded from here
$templates = array('home.twig');
Timber::render($templates, $context, false);
