<?php


if (isset($_REQUEST['helper_form_action'])) {
	add_action('init', function() {
		$resp = array('success'=>false, 'error'=>false);
		$post = array_merge(array(
			'email' => '',
			'success' => 'Contato enviado',
		), $_REQUEST);

		if (! filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
			$resp['error'] .= "E-mail inválido \n";
		}


		if (! $resp['error']) {

			$json = $post;
			unset($json['success']);
			$json = json_encode($json);

			$return = wp_insert_post(array(
				'post_content' => $json,
				'post_title' => "{$post['type']} - {$post['email']}",
				'post_status' => 'draft',
				'post_type' => 'helper_form',
			), true);

			$resp['success'] = $post['success'] ." - #{$return}";
		}

		echo json_encode($resp); die;
	});
}



function helper_form($atts=null, $content=null) {
	if (is_callable($content)) {
		ob_start();
		call_user_func($content);
		$content = ob_get_clean();
	}
	return do_shortcode("[helper_form {$atts}]{$content}[/helper_form]");
}



add_shortcode('helper_form', function($atts=null, $content=null) {
	$atts = shortcode_atts(array(
		'type' => 'contact',
		'success' => 'Contato enviado',
	), $atts, 'bartag');
	ob_start(); ?>
	<form onsubmit="return helper_form_submit(this);">
		<?php foreach($atts as $key=>$val): ?>
		<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $val; ?>">
		<?php endforeach; ?>
		<?php echo $content; ?>
	</form>
	<script>
	var helper_form_submit = function(form) {
		var $=jQuery, $form=$(form), post={};
		$.map($form.serializeArray(), function(n, i){ post[n['name']] = n['value']; });
		post.helper_form_action = true;

		$form.css({opacity:.5});
		$.post("<?php echo site_url('?helper_form_action'); ?>", post, function(resp) {
			$form.css({opacity:1});
			if (resp.error) {
				alert(resp.error);
				return false;
			}
			alert(resp.success);
			form.reset();
		}, "json");
		return false;
	};
	</script>
	<?php return ob_get_clean();
});




add_action('init', function() {
	register_post_type('helper_form', array(
		'labels' => array(
			'name' => 'Contatos',
			'singular_name' => 'Contatos',
		),
		'public' => true,
		'has_archive' => true,
	));
});


add_filter('manage_helper_form_posts_columns', function ( $columns ) {
	$columns['helper_form_data'] = 'Dados';
	return $columns;
});

add_filter('manage_edit-helper_form_columns', function($columns) {
	return array(
		'cb' => $columns['cb'],
		'title' => $columns['title'],
		'helper_form_data' => $columns['helper_form_data'],
	);
});


add_action('manage_helper_form_posts_custom_column', function ($column_name, $post_id ) {
    
    if ($column_name == 'helper_form_data') {
    	$post = get_post($post_id);
    	$post->post_content = json_decode($post->post_content, true);
    	$ignore = array('helper_form_action', 'type', 'email');
    	?>
		
		<button style="float:right;" onclick="_toggleDetails(this);">Mais detalhes</button>
    	<strong><?php echo $post->post_content['email']; ?></strong>
    	<div style="clear:both;"></div>
		<div class="toggle-details" style="display:none;">
			<?php foreach($post->post_content as $key=>$val): if (in_array($key, $ignore)) continue; ?>
			<div>
				<strong><?php echo $key; ?></strong>
				&nbsp; <?php echo $val; ?>
			</div>
			<?php endforeach; ?>
		</div>

		<script>
		var _toggleDetails = function(el) {
			var $=jQuery;
			var $wrapper = $(el).parent().find('.toggle-details');
			$wrapper.slideToggle(200);
			if ($wrapper.find(">*").length==0) {
				$wrapper.html('<div style="color:#777; text-align:center; background:#eee; padding:5px;">Nenhum detalhe</div>');
			}
		};
		</script>
    	<?php
	}

}, 10, 2);


add_action('load-edit.php', function() { ?>
<?php if (isset($_GET['post_type']) AND $_GET['post_type']=='helper_form'): ?>
<style>
.post-state, #filter-by-date, #post-query-submit {display:none;}
</style>
<?php endif; ?>
<?php });
