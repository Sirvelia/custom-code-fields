<?php

namespace CCF\Field;

class ImageField extends Field
{
	public function display()
	{
		wp_enqueue_media();
		ob_start(); ?>

		<p class="form-field _<?= $this->type ?>_field">
            <div x-data="imageField(section_fields, field_name + '_<?= $this->slug ?>','<?= $this->default_value ?>')">
				<label :for="field_name"><?= $this->name ?></label>
				<div class="image-field-wrapper">
					<input type="hidden" :name="field_name" :id="field_name" x-model="field_value">
					<div class="image-preview" style="margin: 10px 0;">
						<template x-if="image_url">
							<img :src="image_url" style="max-width: 150px; height: auto;">
						</template>
					</div>
					<button type="button" class="button" @click="openMediaLibrary()">
						<span x-text="field_value ? 'Change Image' : 'Select Image'"></span>
					</button>
					<button type="button" class="button" x-show="field_value" @click="field_value = ''; image_url = ''">
						Remove Image
					</button>
				</div>
			</div>
		</p>
		<?php echo ob_get_clean();
	}
    
    public function display_complex($parent='')
	{
		return $this->display($parent);
	}

	public function save($object_id, $context = 'post', $parent = '')
	{
		$key = $parent . '_' . $this->slug;
		$value = isset($_POST[$key]) ? intval($_POST[$key]) : 0;

		switch ($context) {
			case 'post':
			case 'product':
				if ($value > 0) {
					update_post_meta($object_id, $key, $value);
				} else {
					delete_post_meta($object_id, $key);
				}
				break;
			case 'user':
				if ($value > 0) {
					update_user_meta($object_id, $key, $value);
				} else {
					delete_user_meta($object_id, $key);
				}
				break;
			case 'term':
				if ($value > 0) {
					update_term_meta($object_id, $key, $value);
				} else {
					delete_term_meta($object_id, $key);
				}
				break;
			default:
				do_action('ccf/save_field/image', $object_id, $context, $key, $value);
				break;
		}
	}
}
