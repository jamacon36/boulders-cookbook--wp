<?php
require 'vendor/autoload.php';
include 'theme/theme.php';
include 'theme/classes/timber.php';
include 'theme/classes/admin.php';

new bouldersTheme();

if (is_admin()) {
  new bouldersAdmin();
}