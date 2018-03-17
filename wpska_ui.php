<?php

class Wpska_Ui
{

	static function frete($value=null, $attrs=null, $params=array()) {

		$params = array_merge(array(
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
		), $params);

		?><div class="wpska-ui-frete">
			<div class="input-group">
				<input type="text" class="form-control wpska-ui-frete-input" onkeydown="if (event.keyCode==13) { event.preventDefault(); wpska_ui_frete(this); }">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default" onclick="wpska_ui_frete(this);">Calcular</button>
				</div>
			</div>
			<div class="wpska-ui-frete-resp"></div>
		</div>
		<script>
		var wpska_ui_frete = function(el) {
			var $=jQuery, post=<?php echo json_encode($params); ?>;
			var $parent = $(el).closest(".wpska-ui-frete");
			var $input = $parent.find(".wpska-ui-frete-input");
			var $resp = $parent.find(".wpska-ui-frete-resp");
			post.sCepDestino = $input.val();
			$parent.css({opacity:.5});
			$.get("<?php echo site_url(); ?>", post, function(resp) {
				$parent.css({opacity:1});
				$resp.html(resp);
			});
		};
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
		$resp = '<table class="table table-bordered"><thead><tr><th>Servi&ccedil;o</th><th>Valor</th><th>Prazo</th></tr></thead><tbody>';
		foreach($xml->cServico as $serv) {
			if ($serv->Erro !=0) continue;
			if ($serv->Codigo=='41106') $serv->Codigo='PAC';
			else if ($serv->Codigo=='40010') $serv->Codigo='SEDEX';
			else if ($serv->Codigo=='40045') $serv->Codigo='SEDEX a cobrar';
			else if ($serv->Codigo=='40215') $serv->Codigo='SEDEX 10';
			$resp .= "<tr><td>{$serv->Codigo}</td><td>R\${$serv->Valor}</td><td>{$serv->PrazoEntrega} dias</td></tr>";
		}
		$resp .= "</tbody></table>";
		echo $resp; die;
	});
}