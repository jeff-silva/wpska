<?php

function wpska_theme_classes_create() {
	if (class_exists('Wpska_Theme_Control_Callback')) return false;

	class Wpska_Theme_Control_Callback extends WP_Customize_Control
	{
		public function __construct($manager, $id, $args=array())
		{
			$this->type = $args['type'];
			$this->callback = $args['callback'];
			parent::__construct($manager, $id, $args);
		}

		public function link($setting_key='default') {
			return $this->get_link($setting_key);
		}

		public function render_content()
		{
			if (is_callable($this->callback)) {
				call_user_func($this->callback, $this);
			}
		}
	}


	class Wpska_Theme_Control_Media extends WP_Customize_Control
	{
		public $type = 'wpska_media';

		public function render_content() { ?>
		<div>media</div>
		<?php }
	}
}





class Wpska_Theme
{

	static $data = array();

	public function __construct()
	{
		if (empty(self::$data)) {
			
			$theme = wp_get_theme();
			self::$data['Name'] = $theme->get('Name');
			self::$data['ThemeURI'] = $theme->get('ThemeURI');
			self::$data['Description'] = $theme->get('Description');
			self::$data['Author'] = $theme->get('Author');
			self::$data['AuthorURI'] = $theme->get('AuthorURI');
			self::$data['settings'] = array();

			$this->init();
			add_action('customize_register', array($this, 'customize_register'));
		}
	}

	public function init() {}


	public $settings = array();
	public function setting($keyname, $settings=null)
	{
		$index = sizeof(self::$data['settings'])+1;

		if (! is_array($settings)) parse_str($settings, $settings);
		$settings = is_array($settings)? $settings: array();
		$settings = array_merge(array(
			'label' => "label {$index}",
			'description' => '',
			'default' => '',
			'transport'  => 'refresh', // refresh|postMessage
			'type' => 'text',
			'choices' => array(),
		), $settings);

		self::$data['settings'][$keyname] = $settings;
	}


	public function customField($keyname, $callback)
	{
		if (isset(self::$data['settings'][$keyname]) AND is_callable($callback)) {
			// self::$data['settings'][ $keyname ]['type'] = false;
			self::$data['settings'][$keyname]['callback'] = $callback;
		}
	}


	public function get($keyname)
	{
		$default = false;
		if (isset(self::$data['settings'][$keyname])) {
			$default = self::$data['settings'][$keyname]['default'];
		}
		return get_theme_mod($keyname, $default);
	}


	public function customize_register($wp_customize)
	{
		$section_key = strtolower(__CLASS__);
		$wp_customize->add_section($section_key, array(
			'title' => self::$data['Name'],
			'description' => self::$data['Description'],
			'priority' => 20,
		));


		foreach(self::$data['settings'] as $keyname=>$setting) {

			if (is_string($setting)) parse_str($setting, $setting);
			$setting = is_array($setting)? $setting: array();

			// Add settings
			$settings_args = array_merge(array(
				'capability' => 'edit_theme_options',
			), $setting);

			$settings_args['type'] = 'theme_mod';
			$wp_customize->add_setting($keyname, $settings_args);


			// Add control
			$control_args = array_merge(array(
				'priority' => 10,
				'section' => $section_key,
				'input_attrs' => array(),
				// 'settings' => $keyname,
			), $setting);

			
			// wpska_media
			if ($setting['type']=='wpska_media') {
				$setting['callback'] = function($me) use($keyname) {
					echo '<label>Media</label><br>';
					Wpska_Ui::media($me->value(), array(
						'name' => "guaglini_section01_img0{$x}_img",
						'attr' => $me->link(),
					));
				};
			}

			if (isset($setting['callback'])) {
				$control_args['type'] = $keyname;
				$control_args['callback'] = $setting['callback'];
				wpska_theme_classes_create();
				$wp_customize->add_control(new Wpska_Theme_Control_Callback($wp_customize, $keyname, $control_args));
			}

			else {
				$wp_customize->add_control($keyname, $control_args);
			}

			// dd(array(
			// 	'settings_args' => $settings_args,
			// 	'control_args' => $control_args,
			// )); die;
		}

		// dd(get_theme_mods(), $wp_customize); die;
	}
}

