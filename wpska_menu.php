<?php


class Wpska_Walker extends Walker_Nav_Menu
{
	
	public $callbacks=array();
	public function __construct($callbacks=array())
	{
		$this->callbacks = $callbacks;
	}


	public function start_el(&$output, $item, $depth=0, $args=array(), $id=0) {
		$html = "<li><a href=\"{$item->url}\">{$item->title}</a>";
		if (isset($this->callbacks['start_el']) AND is_callable($this->callbacks['start_el'])) {
			ob_start();
			echo call_user_func($this->callbacks['start_el'], $item, $depth, $args, $id);
			$content = ob_get_clean();
			if ($content) $html = $content;
		}
		$output .= $html;
	}


	public function end_el(&$output, $item, $depth=0, $args=array()) {
		$html = "</li>";
		if (isset($this->callbacks['end_el']) AND is_callable($this->callbacks['end_el'])) {
			ob_start();
			echo call_user_func($this->callbacks['end_el'], $item, $depth, $args);
			$content = ob_get_clean();
			if ($content) $html = $content;
		}
		$output .= $html;
	}


	public function start_lvl(&$output, $depth) {
		$html = "<ul class=\"sub-menu\">";
		if (isset($this->callbacks['start_lvl']) AND is_callable($this->callbacks['start_lvl'])) {
			ob_start();
			echo call_user_func($this->callbacks['start_lvl'], $depth);
			$content = ob_get_clean();
			if ($content) $html = $content;
		}
		$output .= $html;
	}


	public function end_lvl(&$output, $depth=0, $args=array()) {
		$html = "</ul>";
		if (isset($this->callbacks['end_lvl']) AND is_callable($this->callbacks['end_lvl'])) {
			ob_start();
			echo call_user_func($this->callbacks['end_lvl'], $depth, $args);
			$content = ob_get_clean();
			if ($content) $html = $content;
		}
		$output .= $html;
	}
}



class Wpska_Menu
{
	
	public $settings = array(
        'container' => false,
        'container_class' => '',
        'container_id' => '',
        'items_wrap' => '<ul class="%2$s">%3$s</ul>', //%3$s
        'echo' => false,
        'fallback_cb' => false,
        'menu_class' => '',
        'theme_location' => '',
        'walker' => false,
    );

    public $callbacks = array(
    	'start_el' => false,
    	'end_el' => false,
    	'start_lvl' => false,
    	'end_lvl' => false,
    );

	public function __construct($theme_location, $settings=array())
	{
		$this->settings($settings);
		$this->settings['theme_location'] = $theme_location;
	}

	public function settings($settings=array()) {
		$this->settings = array_merge($this->settings, $settings);
	}

	public function start_el($call=null) {
		$this->callbacks['start_el'] = $call;
	}
	
	public function end_el() {
		$this->callbacks['end_el'] = $call;
	}

	public function start_lvl() {
		$this->callbacks['start_lvl'] = $call;
	}

	public function end_lvl() {
		$this->callbacks['end_lvl'] = $call;
	}

	public function render()
	{
		$this->settings['walker'] = new Wpska_Walker($this->callbacks);
		return wp_nav_menu($this->settings);
	}

	public function renderBootstrap()
	{
		$id = 'nav-'.rand();
		$this->settings(array(
			'items_wrap' => '<ul class="nav navbar-nav %2$s">%3$s</ul>',
		));
		
		ob_start();
		?>

		<nav class="navbar navbar-default">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#<?php echo $id; ?>" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
				</div>
				<div id="<?php echo $id; ?>" class="navbar-collapse collapse">
					<?php echo $this->render(); ?>
				</div><!--/.nav-collapse -->
			</div><!--/.container-fluid -->
		</nav>

		<?php
		return ob_get_clean();
	}
}

