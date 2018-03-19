<?php

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

		return $params;
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

		wpska_header();
		?><div id="<?php echo $params['id']; ?>" class="wpska-ui-frete">
			<textarea style="display:none;">{{ response||[] }}</textarea>
			<div class="input-group">
				<input type="text" class="form-control wpska-ui-frete-input" @keydown.prevent.13="_calculate();" v-model="post.sCepDestino">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default wpska-ui-frete-btn" @click="_calculate();">
						<span v-if="loading"><i class="fa fa-fw fa-spin fa-spinner"></i> Calculando</span>
						<span v-else >Calcular</span>
					</button>
				</div>
			</div>
			<table class="table" v-if="response">
				<thead><tr><th>Tipo</th><th>Valor</th><th>Prazo</th></tr></thead>
				<tbody>
					<tr v-if="!response">
						<td class="text-muted text-center">
							Nada encontrado
						</td>
					</tr>
					<tr v-for="serv in response">
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
				post: <?php echo json_encode($params); ?>,
				response: false,
			},
			methods: {
				_calculate: function() {
					var app=this, $=jQuery;
					app.loading = true;
					$.get("<?php echo site_url(); ?>", app.post, function(resp) {
						app.loading = false;
						app.response = resp.success;
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



if (isset($_REQUEST['wpska_ui_frete'])) {
	add_action('init', function() {
		unset($_REQUEST['wpska_ui_frete']);

		$params = array_filter($_REQUEST, function($val) { return $val; });
		$params = array_merge(array(
			'nCdEmpresa' => '',
			'sDsSenha' => '',
			'sCepOrigem' => '04320-040',
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

		$data = array();
		foreach($xml->cServico as $serv) {
			if ($serv->Erro !=0) continue;
			if ($serv->Codigo=='41106') $serv->CodigoNome='PAC';
			else if ($serv->Codigo=='40010') $serv->CodigoNome='SEDEX';
			else if ($serv->Codigo=='40045') $serv->CodigoNome='SEDEX a cobrar';
			else if ($serv->Codigo=='40215') $serv->CodigoNome='SEDEX 10';
			$data[] = $serv;
		}

		$resp = new Wpska_Response();
		$resp->success($data);
		$resp->json();
	});
}