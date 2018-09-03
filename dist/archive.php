<?php
/**
 * The template for displaying archives
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

$templates = array( 'archive/archive-' . $post->post_type . '.twig', 'archive/archive.twig' );
$context = Timber::get_context();
$context['pagination'] = Timber::get_pagination();

if (is_day()){
  $context['title'] = 'Archive: '.get_the_date( 'D M Y' );
} else if (is_month()){
  $context['title'] = 'Archive: '.get_the_date( 'M Y' );
} else if (is_year()){
  $context['title'] = 'Archive: '.get_the_date( 'Y' );
} else if (is_tag()){
  $context['title'] = single_tag_title('', false);
} else if (is_category()){
  $context['title'] = single_cat_title('', false);
  array_unshift($templates, 'archive-'.get_query_var('cat').'.twig');
} else if (is_post_type_archive()){
  $context['title'] = post_type_archive_title('', false);
  array_unshift($templates, 'archive-'.get_post_type().'.twig');
} else if (is_author()) {
  $context['title'] = 'Author - ';
}

$context['count'] = $GLOBALS['wp_query']->found_posts;

Timber::render($templates, $context);