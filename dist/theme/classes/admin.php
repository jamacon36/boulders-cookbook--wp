<?php
class bouldersAdmin extends bouldersTheme
{
    function __construct() {
        // Class Props
        $this->meta_boxes = array();

        // Filters

        // Actions
        add_action('admin_init', array($this, 'remove_post_support'));
        add_action('add_meta_boxes', array($this, 'add_custom_meta'));
        add_action('admin_head', array($this, 'style_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'), 10, 3);
        
        // Ajax Actions
    }

    // Remove Editor From Posts
    function remove_post_support() {
        remove_post_type_support( 'page', 'editor' );
    }

    // Normalize the look of custom metaboxes
    function style_meta_boxes() {
        ?>
        <style>
            .theme-metafield {
                width: 100%;
                padding: 5px;
                box-sizing: border-box;
            }
            .theme-metafield label {
                display: block;
            }
            .theme-metafield textarea, .theme-metafield input {
                width: 100%;
            }
        </style>
        <?
    }

    function make_meta_box($post, $args) {
        $fields = $args['args']['fields'];
        foreach ($fields as $field_meta) {
            self::make_meta_field($field_meta['type'], $field_meta['name'], $field_meta['placeholder'], $field_meta['values']);
        }
    }

    function save_meta_boxes($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can( 'edit_post', $post_id )) return;
        
        foreach ($_POST['meta'] as $meta) {
            if (isset($_POST[$meta['key']])) {
                update_post_meta( $post_id, $meta['key'], htmlentities($_POST[$meta['key']]) );
            }
        }
    }

    function fill_meta_field($field) {
        global $post;
        $value = self::old_order_meta($post->ID, array('name' => $field, 'value' => get_post_meta( $post->ID, $field, true )));
        if ($value && !empty($value)) {
            return $value;
        } else {
            add_post_meta($post->ID, $field, '', true);
            return '';
        }
    }

    function make_meta_field($type, $name, $placeholder, $values = array()) {
        $slug = strtolower(str_replace('.','', str_replace(' ', '_', $name)));
        $value = self::fill_meta_field($slug);
        switch ($type) {
            case 'text':
                # textbox...
                ?>
                <div class="theme-metafield">
                    <label for="<?= $slug ?>"><?= $name ?></label>
                    <input type="text" name="<?= $slug ?>" placeholder="<?= $placeholder ?>" id="<?= $slug ?>"<? if ($value): ?> value="<?= $value ?>"<? endif ?> />
                </div>
                <?
                break;
            case 'textarea':
                # textarea...
                ?>
                <div class="theme-metafield">
                    <label for="<?= $slug ?>"><?= $name ?></label>
                    <textarea name="<?= $slug ?>" id="<?= $slug ?>" placeholder="<?= $placeholder ?>"><? if ($value): ?><?= $value ?><? endif ?></textarea>
                </div>
                <?
                break;
            case 'wysiwyg':
                # WYSIWYG editor...
                ?>
                <div class="theme-metafield">
                    <label for="<?= $slug ?>"><?= $name ?></label>
                    <?
                    wp_editor(wpautop($value), 'meta_content_editor--' . $slug, array(
                        'wpautop' => true,
                        'media_buttons' => false,
                        'textarea_name' => $slug,
                        'teeny' => true
                    ))
                    ?>
                </div>
                <?
                break;
            case 'select':
                # select...
                ?>
                <div class="theme-metafield">
                    <label for="<?= $slug ?>"><?= $name ?></label>
                    <? if (count($values) > 0): ?>
                    <select name="<?= $slug ?>" id="<?= $slug ?>">
                    <? foreach ($values as $value => $vname) : ?>
                    <option value="<?= $value ?>"><?= $vname ?></option>
                    <? endforeach ?>
                    </select>
                    <? endif ?>
                </div>
                <?
                break;
        }
    }

    // Add Custom Meta Boxes
    function add_custom_meta() {
        global $post;
    }
}