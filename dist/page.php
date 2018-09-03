<?php
/**
 * The template for displaying all pages.
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;
$post_name = strtolower(str_replace(' ', '-', trim($post->post_name)));;
$templates = array('page/page-' . $post_name . '.twig', 'page/page.twig');

Timber::render($templates, $context, false);
