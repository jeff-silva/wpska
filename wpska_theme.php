<?php

function wpska_theme_ghost_create() {
	class Wpska_Theme_Ghost extends WP_Customize_Control
	{
		public function __construct($manager, $id, $args=array())
		{
			$this->type = $args['type'];
			$this->callback = $args['callback'];
			parent::__construct($manager, $id, $args);
		}

		public function render_content()
		{
			if (is_callable($this->callback)) {
				call_user_func($this->callback, $this);
			}
		}
	}
}





class Wpska_Theme
{
	public $Name = false;
	public $ThemeURI = false;
	public $Description = false;
	public $Author = false;
	public $AuthorURI = false;
	public function __construct()
	{
		$theme = wp_get_theme();
		$this->Name = $theme->get('Name');
		$this->ThemeURI = $theme->get('ThemeURI');
		$this->Description = $theme->get('Description');
		$this->Author = $theme->get('Author');
		$this->AuthorURI = $theme->get('AuthorURI');
		$this->init();
		add_action('customize_register', array($this, 'customize_register'));
		// dd($this); die;
	}

	public function init() {}


	public $settings = array();
	public function setting($keyname, $settings=null)
	{
		if (! is_array($settings)) parse_str($settings, $settings);
		$settings = is_array($settings)? $settings: array();
		$index = sizeof($this->settings)+1;
		$settings = array_merge(array(
			'label' => "label {$index}",
			'description' => '',
			'default' => '',
			'transport'  => 'refresh', // refresh|postMessage
			'type' => 'text',
			'choices' => array(),
		), $settings);
		$this->settings[ $keyname ] = $settings;
	}


	public function customField($keyname, $callback)
	{
		if (isset($this->settings[ $keyname ]) AND is_callable($callback)) {
			// $this->settings[ $keyname ]['type'] = false;
			$this->settings[ $keyname ]['callback'] = $callback;
		}
	}


	public function get($keyname)
	{
		$default = false;
		if (isset($this->settings[ $keyname ])) {
			$default = $this->settings[ $keyname ]['default'];
		}
		return get_theme_mod($keyname, $default);
	}


	public function customize_register($wp_customize)
	{

		foreach($this->settings as $keyname=>$setting) {
			$args = $setting;
			$args['type'] = 'theme_mod';
			$wp_customize->add_setting($keyname, $args);
		}

		$section_key = strtolower(__CLASS__);
		$wp_customize->add_section($section_key, array(
			'title'       => $this->Name,
			'description' => $this->Description,
			'priority'    => 20,
		));

		foreach($this->settings as $setting_key=>$setting) {
			$args = array();
			$args['label'] = $setting['label'];
			$args['description'] = $setting['description'];
			$args['priority'] = 10;
			$args['section'] = $section_key;

			if (isset($setting['callback'])) {
				$args['type'] = $setting_key;
				$args['callback'] = $setting['callback'];
				wpska_theme_ghost_create();
				$wp_customize->add_control(new Wpska_Theme_Ghost($wp_customize, $setting_key, $args));
			}

			else {
				$wp_customize->add_control(new WP_Customize_Control($wp_customize, $setting_key, $args));
			}
		}
	}
}

