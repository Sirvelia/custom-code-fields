<?php

namespace CCF\Field;

class CodeEditorField extends Field
{
    private $language;
    private $indentUnit;
    private $useTabs;

    public function __construct(string $type, string $slug, string $name, string $language = 'javascript', int $indentUnit = 2, bool $useTabs = false)
    {
        parent::__construct($type, $slug, $name);
        $this->default_value = '';
        $this->language = $language;
        $this->indentUnit = $indentUnit;
        $this->useTabs = $useTabs;
    }

    public function set_language(string $language)
    {
        $this->language = $language;
        return $this;
    }

    public function set_indent(int $indentUnit)
    {
        $this->indentUnit = $indentUnit;
        return $this;
    }

    public function use_tabs(bool $useTabs)
    {
        $this->useTabs = $useTabs;
        return $this;
    }

    public function display()
    {

        // Enqueue CodeMirror scripts and styles if not already enqueued
        if (!wp_script_is('wp-codemirror', 'enqueued')) {
            wp_enqueue_script('wp-codemirror');
            wp_enqueue_style('wp-codemirror');
        }

        // Enqueue the CodeMirror settings script
        if (!wp_script_is('code-editor', 'enqueued')) {
            wp_enqueue_script('code-editor');
        }

        ob_start(); ?>
        <div x-data="{ 
                field_name: field_name + '_<?php echo $this->slug ?>', 
                field_value: section_fields[field_name] ? section_fields[field_name] : '<?php echo esc_js($this->default_value) ?>' 
            }"
            x-init="
                $nextTick(() => {
                const editor = wp.codeEditor.initialize(document.getElementById(field_name), {
                    codemirror: {
                        mode: '<?php echo esc_js($this->language) ?>',
                        lineNumbers: true,
                        indentUnit: <?php echo esc_js($this->indentUnit) ?>,
                        indentWithTabs: <?php echo $this->useTabs ? 'true' : 'false' ?>,
                        tabSize: <?php echo esc_js($this->indentUnit) ?>
                    }
                });

                // Watch for changes in section_fields and update editor value if needed
                $watch('section_fields', (newVal) => {
                    if (newVal[field_name] !== undefined) {
                        editor.codemirror.setValue(newVal[field_name]);
                        field_value = newVal[field_name];
                    }
                });

                // Update field_value when the editor content changes
                editor.codemirror.on('change', () => {
                    field_value = editor.codemirror.getValue();
                });
            })
            "
            class="form-field _<?php echo esc_attr($this->type) ?>_field">
            <label :for="field_name"><?php echo esc_html($this->name) ?></label>
            <textarea x-cloak
                :id="field_name"
                :name="field_name"
                class="code-editor short"
                style="width: 100%; height: 300px;"
                :value="field_value"></textarea>
        </div>
<?php echo ob_get_clean();
    }

    public function save($object_id, $context = 'product', $parent = '')
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
                do_action('ccf/save_field/code_editor', $object_id, $context, $key, $value);
                break;
        }
    }
}
