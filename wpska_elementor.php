<?php

class Wpska_Elementor_Controls_Posts extends \Elementor\Base_Data_Control {

	public function get_type() {
		return 'posts';
	}

	protected function get_default_settings() {
		return [
			'input_type' => 'posts',
			'placeholder' => '',
			'title' => '',
			'label_block' => true,
			'rows' => 3,
			'emojionearea_options' => [],
		];
	}

	public function content_template() {
		$control_uid = $this->get_control_uid();
		?>
		<div class="elementor-control-field">
			<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-input-wrapper">
				<textarea id="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-tag-area" rows="{{ data.rows }}" data-setting="{{ data.name }}" placeholder="{{ data.placeholder }}" data-wp-posts="{}"></textarea>
			</div>
		</div>
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

}





class Wpska_Elementor_Actions
{
	public $elementor_controls_controls_registered = array(
		'action' => 'elementor/controls/controls_registered',
	);
	public function elementor_controls_controls_registered($controls_manager)
	{
		$controls_manager->register_control('posts', new Wpska_Elementor_Controls_Posts());
	}
}


new Wpska_Elementor_Actions();
