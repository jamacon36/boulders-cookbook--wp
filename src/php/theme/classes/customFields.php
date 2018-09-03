<?php

class themeCustomFields
{
    function import_on_activation() {
        $json = file_get_contents(__DIR__ . '/../acf/acf-export.json');
        if ($json && function_exists('acf_add_local_field_group')) {
            $groups = json_decode( $json, true );
            foreach ($groups as $group) {
                acf_add_local_field_group($group);
            }
        }
        // Note: these will not appear in the acf editor. To have them there import the JSON file with the ACF import tool
    }
    function __construct() {
        add_action('acf/init', array($this, 'import_on_activation'), 15);
    }
}