<?php

namespace CCF\Field;

class MultiSelectField extends Field
{

	private $options = [];

	public function __construct(string $type, string $slug, string $name)
	{
		parent::__construct($type, $slug, $name);
		$this->options = apply_filters('ccf_multiselect_' . $slug . '_options', []);
		$this->default_value = [];
	}

	public function display()
	{
		ob_start(); ?>
		<p x-data="{ 
                field_name: field_name + '_<?= $this->slug ?>', 
                field_value: section_fields[field_name] !== undefined ? section_fields[field_name] : <?= json_encode($this->default_value) ?>
            }"
			class="form-field _<?= $this->type ?>_field">
			<label :for="field_name"><?= $this->name ?></label>
			<select x-cloak
				multiple
				class="short"
				:name="field_name + '[]'"
				:id="field_name"
				x-model="field_value">
				<?php foreach ($this->options as $option_key => $option_value): ?>
					<option :value="'<?= $option_key ?>'"
						:selected="field_value.includes('<?= $option_key ?>')">
						<?= $option_value ?>
					</option>
				<?php endforeach; ?>
			</select>
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
		$value = isset($_POST[$key]) ? (array) $_POST[$key] : [];

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
                do_action('ccf/save_field/multiselect', $object_id, $context, $key, $value);
                break;
        }
	}
}
