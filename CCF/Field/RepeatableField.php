<?php

namespace CCF\Field;

class RepeatableField
{
    private $slug;
    private $name;
    private $fields;

    public function __construct(string $slug, string $name, $fields)
    {
        $this->slug = $slug;
        $this->name = $name;
        if (is_callable($fields)) $fields = call_user_func($fields);
        $this->fields = (array) $fields;
    }

    public static function create(string $slug, string $name, $fields)
    {
        return (new self($slug, $name, $fields));
    }

    public function display()
    {

        ob_start() ?>
        <style>
            .wp-editor-area {
                color: black !important;
            }
        </style>
        <div x-data='{ field_name: field_name + "_" + "<?php echo $this->slug; ?>"}'>
            <div x-init="$watch('section_fields', (value, oldValue) => {tabs = value[field_name] ? parseInt(value[field_name]) : 0; selected_tab = value[field_name] ? 0 : -1})" x-data='{ tabs: section_fields[field_name] ? parseInt(section_fields[field_name]) : 0, selected_tab: section_fields[field_name] ? 0 : -1 }' x-cloak style="margin-right: 9px; margin-bottom: 9px">
                <p style="font-size: 16px; font-weight: bold;"><?= $this->name ?></p>
                <div style="display: flex; padding-left: 9px; padding-right: 9px; flex-wrap: wrap;">
                    <template x-for="tab in [...Array(tabs).keys()]">
                        <div :style="'padding: 10px 15px; margin: 0; font-size: 16px; font-weight: bold; border: 1px gainsboro solid; cursor: pointer;' + (selected_tab === tab ? 'border-bottom-color: white;' : 'background-color: ghostwhite;')" @click="selected_tab = tab;" x-text="tab + 1"></div>
                    </template>
                    <div :style="'padding: 10px 15px; margin: 0; font-size: 16px; font-weight: bold; border: 1px gainsboro solid; cursor: pointer;' + (tabs === 0 ? 'border-bottom-color: white;' : 'background-color: ghostwhite;')" @click="selected_tab = tabs; tabs += 1;">+</div>
                </div>
                <input type="hidden" :value="tabs" :name="field_name" :id="field_name">
                <template x-for="tab in [...Array(tabs).keys()]">
                    <div>
                        <div x-data='{field_name: field_name + "_"  + tab}' :style="selected_tab === tab ? 'margin-left: 9px; padding: 9px; border: 1px gainsboro solid; display: flex; flex-direction: column;' : 'display: none'">
                            <?php foreach ($this->fields as $field): ?>
                                <?php $field->display(); ?>
                            <?php endforeach; ?>
                            <div style="display: flex; justify-content: end;">
                                <span class="dashicons dashicons-trash" style="cursor: pointer" @click="tabs -= 1; selected_tab -= 1;"></span>
                            </div>
                        </div>
                    </div>
                </template>
                <div x-show="tabs === 0" style="margin-left: 9px; border: 1px gainsboro solid; font-size: 16px;">
                    <p style="font-size: 16px;">There are no entries yet.</p>
                    <button type="button" style="margin: 9px; padding: 9px; cursor: pointer;" @click="selected_tab = tabs; tabs += 1;">Add Entry</button>
                </div>
            </div>
        </div>
<?php echo ob_get_clean();
    }

    public function save($object_id, $context = 'product', $parent = '')
    {
        $key = $parent . '_' . $this->slug;
        if (isset($_POST[$key])) { // phpcs:ignore
            $num_entries = intval($_POST[$key]);
            $num_entries_old = (int) get_post_meta($object_id, $key, true);

            switch ($context) {
                case 'post':
                case 'product':
                    update_post_meta($object_id, $key, $num_entries);
                    break;
                case 'user':
                    update_user_meta($object_id, $key, $num_entries);
                    break;
                case 'term':
                    update_term_meta($object_id, $key, $num_entries);
                    break;
                default:
                    do_action('ccf/save_field/repeatable', $object_id, $context, $key, $num_entries);
                    break;
            }

            for ($i = 0; $i < $num_entries; $i++) {
                foreach ($this->fields as $field) {
                    $field->save($object_id, $context, $key . '_' . $i);
                }
            }
            $entries_to_remove = $num_entries_old - $num_entries;
            if ($entries_to_remove > 0) {
                for ($i = $num_entries; $i < $num_entries + $entries_to_remove; $i++) {
                    foreach ($this->fields as $field) {
                        $field->delete($object_id, $context, $key . '_' . $i);
                    }
                }
            }
        }
    }
}
