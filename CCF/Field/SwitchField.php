<?php

namespace CCF\Field;

class SwitchField extends Field
{
	public function __construct(string $type, string $slug, string $name)
	{
		parent::__construct($type, $slug, $name);
		$this->default_value = '0'; // Default is off (unchecked)
	}

	public function display()
	{
		ob_start(); ?>
		<p x-data="{ 
                field_name: field_name + '_<?= $this->slug ?>', 
                field_value: section_fields[field_name] !== undefined ? section_fields[field_name] : '<?= $this->default_value ?>' 
            }"
			class="form-field _<?= $this->type ?>_field">
			<label :for="field_name"><?= $this->name ?></label>
			<input x-cloak
				type="checkbox"
				class="switch"
				:name="field_name"
				:id="field_name"
				x-model="field_value"
				:value="field_value"
				@change="field_value = $el.checked ? '1' : '0'">
		</p>
<?php echo ob_get_clean();
	}

	public function save($object_id, $context = 'post', $parent = '')
	{
		$key = $parent . '_' . $this->slug;

		switch ($context) {
            case 'post':
            case 'product':
				update_post_meta($object_id, $key, isset($_POST[$key]) ? '1' : '0');
                break;
            case 'user':
				update_user_meta($object_id, $key, isset($_POST[$key]) ? '1' : '0');
                break;
            case 'term':
				update_term_meta($object_id, $key, isset($_POST[$key]) ? '1' : '0');
                break;
            default:
                do_action('ccf/save_field/switch', $object_id, $context, $key, isset($_POST[$key]) ? '1' : '0');
                break;
        }
	}
}
