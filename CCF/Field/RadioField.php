<?php

namespace CCF\Field;

class RadioField extends Field
{
    private $options = [];

    public function __construct(string $type, string $slug, string $name)
    {
        parent::__construct($type, $slug, $name);
        $this->options = apply_filters('ccf_radio_' . $slug . '_options', []);
        $this->default_value = '';
    }

    public function display()
    {
        ob_start(); ?>
        <p x-data="{ 
                field_name: field_name + '_<?= $this->slug ?>', 
                field_value: section_fields[field_name] !== undefined ? section_fields[field_name] : '<?= $this->default_value ?>' 
            }"
            class="form-field _<?= $this->type ?>_field">
            <label><?= $this->name ?></label>
        <div>
            <?php foreach ($this->options as $option_key => $option_value): ?>
                <label>
                    <input x-cloak
                        type="radio"
                        :name="field_name"
                        :id="field_name + '_<?= $option_key ?>'"
                        :value="'<?= $option_key ?>'"
                        x-model="field_value">
                    <?= $option_value ?>
                </label>
            <?php endforeach; ?>
        </div>
        </p>
<?php echo ob_get_clean();
    }


    public function set_options($options)
    {
        if (is_callable($options)) {
            $options = call_user_func($options);
        }
        $this->options = (array) $options;
        return $this;
    }

    public function add_options($options)
    {
        if (is_callable($options)) {
            $options = call_user_func($options);
        }
        $this->options = array_merge($this->options, (array) $options);
        return $this;
    }

    public function save($object_id, $context = 'post', $parent = '')
    {
        $key = $parent . '_' . $this->slug;
        $value = isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : '';

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
                do_action('ccf/save_field/radio', $object_id, $context, $key, $value);
                break;
        }
    }
}
