<?php

/* Copiar de:
https://github.com/jeff-silva/520/blob/master/helpers-ui.php
*/


function wpska_ui_posts_query($query=array()) {
	/*
	s:
	post_type:
	post__in:
	post_parent__in:
	author__in:
	category__in:
	tag:
	posts_per_page:
	orderby:
	order:
	tax_query:
	*/

	$query = array_merge(array(
		'post_type' => array(),
		'posts_per_page' => 12,
		'orderby' => 'ID',
		'order' => 'DESC',
	), $query);

	if (! is_array($query['post_type'])) {
		$query['post_type'] = array('any');
	}

	if (empty($query['post_type'])) {
		$query['post_type'] = array('any');
	}
	else if (sizeof($query['post_type'])>1) {
		foreach($query['post_type'] as $i=>$post_type) {
			if ($post_type=='any') unset($query['post_type'][$i]);
		}
	}
	$query['post_type'] = array_values($query['post_type']);

	$query['posts_per_page'] = intval($query['posts_per_page']);

	if ($query['order']=='RAND') {
		$query['order'] = '';
		$query['orderby'] = 'RAND';
	}
	return $query;
}

class Wpska_Ui
{

	static function _params($params, $merge=null)
	{
		if (is_string($params)) parse_str($params, $params);
		$params = is_array($params)? $params: array();

		if ($merge) {
			if (is_string($merge)) parse_str($merge, $merge);
			$merge = is_array($merge)? $merge: array();
			$params = array_merge($merge, array_filter($params, 'strlen'));
		}

		$params['id'] = 'wpska-ui-'.rand();
		$params['name'] = isset($params['name'])? $params['name']: '';
		$params['attr'] = isset($params['attr'])? $params['attr']: '';

		return $params;
	}


	static function types()
	{
		$titles = array(
			'text' => 'Texto simples',
			'textarea' => 'Texto multilinha',
			'address' => 'Endereço',
			'color' => 'Cor',
			'icon' => 'Ícone',
			'posts' => 'Seletor de posts',
			'frete' => 'Cálculo de frete',
		);

		$return = array();
		foreach(get_class_methods(__CLASS__) as $method) {
			if (in_array($method, array('_params', 'types'))) continue;
			$return[] = array(
				'title' => (isset($titles[$method])? $titles[$method]: $method),
				'method' => $method,
			);
		}
		return $return;
	}



	static function text($value=null, $params=null)
	{
		$params = self::_params($params, array(
			'type' => 'text',
		));
		echo "<input type='{$params['type']}' name='{$params['name']}' id='{$params['id']}' class='{$params['class']}' style='{$params['style']}' value='{$value}' >";
	}


	static function textarea($value=null, $params=null)
	{
		$params = self::_params($params, array());
		echo "<textarea name='{$params['name']}' id='{$params['id']}' class='{$params['class']}'>{$value}</textarea>";
	}


	static function media($value=null, $params=null)
	{
		$params = self::_params($params, array());
		?>
		<div class="wpska-ui-media" id="<?php echo $params['id']; ?>">
			<div class="input-group">
				<input type="text" name="<?php echo $params['name']; ?>" value="<?php echo $value; ?>" class="form-control wpska-ui-media-input" <?php echo $params['attr']; ?> >
				<div class="input-group-btn">
					<button type="button" class="btn btn-default wpska-ui-media-btn">
						<i class="fa fa-fw fa-upload"></i>
					</button>
				</div>
			</div>
			<div class="wpska-ui-media-preview"></div>
		</div>
		<?php wpska_header(); ?>
		<script>
		jQuery(document).ready(function() {
			var $parent = $("#<?php echo $params['id']; ?>");
			var $input = $parent.find(".wpska-ui-media-input");
			var $btn = $parent.find(".wpska-ui-media-btn");
			var $preview = $parent.find(".wpska-ui-media-preview");
			var _mediaOpen = function() {
				var media = wp.media({
					title: 'Select Media',
					multiple : false,
					library : {type : 'image'}
				});

				media.on('select',function() {
					var attachs = media.state().get('selection').toJSON();
					for(var i in attachs) {
						$input.val(attachs[i].url);
						$input.trigger("change");
						break;
					}
				});

				media.open();
			};
			var _preview = function(value) {
				$preview.html('');
				if (value.match(/\.(jpg|jpeg|bmp|png|gif)/)||false) {
					$preview.html('<div style="max-height:300px; overflow:hidden;"><img src="'+value+'" alt="" style="width:100%; cursor:pointer;" /></div>');
					$preview.find("img").on("click", _mediaOpen);
				}
			};

			$btn.on("click", _mediaOpen);

			$input.on("change", function() {
				_preview($input.val());
			});

			setTimeout(function() {
				_preview("<?php echo $value; ?>");
			}, 200);
		});
		</script>
		<?php
	}



	static function medias($value=null, $params=null)
	{
		$params = self::_params($params, array());

		$value = json_decode($value, true);
		$value = is_array($value)? $value: array();

		?>
		<div class="wpska-ui-medias" id="<?php echo $params['id']; ?>">
			<button type="button" class="btn btn-default wpska-ui-medias-btn" @click="_media();">
				<i class="fa fa-fw fa-upload"></i>
			</button>
			<div class="wpska-ui-medias-preview">
				<div v-for="attach in value" style="display:inline-block; position:relative; padding:5px;">
					<img :src="attach.url" :alt="attach.title" style="height:120px;">
					<a href="javascript:;" class="fa fa-fw fa-remove" style="position:absolute; top:5px; right:5px; background:#ffffff66; color:#222; padding:15px 25px 15px 15px;" @click="_attachRemove(attach);"></a>
				</div>
			</div>
			<textarea name="<?php echo $params['name']; ?>" style="display:none;">{{ value }}</textarea>
			<!-- <pre>{{ $data }}</pre> -->
		</div>
		<?php wpska_header(); ?>
		<script>
		new Vue({
			el: "#<?php echo $params['id']; ?>",
			data: {
				value: <?php echo json_encode($value); ?>,
			},
			methods: {
				_media: function() {
					var app=this;

					var media = wp.media({
						title: 'Select Medias',
						multiple : true,
						library : {type : 'image'},
					});

					media.on('select',function() {
						var attachs = media.state().get('selection').toJSON();
						Vue.set(app, "value", attachs);
					});

					media.open();
				},

				_attachRemove: function(attach) {
					var app = this;
					var index = app.value.indexOf(attach);
					app.value.splice(index, 1);
				},
			},
		});
		</script>
		<?php
	}




	static function url($value, $params=null)
	{
		$params = self::_params($params, array());
		echo "<div>Search Wordpress URL: {$value}</div>";
	}



	static function address($value=null, $params=null)
	{
		$params = self::_params($params, array());
		$default = array(
			'zipcode' => '',
			'route' => '',
			'number' => '',
			'complement' => '',
			'district' => '',
			'city' => '',
			'state' => '',
			'state_short' => '',
			'country' => '',
			'country_short' => '',
			'lat' => '',
			'lng' => '',
		);

		$value = json_decode($value, true);
		$value = is_array($value)? $value: array();
		$value = array_merge($default, $value);

		?>
		<div class="wpska-ui-address" id="<?php echo $params['id']; ?>">
			<div class="row">
				<div class="col-sm-6">
					<div class="input-group">
						<input type="text" class="form-control" v-model="value.zipcode" placeholder="Zipcode">
						<div class="input-group-btn">
							<button type="button" class="btn btn-default" @click="_addressSearch();">
								<i class="fa fa-fw fa-search"></i>
							</button>
						</div>
					</div>
				</div>
				<div class="clearfix"></div>
				<div class="col-sm-10"><input type="text" class="form-control" v-model="value.route" placeholder="Rua"></div>
				<div class="col-sm-2"><input type="text" class="form-control" v-model="value.number" placeholder="Número"></div>
				<div class="col-sm-4"><input type="text" class="form-control" v-model="value.complement" placeholder="Complemento"></div>
				<div class="col-sm-4"><input type="text" class="form-control" v-model="value.district" placeholder="Bairro"></div>
				<div class="col-sm-4"><input type="text" class="form-control" v-model="value.city" placeholder="Cidade"></div>
				<div class="col-sm-4"><input type="text" class="form-control" v-model="value.state" placeholder="Estado"></div>
				<div class="col-sm-4"><input type="text" class="form-control" v-model="value.country" placeholder="País"></div>
			</div>
			<textarea name="<?php echo $params['name']; ?>" style="display:none;">{{ value }}</textarea>
		</div>
		<?php wpska_header(); ?>
		<script>
		new Vue({
			el: "#<?php echo $params['id']; ?>",
			data: function() {
				var data={loading:false};
				data.value = <?php echo json_encode($value); ?>;
				return data;
			},
			methods: {
				_addressSearch: function() {
					var app=this, $=jQuery, $parent=$("#<?php echo $params['id']; ?>");
					$parent.css({opacity:.5});
					var post = {"wpska":"wpska_ui_address_search", "search":app.value.zipcode};
					$.post("<?php echo site_url('/'); ?>", post, function(resp) {
						$parent.css({opacity:1});
						Vue.set(app, "value", resp.success);
					}, "json");
				},
			},
		});
		</script>
		<?php
	}


	
	static function color($value=null, $params=null) { echo '<input type="text" class="form-control" value="color">'; }
	


	static function icon($value=null, $params=null) {
		$params = self::_params($params, array());
		?>
		<div class="wpska-ui-icon" id="<?php echo $params['id']; ?>">
			<div class="input-group">
				<input type="text" class="form-control wpska-ui-icon-input">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default">
						<i :class="value"></i>
					</button>
				</div>
			</div>
			<div style="position:relative; width:100%;">
				<div class="wpska-ui-icon-options" style="position:absolute; top:0; left:0; width:100%; display:none; z-index:9;">
					<button type="button" v-for="icon in icons" @click="value=icon;">
						<i :class="icon"></i>
					</button>
				</div>
			</div>
		</div>
		<?php

		add_action('wpska_footer', function() use($value, $params) { ?>
		<?php wpska_header(); ?>
		<script>
		new Vue({
			el: "#<?php echo $params['id']; ?>",
			data: {
				value: "<?php echo $value; ?>",
				icons: <?php echo json_encode(wpska_icons()); ?>,
			},
			mounted: function() {
				var app=this, $=jQuery;
				var $parent = $(app.$el);
				var $input = $parent.find(".wpska-ui-icon-input");
				var $options = $parent.find(".wpska-ui-icon-options");
				$input.on("focus", function() {
					$options.fadeIn(200);
				});
				$input.on("blur", function() {
					setTimeout(function() {
						$options.fadeOut(200);
					}, 100);
				});
			},
		});
		</script>

		<style>
		.wpska-ui-icon-options {max-height:250px; overflow:auto;}
		.wpska-ui-icon-options button {float:left; background:#fff; line-height:0px; width:37px; height:26px; border:solid 1px #bbb; margin:0px -1px -1px 0px; text-align:center; outline:none !important}
		</style>
		<?php });
	}
	

	static function posts($value=null, $params=null) {

		$params = self::_params($params, array());

		$value = json_decode($value, true);
		$value = is_array($value)? $value: array();
		$value = wpska_ui_posts_query($value);

		?>
		<div class="wpska-ui-posts" id="<?php echo $params['id']; ?>">
			<div class="row">
				<div class="col-sm-6">
					<?php wpska_tab('Básico', function() { ?>
					<div class="form-group">
						<label>Post type</label>
						<?php foreach(get_post_types() as $post_type): ?>
						<label style="display:block;">
							<input type="checkbox" v-model="value.post_type" value="<?php echo $post_type; ?>">
							<span><?php echo $post_type; ?></span>
						</label>
						<?php endforeach; ?>
					</div>
					<?php }); ?>
					<?php // wpska_tab('Categorias', 'Categorias'); ?>
					<?php // wpska_tab('Meta', 'Meta'); ?>
					<?php wpska_tab('Resultado', function() { ?>
					<div class="form-group">
						<label>Resultados por página</label>
						<input type="text" v-model="value.posts_per_page" class="form-control">
					</div>
					<div class="form-group">
						<label>Ordenado por</label>
						<div class="input-group">
							<input type="text" v-model="value.orderby" class="form-control">
							<div class="input-group-btn" style="width:0;"></div>
							<select class="form-control" v-model="value.order">
								<option value="ASC">Crescente</option>
								<option value="DESC">Decrescente</option>
								<option value="RAND">Aleatório</option>
							</select>
						</div>
					</div>
					<?php }); ?>
					<?php wpska_tab_render(); ?>
					<div class="text-right">
						<button type="button" class="btn btn-primary" @click="_search();">
							<span v-if="loading">Pesquisando</span>
							<span v-else>Pesquisar</span>
						</button>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="row">
						<div class="col-xs-6" v-for="post in posts">
							<div style="position:relative; width:100%; height:150px; overflow:hidden;" :style="'background:url('+post.thumbnail+') no-repeat center center; background-size:cover;'">
								<div style="position:absolute; bottom:15px; right:15px; text-align:right; color:#fff; text-shadow:0px 0px 1px #000;">
									<strong>{{ post.post_title }}</strong><br>
									<small>{{ post.post_type }} #{{ post.ID }}</small>
								</div>
							</div><br>
						</div>
					</div>
				</div>
			</div>
			<textarea name="<?php echo $params['name']; ?>" style="display:none;">{{ value }}</textarea>
		</div>
		<?php wpska_header(); ?>
		<script>
		new Vue({
			el: "#<?php echo $params['id']; ?>",
			data: {
				loading: false,
				value: <?php echo json_encode($value); ?>,
				posts: [],
			},
			methods: {
				_add: function(parent, data, end) {},
				_remove: function(parent, data) {parent, data},
				_search: function() {
					var app=this, $=jQuery;
					app.loading=true;
					var params = {wpska:"wpska_ui_posts_search", value:app.value};
					$.get("<?php echo site_url('/'); ?>", params, function(resp) {
						app.loading=false;
						Vue.set(app, "posts", resp.success.posts);
						Vue.set(app, "value", resp.success.value);
					}, "json");
				},
			},
		});
		</script>
		<?php
	}


	static function frete($value=null, $params=null) {
		$params = self::_params($params, array(
			'nCdEmpresa' => '',
			'sDsSenha' => '',
			'sCepOrigem' => '',
			'sCepDestino' => '',
			'nVlPeso' => '0',
			'nCdFormato' => '1',
			'nVlComprimento' => '0',
			'nVlAltura' => '0',
			'nVlLargura' => '0',
			'sCdMaoPropria' => 'n',
			'nVlValorDeclarado' => '0',
			'sCdAvisoRecebimento' => 'n',
			'nCdServico' => '', //41106:PAC, 40010:Sedex, 40045:Sedex a cobrar, 40215:Sedex10
			'nVlDiametro' => '0',
			'StrRetorno' => 'xml',
			'wpska_ui_frete' => '1',
		));

		$value = json_decode($value, true);
		$value = is_array($value)? $value: array();
		$value['params'] = isset($value['params'])? $value['params']: array();
		$value['params'] = is_array($value['params'])? $value['params']: array();
		$value['params'] = array_filter($value['params'], 'strlen');
		$value['params'] = array_merge($value['params'], $params);

		$value['values'] = isset($value['values'])? $value['values']: array();
		$value['values'] = is_array($value['values'])? $value['values']: array();
		
		?>

		<div id="<?php echo $params['id']; ?>" class="wpska-ui-frete" data-vue="{init:vueInit}">
			<div class="input-group">
				<input type="text" class="form-control wpska-ui-frete-input" @keydown.prevent.13="_calculate();" v-model="value.params.sCepDestino">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default wpska-ui-frete-btn" @click="_calculate();">
						<span v-if="loading"><i class="fa fa-fw fa-spin fa-spinner"></i> Calculando</span>
						<span v-else >Calcular</span>
					</button>
				</div>
			</div>
			<table class="table" v-if="value.values" style="display:none;">
				<thead><tr><th>Tipo</th><th>Valor</th><th>Prazo</th></tr></thead>
				<tbody>
					<tr v-if="value.values.length==0">
						<td class="text-muted text-center">
							Nada encontrado
						</td>
					</tr>
					<tr v-for="serv in value.values">
						<td>{{ serv.CodigoNome }}</td>
						<td>R${{ serv.Valor }}</td>
						<td>{{ serv.PrazoEntrega }} dias úteis</td>
					</tr>
				</tbody>
			</table>
			<textarea style="display:none;">{{ value }}</textarea>
		</div>

		<?php wpska_header(); ?>

		<script>
		var vueInit = function() {
			return {
				el: "#<?php echo $params['id']; ?>",
				data: {
					loading: false,
					value: <?php echo json_encode($value); ?>,
				},
				methods: {
					_calculate: function() {
						var app=this, $=jQuery, $parent=$("#<?php echo $params['id']; ?>");
						app.loading = true;
						app.value.action = "wpska_ui_frete";
						$parent.css({opacity:.5});
						$.get("<?php echo admin_url('/admin-ajax.php'); ?>", app.value, function(resp) {
							app.loading = false;
							$parent.find("table").fadeIn(200);
							$parent.css({opacity:1});
							Vue.set(app, "value", resp.success);
						}, "json");
					},
				},
			};
		};
		</script>
		<?php
	}

}


// function wpska_action_footer() {}
// add_action('admin_footer', 'wpska_action_footer');
// add_action('wp_footer', 'wpska_action_footer');




class Wpska_Ui_Actions extends Wpska_Actions
{
	//
}


class Wpska_Ui_Ajax extends Wpska_Ajax
{
	public function wpska_ui_frete()
	{
		$params = $this->param('params', array());
		$params = array_filter($params, function($val) { return $val; });
		$params = array_merge(array(
			'nCdEmpresa' => '',
			'sDsSenha' => '',
			'sCepOrigem' => '01505-010',
			'sCepDestino' => '',
			'nVlPeso' => '0.500',
			'nCdFormato' => '1',
			'nVlComprimento' => '16',
			'nVlAltura' => '11',
			'nVlLargura' => '11',
			'sCdMaoPropria' => 'n',
			'nVlValorDeclarado' => '0',
			'sCdAvisoRecebimento' => 'n',
			'nCdServico' => '41106,40010,40215', //41106:PAC, 40010:Sedex, 40045:Sedex a cobrar, 40215:Sedex10
			'nVlDiametro' => '0',
			'StrRetorno' => 'xml',
		), $params);

		$url = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?'.http_build_query($params);
		$xml = simplexml_load_file($url);

		$values = $this->param('values', array());
		foreach($xml->cServico as $serv) {
			if ($serv->Erro !=0) continue;
			if ($serv->Codigo=='41106') $serv->CodigoNome='PAC';
			else if ($serv->Codigo=='40010') $serv->CodigoNome='SEDEX';
			else if ($serv->Codigo=='40045') $serv->CodigoNome='SEDEX a cobrar';
			else if ($serv->Codigo=='40215') $serv->CodigoNome='SEDEX 10';
			$values[] = $serv;
		}

		return array('params'=>$params, 'values'=>$values);
	}


	public function wpska_ui_address_search()
	{
		if (! function_exists('google_places_search')) {
			function google_places_search($path=null) {
				$parse = parse_url($path);
				$parse = array_merge(array('path'=>null, 'query'=>null), $parse);
				parse_str($parse['query'], $parse['query']);
				$parse['query'] = is_array($parse['query'])? $parse['query']: array();
				$parse['query']['key'] = 'AIzaSyB-Li2nMHdkyiJVLubSOtxZZEqGkmxRpvs';
				$parse['query']['language'] = 'pt-BR';
				$parse['query'] = http_build_query($parse['query']);
				$path = trim("{$parse['path']}?{$parse['query']}", '/');
				$data = wpska_content($url = "https://maps.googleapis.com/maps/api/place/{$path}");
				$data = json_decode($data, true);
				$data['url'] = $url;
				return $data;
			}
		}

		$return = false;

		if ($search = $this->param('search')) {
			$search = str_replace(' ', '%20', $search);
			$return = array(
				'zipcode' => '',
				'route' => '',
				'number' => '',
				'complement' => '',
				'district' => '',
				'city' => '',
				'state' => '',
				'state_short' => '',
				'country' => '',
				'country_short' => '',
				'lat' => '',
				'lng' => '',
			);

			$resp1 = wpska_content("https://viacep.com.br/ws/{$search}/json/");
			$resp1 = json_decode($resp1, true);
			if (is_array($resp1) AND isset($resp1['logradouro'])) {
				$search = "{$search}+{$resp1['logradouro']}+{$resp1['bairro']}+{$resp1['localidade']}";
				$return['zipcode'] = $resp1['cep'];
				$return['route'] = $resp1['logradouro'];
				$return['district'] = $resp1['bairro'];
				$return['city'] = $resp1['localidade'];
				$return['state'] = $resp1['uf'];
				$return['state_short'] = $resp1['uf'];
			}

			$resp2 = google_places_search("/textsearch/json?query={$search}");
			if (isset($resp2['results'][0]['place_id'])) {
				$resp2 = google_places_search("/details/json?placeid={$resp2['results'][0]['place_id']}");
				if (isset($resp2['result']['address_components'])) {
					foreach($resp2['result']['address_components'] as $comp) {
						if ($comp['types'][0]=='route') $return['route']=$comp['long_name'];
						else if ($comp['types'][0]=='street_number') $return['number']=$comp['long_name'];
						else if ($comp['types'][0]=='postal_code') $return['zipcode']=$comp['long_name'];
						else if ($comp['types'][0]=='sublocality_level_1') $return['district']=$comp['long_name'];
						else if ($comp['types'][0]=='administrative_area_level_2') $return['city']=$comp['long_name'];
						else if ($comp['types'][0]=='administrative_area_level_1') {
							$return['state']=$comp['long_name'];
							$return['state_short']=$comp['short_name'];
						}
						else if ($comp['types'][0]=='country') {
							$return['country']=$comp['long_name'];
							$return['country_short']=$comp['short_name'];
						}
					}
					$return['lat'] = $resp2['result']['geometry']['location']['lat'];
					$return['lng'] = $resp2['result']['geometry']['location']['lng'];
					$return['formatted_address'] = $resp2['result']['formatted_address'];
				}
			}
		}

		return $return;
	}


	public function wpska_ui_posts_search()
	{
		$value = isset($_REQUEST['value'])? $_REQUEST['value']: array();
		$value = is_array($value)? $value: array();
		$value = wpska_ui_posts_query($value);

		$posts = array();
		foreach(get_posts($value) as $post) {
			$post->thumbnail = wpska_thumbnail($post);
			$posts[] = $post;
		}

		return array(
			'value' => $value,
			'posts' => $posts,
		);
	}
}


new Wpska_Ui_Actions();
new Wpska_Ui_Ajax();