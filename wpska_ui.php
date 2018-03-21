<?php

/* Copiar de:
https://github.com/jeff-silva/520/blob/master/helpers-ui.php
*/

class Wpska_Ui
{

	static function _params($params, $merge=null)
	{
		if (is_string($params)) parse_str($params, $params);
		$params = is_array($params)? $params: array();

		if ($merge) {
			if (is_string($merge)) parse_str($merge, $merge);
			$merge = is_array($merge)? $merge: array();
			$params = array_merge($params, $merge);
		}

		$params['id'] = 'wpska-ui-'.rand();
		$params['name'] = isset($params['name'])? $params['name']: '';

		return $params;
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

		wpska_header();
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


	static function uploader($value=null, $params=null) { echo '<input type="text" class="form-control" value="uploader">'; }
	static function upload($value=null, $params=null) { echo '<input type="text" class="form-control" value="upload">'; }
	static function uploads($value=null, $params=null) { echo '<input type="text" class="form-control" value="uploads">'; }
	static function posts($value=null, $params=null) { echo '<input type="text" class="form-control" value="posts">'; }
	static function icon($value=null, $params=null) { echo '<input type="text" class="form-control" value="icon">'; }


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
		$value['params'] = array_merge(array(
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
		), $value['params']);

		$value['values'] = isset($value['values'])? $value['values']: array();
		$value['values'] = is_array($value['values'])? $value['values']: array();

		wpska_header();
		?><div id="<?php echo $params['id']; ?>" class="wpska-ui-frete">
			<textarea style="display:none;">{{ value }}</textarea>
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
		</div>
		<script>
		new Vue({
			el: "#<?php echo $params['id']; ?>",
			data: {
				loading: false,
				value: <?php echo json_encode($value); ?>,
			},
			methods: {
				_calculate: function() {
					var app=this, $=jQuery, $parent=$("#<?php echo $params['id']; ?>");
					app.loading = true;
					app.value.wpska = "wpska_ui_frete";
					$parent.css({opacity:.5});
					$.get("<?php echo site_url('/'); ?>", app.value, function(resp) {
						app.loading = false;
						$parent.find("table").fadeIn(200);
						$parent.css({opacity:1});
						Vue.set(app, "value", resp.success);
					}, "json");
				},
			},
		});
		</script>
		<?php
	}

}


// function wpska_action_footer() {}
// add_action('admin_footer', 'wpska_action_footer');
// add_action('wp_footer', 'wpska_action_footer');




class Wpska_Ui_Actions extends Wpska_Actions
{
	public function wpska_settings()
	{
		wpska_tab('UI', function() { ?>
		<div class="row">
			<?php foreach(get_class_methods('Wpska_Ui') as $method):
			if ($method=='_params') continue;
			?>
			<div class="col-sm-6 form-group">
				<label><?php echo $method; ?></label>
				<?php call_user_func(array('Wpska_Ui', $method)); ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php });
	}
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
}


new Wpska_Ui_Actions();
new Wpska_Ui_Ajax();