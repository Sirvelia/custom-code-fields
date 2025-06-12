<?php

namespace CCF\Section;

class ProductSection extends Section
{
	private $tab;
	private $product_type;
	private $checked;
	private $not_checked;
	private $ids;

	public function __construct(string $slug, string $name, array $fields) 
	{
		parent::__construct($slug, $name, $fields);

		$this->tab = 'general';
		$this->product_type = [];
		$this->checked = [];
		$this->not_checked = [];
		$this->ids = [];
		$this->section_type = 'product';

		add_action('woocommerce_product_options_general_product_data', [$this, 'display_default']);
		add_action('woocommerce_process_product_meta', [$this, 'save']);

		add_action('admin_enqueue_scripts', function ($hook) {
			global $typenow;

			if ($typenow === 'product' && ($hook === 'post-new.php' || $hook === 'post.php')) {
				wp_enqueue_style('custom-code-fields/app.css', plugin_dir_url(__DIR__) . '../dist/app.css', false, __NAMESPACE__ . '\VERSION');
				wp_enqueue_script('custom-code-fields/app.js', plugin_dir_url(__DIR__) . '../dist/app.js', [], __NAMESPACE__ . '\VERSION', true);
				wp_localize_script('custom-code-fields/app.js', 'CCF_PARAMS', array(
					'ajaxurl'   => admin_url('admin-ajax.php'),
					'api_url'   => get_rest_url(null, 'custom-code-fields/v1'),
					'nonce'     => wp_create_nonce('ajax-nonce'),
					'restNonce'	=> wp_create_nonce('wp_rest')
				));
			};
		}, 0);
	}

	public function if_tab(string $tab)
	{
		$this->tab = $tab;
		remove_action('woocommerce_product_options_general_product_data', array($this, 'display_default'));
		if (!in_array($tab, ['general', 'inventory', 'related', 'attributes', 'advanced'])) {
			add_action('woocommerce_product_data_panels', function () {
				echo "<div id='$this->tab' class='panel woocommerce_options_panel hidden'>";
				$this->display();
				echo '</div>';
			});
		} else {
			add_action("woocommerce_product_options_{$tab}_product_data", array($this, 'display'));
		}
		return $this;
	}

	public function if_product_type($product_type)
	{
		$this->product_type = (array) $product_type;
		return $this;
	}

	public function if_checked($values)
	{
		$this->checked = (array) $values;
		return $this;
	}

	public function if_not_checked($values)
	{
		$this->not_checked = (array) $values;
		return $this;
	}

	public function if_id($values)
	{
		$this->ids = (array) $values;
		return $this;
	}

	public function get_classes() {
		$classes = '';
		foreach ($this->product_type as $product_type) {
			$classes .= " show_if_$product_type";
		}
		foreach ($this->checked as $checked) {
			$classes .= " show_if_$checked hidden";
		}

		return $classes;
	}

	public function has_permission()
	{
		if(!parent::has_permission()) return false;

		//CHECK IDS
		global $post;
		if ($this->ids) {
			if (!in_array($post->ID, $this->ids)) return false;
		}

		return true;
	}

	public function display_default()
	{
		if ($this->tab === 'general') {
			$this->display();
		}
	}

}
