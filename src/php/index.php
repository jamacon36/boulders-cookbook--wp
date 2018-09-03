<?php
/**
 * The main template file
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

if ( ! class_exists( 'Timber' ) ) {
  echo 'Timber not activated. Make sure you activate the plugin in <a href="/wp-admin/plugins.php#timber">/wp-admin/plugins.php</a>';
  return;
}
$context = Timber::get_context();
$context['pagination'] = Timber::get_pagination();
$context['categories'] = Timber::get_terms('category');
$context['sidebar'] = Timber::get_sidebar('sidebar.php');

$templates = array( 'index.twig' );
Timber::render( $templates, $context );