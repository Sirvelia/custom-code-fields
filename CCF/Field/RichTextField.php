<?php

namespace CCF\Field;

class RichTextField extends Field
{
    private $rows = 4;

    public function display()
    {
        // Generate a unique ID for the editor to avoid conflicts
        $editor_id = 'editor_' . $this->slug;

        wp_enqueue_media();

        ob_start(); ?>
        <div x-data="{ 
                field_name: field_name + '_<?= esc_attr($this->slug) ?>', 
                field_value: section_fields[field_name] !== undefined ? section_fields[field_name] : '<?= esc_js($this->default_value) ?>' 
            }"
            class="form-field _<?= esc_attr($this->type) ?>_field">
            <label :for="field_name"><?= esc_html($this->name) ?></label>
            <div :id="'<?= esc_attr($editor_id) ?>'" x-init="
                $nextTick(() => {
                    // Initialize the TinyMCE editor
                    wp.editor.initialize('<?= esc_js($editor_id) ?>', {
                        tinymce: {
                            wpautop: true,
                            plugins: 'lists,link,wordpress,wpautoresize,wpeditimage',
                            toolbar1: 'formatselect,bold,italic,underline,bullist,numlist,link,unlink,wp_adv',
                            toolbar2: 'alignleft,aligncenter,alignright,alignjustify,forecolor,wp_help'
                        },
                        quicktags: true,
                        mediaButtons: true
                    });
    
                    // Get the TinyMCE instance
                    const editorInstance = tinymce.get('<?= esc_js($editor_id) ?>');
    
                    // Watch for changes in section_fields and update editor value if needed
                    $watch('section_fields', (newVal) => {
                        if (newVal[field_name] !== undefined && editorInstance) {
                            editorInstance.setContent(newVal[field_name]);
                            field_value = newVal[field_name];
                        }
                    });
    
                    // Update Alpine.js field_value when the editor content changes
                    if (editorInstance) {
                        editorInstance.on('change keyup', () => {
                            field_value = editorInstance.getContent();
                        });
                    }
                });
            ">
                <?php
                wp_editor(
                    $this->default_value,
                    $editor_id,
                    [
                        'textarea_name' => $this->slug,
                        'textarea_rows' => $this->rows,
                        'teeny' => false,
                        'media_buttons' => true,
                    ]
                );
                ?>
            </div>
        </div>
<?php echo ob_get_clean();
    }


    public function rows($rows)
    {
        $this->rows = sanitize_text_field($rows);
        return $this;
    }

    public function save($object_id, $context = 'post', $parent = '')
    {
        $key = $parent . '_' . $this->slug;
        $value = isset($_POST[$key]) ? wp_kses_post($_POST[$key]) : '';

        switch ($context) {
            case 'post':
            case 'product':
                update_post_meta($object_id, $key, $value);
                break;
            case 'user':
                update_user_meta($object_id, $key, $value);
                break;
            case 'term':
                update_term_meta($object_id, $key, $value);
                break;
            default:
                do_action('ccf/save_field/rich_text', $object_id, $context, $key, $value);
                break;
        }
    }
}
