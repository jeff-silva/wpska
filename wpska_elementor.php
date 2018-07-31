<?php

if (! class_exists('\Elementor\Base_Data_Control')) return false;

// If !defined
if (! defined('WPSKA_ELEMENTOR')) {
	wpska_tab('Elementor', function() { ?>
		<?php $wpska_elementor_theme = get_option('wpska_elementor_theme'); ?>
		<div class="row">
			<div class="col-sm-6">
				<input type="text" name="wpska_elementor_theme" value="<?php echo $wpska_elementor_theme; ?>" class="form-control">
			</div>
		</div>
		<?php
		$theme = get_theme_root() .'/'. $wpska_elementor_theme;
		if (! file_exists("{$theme}/style.css")) {
			mkdir($theme, 0755, true);
			file_put_contents("{$theme}/style.css", "/*!\nTheme Name: {$wpska_elementor_theme}\nTheme URI: \nAuthor: Jeferson Inácio\nAuthor URI: http://jsiqueira.com;\nDescription: Tema limpo para elementor\nVersion: 1.0.0\n*/");
			file_put_contents("{$theme}/header.php", "<!doctype html><html <?php language_attributes(); ?>><head><meta charset=\"<?php bloginfo( 'charset' ); ?>\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n<link rel=\"profile\" href=\"http://gmpg.org/xfn/11\">\n<?php wp_head(); ?>\n</head>\n<body>");
			file_put_contents("{$theme}/index.php", "<?php /* Template Name: Index */\nget_header();\nthe_content();\nget_footer();");
			file_put_contents("{$theme}/footer.php", "<?php wp_footer(); ?>\n</body></html>");
			echo '<div class="alert alert-success">Tema criado</div>';
		}
		?>
	<?php });

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


	add_action('elementor/widgets/widgets_registered', function($manager) {
		define('WPSKA_ELEMENTOR', TRUE);
		include __FILE__;
		$manager->register_widget_type(new \Wpska_Elementor_Menu());
		$manager->register_widget_type(new \Wpska_Elementor_Posts());
	});


	// add_filter('template_include', function($template) {
	// 	return NULL;
	// });

	return NULL;
}


/* TODO:
- Suporte ao Bootswatch;
*/


use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Scheme_Color;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Typography;



// element:posts
class Wpska_Elementor_Posts extends Widget_Base
{

	public function get_name() { return get_class(); }
	public function get_title() { return 'Wpska | Posts'; }
	public function get_icon() { return 'eicon-search'; }
	public function get_categories() { return array(); }

	public function get_script_depends() {
		wp_enqueue_style('bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css', [], null, 'all');
		return [
			'bootstrap',
		];
	}

	protected function _register_controls() {
		$this->start_controls_section('section_heading', [
			'label' => 'Menu responsivo',
		]);

		$this->add_control('title', [
			'type' => Controls_Manager::TEXTAREA,
			'label' => 'Título',
			'label_block' => true,
			'separator' => 'before',
			'default' => 'Project name',
		]);

		$this->add_control('query', [
			'type' => Controls_Manager::TEXTAREA,
			'label' => 'Query',
			'default' => 'post_type=posts',
		]);

		$this->add_control('eval', [
			'type' => Controls_Manager::TEXTAREA,
			'label' => 'Eval',
			'default' => 'dd($posts);',
		]);

		$this->end_controls_section();
	}



	protected function render() {

		$settings = $this->get_settings();
		$settings['eval'] = ltrim($settings['eval'], '<?php');
		$settings['eval'] = rtrim($settings['eval'], '?>');

		$posts = get_posts($settings['query']);
		eval(" ?> {$settings['eval']} <?php ");
	}


	protected function content_template() {}

}




// element:menu-responsive
if (! class_exists('Wpska_Menu_Responsive_Walker'))
{
	class Wpska_Elementor_Menu_Walker extends Walker_Nav_Menu {
		public $submenu_type = false;
		public function __construct($submenu_type)
		{
			$this->submenu_type = $submenu_type;
		}

		public function start_el(&$output, $item, $depth=0, $args=array(), $id=0)
		{
			if ($args->walker->has_children AND $this->submenu_type=='dropdown') {
				$output .= "<li class=\"dropdown\"><a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">{$item->title} <span class=\"caret\"></span></a>";
				return NULL;
			}
			
			$output .= "<li class=\"\"><a href=\"{$item->url}\">{$item->title}</a>";
		}

		function start_lvl(&$output, $depth=0, $args=array())
		{
			if ($depth>0 AND $this->submenu_type=='dropdown') {
				$output .= "<ul class=\"dropdown-menu\">";
				return NULL;
			}
			$output .= "<ul class=\"\">";
		}
	}
}

class Wpska_Elementor_Menu extends Widget_Base
{

	public function get_name() { return get_class(); }
	public function get_title() { return 'Wpska | Responsive menu'; }
	public function get_icon() { return 'eicon-search'; }
	public function get_categories() { return array(); }

	public function get_script_depends() {
		wp_enqueue_style('bootstrap-css', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css', [], null, 'all');
		wp_enqueue_style('bootstrap-js', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', [], null, 'all');
		return ['bootstrap-css', 'bootstrap-js'];
	}

	protected function _register_controls() {
		$this->start_controls_section('section_heading', [
			'label' => 'Menu responsivo',
		]);

		$this->add_control('title', [
			'type' => Controls_Manager::TEXTAREA,
			'label' => 'Título',
			'label_block' => true,
			'separator' => 'before',
			'default' => 'Project name',
		]);

		$this->add_control('logo', [
			'type' => Controls_Manager::MEDIA,
			'label' => 'Logo',
		]);

		$menus = get_registered_nav_menus();
		array_unshift($menus, 'Nenhum');

		$this->add_control('menu_left', [
			'type' => Controls_Manager::SELECT,
			'label' => 'Menu esquerda',
			'options' => $menus,
		]);

		$this->add_control('menu_left_type', [
			'type' => Controls_Manager::SELECT,
			'label' => 'Tipo',
			'default' => 'submenu',
			'options' => array(
				'dropdown' => 'Dropdown',
				'submenu' => 'Submenu',
			),
		]);

		$this->add_control('menu_right', [
			'type' => Controls_Manager::SELECT,
			'label' => 'Menu direita',
			'options' => $menus,
		]);

		$this->add_control('menu_right_type', [
			'type' => Controls_Manager::SELECT,
			'label' => 'Tipo',
			'default' => 'submenu',
			'options' => array(
				'dropdown' => 'Dropdown',
				'submenu' => 'Submenu',
			),
		]);

		$this->add_control('style', [
			'type' => Controls_Manager::TEXTAREA,
			'label' => 'CSS',
		]);

		$this->end_controls_section();
	}



	protected function render() {

		$id_navbar = uniqid('navbar');
		$settings = $this->get_settings();


		?>

		<nav class="navbar navbar-default navbar-wpska-elementor">
			<div class="container">
			  <div class="navbar-header">
			    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#<?php echo $id_navbar; ?>" aria-expanded="false" aria-controls="navbar">
			      <span class="sr-only">Toggle navigation</span>
			      <span class="icon-bar"></span>
			      <span class="icon-bar"></span>
			      <span class="icon-bar"></span>
			    </button>

				<?php if ($settings['title'] OR $settings['logo']['url']): ?>
			    <a class="navbar-brand" href="javascript:;">
					<?php if ($settings['logo']['url']): ?>
					<img src="<?php echo $settings['logo']['url']; ?>" alt="<?php echo $settings['title']; ?>">
					<?php endif; ?>
			    	<?php echo $settings['title']; ?>
			    </a>
				<?php endif; ?>

			  </div>
			  <div id="<?php echo $id_navbar; ?>" class="navbar-collapse collapse">

			  	<?php if ($menu = $settings['menu_left']): ?>
			  	<?php wp_nav_menu(array(
					'container' => 'ul',
					'walker' => new Wpska_Elementor_Menu_Walker($settings['menu_left_type']),
					'menu_class' => 'nav navbar-nav',
					'theme_location' => $menu,
				)); ?>
				<?php /* <ul class="nav navbar-nav">
			      <li class="active"><a href="#">Home</a></li>
			      <li><a href="#">About</a></li>
			      <li><a href="#">Contact</a></li>
			      <li class="dropdown">
			        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Dropdown <span class="caret"></span></a>
			        <ul class="dropdown-menu">
			          <li><a href="#">Action</a></li>
			          <li><a href="#">Another action</a></li>
			          <li><a href="#">Something else here</a></li>
			          <li role="separator" class="divider"></li>
			          <li class="dropdown-header">Nav header</li>
			          <li><a href="#">Separated link</a></li>
			          <li><a href="#">One more separated link</a></li>
			        </ul>
			      </li>
			    </ul> */ ?>
			  	<?php endif; ?>


			  	<?php if ($menu = $settings['menu_right']): ?>
			  	<?php wp_nav_menu(array(
					'container' => 'ul',
					'walker' => new Wpska_Elementor_Menu_Walker($settings['menu_right_type']),
					'menu_class' => 'nav navbar-nav navbar-right',
					'theme_location' => $menu,
				)); ?>
				<?php /* <ul class="nav navbar-nav navbar-right">
			      <li class="active"><a href="./">Default <span class="sr-only">(current)</span></a></li>
			      <li><a href="../navbar-static-top/">Static top</a></li>
			      <li><a href="../navbar-fixed-top/">Fixed top</a></li>
			    </ul> */ ?>
			  	<?php endif; ?>

			  </div><!--/.nav-collapse -->
			</div><!--/.container-fluid -->
		</nav>

		<style><?php echo $settings['style']; ?></style>

		<?php
	}


	protected function content_template() {}

}


// element:custom-map
// element:form-login
// element:form-register
// element:form-password
// element:member-area
// element:social-share








