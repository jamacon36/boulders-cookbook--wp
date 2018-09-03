<?php
/**
 * Search results page
 *
 * @package   WordPress
 * @subpackage  Timber
 * @since     Timber 0.1
 */
$templates = array( 'page/page-research-blog.twig', 'archive/archive.twig', 'index.twig' );
$context = Timber::get_context();
$context['blogs'] = xcellTheme::filter_blogs();
$context['categories'] = xcellTheme::get_blog_taxonomies();
$context['selected_category'] = $_GET['category'];
$context['search'] = $_GET['search'];
$context['offset'] = isset($_GET['offset']) && is_numeric($_GET['offset']) ? intval($_GET['offset']) : 1;
if (Timber::get_posts()) {
  $context['title'] = 'Search results for '. get_search_query();
} else {
  $context['title'] = 'No search results found for '. get_search_query();
}
$context['posts'] = Timber::get_posts();
$context['count'] = $GLOBALS['wp_query']->found_posts;

Timber::render($templates, $context);