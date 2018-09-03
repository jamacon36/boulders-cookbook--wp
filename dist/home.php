<?php
/**
 * The template for displaying the home page.
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$context['posts'] = Timber::get_posts();
$context['pagination'] = Timber::get_pagination();
$context['categories'] = Timber::get_terms('category');
$context['featured_post'] = $context['posts'][0];
unset($context['posts'][0]);
$templates = array('page/page-' . $post->post_name . '.twig', 'index.twig', 'page/page.twig');
Timber::render($templates, $context);