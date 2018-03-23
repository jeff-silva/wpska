<?php

/* TODO:
- Criar a possibilidade de estilizar um menu, colocando na url #algum-id no editor, e posteriormente,
criar o html para todas as opções com esse ID.
*/

class Wpska_Menu
{
	public $theme_location = false;
	public $ul_open = false;
	public $ul_close = false;
	public $li_open = false;
	public $li_close = false;

	public function __construct($theme_location)
	{
		$this->theme_location = $theme_location;
	}

	public function ul_open($callback)
	{
		$this->ul_open = $callback;
	}

	public function ul_close($callback)
	{
		$this->ul_close = $callback;
	}

	public function li_open($callback)
	{
		$this->li_open = $callback;
	}

	public function li_close($callback)
	{
		$this->li_close = $callback;
	}


	public function html($items, $level=0)
	{
		if (empty($items)) return null;

		$this->ul_open = is_callable($this->ul_open)? $this->ul_open: function($item) {
			return '<ul>';
		};

		$this->ul_close = is_callable($this->ul_close)? $this->ul_close: function($item) {
			return '</ul>';
		};

		$this->li_open = is_callable($this->li_open)? $this->li_open: function($item) {
			$class = implode(' ', $item->classes);
			return "<li class=\"{$class}\"><a href='{$item->url}'>{$item->title}</a>";
		};

		$this->li_close = is_callable($this->li_close)? $this->li_close: function($item) {
			return '</li>';
		};

		$content = call_user_func($this->ul_open, $items, $level);
		$content .= "\n". str_repeat("\t", $level+1);
		foreach($items as $item) {
			$content .= call_user_func($this->li_open, $item, $level);
			$content .= $this->html($item->children, $level+1);
			$content .= call_user_func($this->li_close, $item, $level);
			$content .= "\n". str_repeat("\t", $level+1);
		}
		$content .= call_user_func($this->ul_close, $items, $level);
		return $content;
	}

	public function render($settings=null)
	{
		
		// Settings
		if (! is_array($settings)) parse_str($settings, $settings);
		$settings = array_merge(array(
			'responsive' => false,
			'social_icons' => false,
		), $settings);


		if (! function_exists('_wpska_menu_tree')) {
			function _wpska_menu_tree(&$elements, $parentId=0, $depth=0) {
			    $branch = array();
			    foreach ( $elements as &$element )
			    {
			        if ( $element->menu_item_parent == $parentId )
			        {
			            $children = _wpska_menu_tree($elements, $element->ID, $depth+1);
			        	$element->depth = $depth;
			            $element->children = $children;

			            $branch[$element->ID] = $element;
			            unset( $element );
			        }
			    }
			    return $branch;
			}
		}

		$theme_location = $this->theme_location;
		$items = get_nav_menu_locations();
		$items = wp_get_nav_menu_items($items[$theme_location]);
		$items = is_array($items)? $items: array();

		
		// Social icons parse
		if ($settings['social_icons']) {
			$icons = array(
				'facebook.com' => array('name'=>'facebook', 'icon'=>'fa fa-fw fa-facebook'),
				'twitter.com' => array('name'=>'twitter', 'icon'=>'fa fa-fw fa-twitter'),
				'instagram.com' => array('name'=>'instagram', 'icon'=>'fa fa-fw fa-instagram'),
				'youtube.com' => array('name'=>'youtube', 'icon'=>'fa fa-fw fa-youtube'),
				'linkedin.com' => array('name'=>'linkedin', 'icon'=>'fa fa-fw fa-linkedin'),
				'pinterest.com' => array('name'=>'pinterest', 'icon'=>'fa fa-fw fa-pinterest'),
			);
			foreach($items as $item) {
				foreach($icons as $url=>$social) {
					if (strpos($item->url, $url) !== false) {
						$item->title = "<i class='{$social['icon']}'></i>";
						$item->classes[] = $social['name'];
						continue 2;
					}
				}
			}
		}
		

		$items = _wpska_menu_tree($items);



		if (! $settings['responsive']): return $this->html($items);
		else:
		ob_start(); ?>
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
					<?php echo $this->html($items); ?>
				</div><!--/.nav-collapse -->
			</div><!--/.container-fluid -->
		</nav><?php
		return ob_get_clean();
		endif;
	}
}