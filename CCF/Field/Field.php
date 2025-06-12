<?php

namespace CCF\Field;

class Field
{
    protected $type;
    protected $slug;
    protected $name;
    protected $default_value;

    public function __construct(string $type, string $slug, string $name)
    {
        $this->type = $type;
        $this->slug = $slug;
        $this->name = $name;
        $this->default_value = '';
    }

    public function default_value($default_value)
    {
        $this->default_value = $default_value;
        return $this;
    }

    public static function create(string $type, string $slug, string $name)
    {
        $class = __NAMESPACE__ . '\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $type))) . 'Field';
        return (new $class($type, $slug, $name));
    }

    public function save($object_id, $context = 'product', $parent = '')
    {
        $key = $parent . '_' . $this->slug;
        $value = isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : $this->default_value;

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
                do_action('ccf/save_field', $object_id, $context, $parent);
                break;
        }
    }

    public function delete($object_id, $context = 'post', $parent = '')
    {
        $key = $parent . '_' . $this->slug;

        switch ($context) {
            case 'post':
            case 'product':
                delete_post_meta($object_id, $key);
                break;
            case 'user':
                delete_user_meta($object_id, $key);
                break;
            case 'term':
                delete_term_meta($object_id, $key);
                break;
            default:
                do_action('ccf/delete_field', $object_id, $context, $parent);
                break;
        }
    }

    public function get_value($object_id, $context = 'post', $parent = '')
    {
        $key = $parent . '_' . $this->slug;

        switch ($context) {
            case 'post':
            case 'product':
                return get_post_meta($object_id, $key, true);
            case 'user':
                return get_user_meta($object_id, $key, true);
            case 'term':
                return get_term_meta($object_id, $key, true);
            default:
                do_action('ccf/get_field_value', $object_id, $context, $parent);
                break;
        }
    }
}
