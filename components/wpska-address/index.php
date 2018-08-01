<?php include __DIR__ . '/../../wpska.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"></script>
<script src="../vuel.js"></script>

<template id="wpska-address">
	
	<!-- Layout 0 -->
	<div class="row" v-if="layout==0">
		<div class="col-sm-8">
			<div class="input-group">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default" title="Meu endereço atual" @click="_currentLocation();">
						<i class="fa fa-fw fa-globe"></i>
					</button>
				</div>
				<input type="text" class="form-control" placeholder="Pesquise endereço ou CEP" v-model="search" @keyup.13.prevent="_search();">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default" @click="_search();">
						<i class="fa fa-fw fa-search" v-if="loading==0"></i>
						<i class="fa fa-fw fa-spin fa-spinner" v-else></i>
					</button>
				</div>
			</div><br>
		</div>
		<div class="clearfix"></div>
		<div class="col-xs-12 col-sm-6"><input type="text" class="form-control" placeholder="Rua" v-model="value.route"><br></div>
		<div class="col-xs-4 col-sm-2"><input type="text" class="form-control" placeholder="Nº" v-model="value.number"><br></div>
		<div class="col-xs-8 col-sm-4"><input type="text" class="form-control" placeholder="Complemento" v-model="value.complement"><br></div>
		<div class="col-xs-6 col-sm-4"><input type="text" class="form-control" placeholder="CEP" v-model="value.zip_code"><br></div>
		<div class="col-xs-6 col-sm-4"><input type="text" class="form-control" placeholder="Bairro" v-model="value.district"><br></div>
		<div class="col-xs-12 col-sm-4"><input type="text" class="form-control" placeholder="Cidade" v-model="value.city"><br></div>
		<div class="col-xs-6 col-sm-6"><input type="text" class="form-control" placeholder="Estado" v-model="value.state"><br></div>
		<div class="col-xs-6 col-sm-6"><input type="text" class="form-control" placeholder="País" v-model="value.country"><br></div>
		<div class="col-sm-12" v-if="map==1 && value.embed">
			<iframe :src="value.embed" style="width:100%; height:200px; border:none;"></iframe>
		</div>
	</div>
	<!-- Layout 0 -->
	

	<!-- Layout 1 -->
	<div class="row" v-if="layout==1">
		<div class="col-sm-12">
			<div class="input-group">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default" title="Meu endereço atual" @click="_currentLocation();">
						<i class="fa fa-fw fa-globe"></i>
					</button>
				</div>
				<input type="text" class="form-control" placeholder="Pesquise endereço ou CEP" v-model="search" @keyup.13.prevent="_search();">
				<div class="input-group-btn">
					<button type="button" class="btn btn-default" @click="_search();">
						<i class="fa fa-fw fa-search" v-if="loading==0"></i>
						<i class="fa fa-fw fa-spin fa-spinner" v-else></i>
					</button>
				</div>
			</div><br>
		</div>
		<div class="col-xs-12"><input type="text" class="form-control" placeholder="Rua" v-model="value.route"><br></div>
		<div class="col-xs-4"><input type="text" class="form-control" placeholder="Nº" v-model="value.number"><br></div>
		<div class="col-xs-8"><input type="text" class="form-control" placeholder="Complemento" v-model="value.complement"><br></div>
		<div class="col-xs-12"><input type="text" class="form-control" placeholder="CEP" v-model="value.zip_code"><br></div>
		<div class="col-xs-12"><input type="text" class="form-control" placeholder="Bairro" v-model="value.district"><br></div>
		<div class="col-xs-12"><input type="text" class="form-control" placeholder="Cidade" v-model="value.city"><br></div>
		<div class="col-xs-6"><input type="text" class="form-control" placeholder="Estado" v-model="value.state"><br></div>
		<div class="col-xs-6"><input type="text" class="form-control" placeholder="País" v-model="value.country"><br></div>
		<div class="col-sm-12" v-if="map==1 && value.embed">
			<iframe :src="value.embed" style="width:100%; height:200px; border:none;"></iframe>
		</div>
	</div>
	<!-- Layout 1 -->


	<!-- Layout 2 -->
	<div class="row" v-if="layout==2">
		<div class="row">
			<div class="col-xs-6">
				<div class="row">
					<div class="col-sm-12">
						<div class="input-group">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default" title="Meu endereço atual" @click="_currentLocation();">
									<i class="fa fa-fw fa-globe"></i>
								</button>
							</div>
							<input type="text" class="form-control" placeholder="Pesquise endereço ou CEP" v-model="search" @keyup.13.prevent="_search();">
							<div class="input-group-btn">
								<button type="button" class="btn btn-default" @click="_search();">
									<i class="fa fa-fw fa-search" v-if="loading==0"></i>
									<i class="fa fa-fw fa-spin fa-spinner" v-else></i>
								</button>
							</div>
						</div><br>
					</div>
					<div class="col-xs-12"><input type="text" class="form-control" placeholder="Rua" v-model="value.route"><br></div>
					<div class="col-xs-4"><input type="text" class="form-control" placeholder="Nº" v-model="value.number"><br></div>
					<div class="col-xs-8"><input type="text" class="form-control" placeholder="Complemento" v-model="value.complement"><br></div>
					<div class="col-xs-12"><input type="text" class="form-control" placeholder="CEP" v-model="value.zip_code"><br></div>
					<div class="col-xs-12"><input type="text" class="form-control" placeholder="Bairro" v-model="value.district"><br></div>
					<div class="col-xs-12"><input type="text" class="form-control" placeholder="Cidade" v-model="value.city"><br></div>
					<div class="col-xs-6"><input type="text" class="form-control" placeholder="Estado" v-model="value.state"><br></div>
					<div class="col-xs-6"><input type="text" class="form-control" placeholder="País" v-model="value.country"><br></div>
				</div>
			</div>
			<div class="col-xs-6">
				<div v-if="map==0">Set map="1"</div>
				<div v-if="map==1">
					<div class="text-center text-muted jumbotron" v-if="!value.embed">Selecione um local</div>
					<iframe :src="value.embed" v-if="value.embed" style="width:100%; height:440px; border:none;"></iframe>
				</div>
			</div>
		</div>
	</div>
	<!-- Layout 2 -->
</template>

<script>
Vuel("wpska-address", {
	data: {
		name: "",
		value: {},
		valueDefault: {
			route: "",
			number: "",
			complement: "",
			zip_code: "",
			district: "",
			city: "",
			state: "",
			state_code: "",
			country: "",
			country_code: "",
			lat: "",
			lng: "",
			formatted_address: "",
			embed: "",
			image: "",
		},
		map: "0",
		layout: "0",
		search: "",
		loading: "0",
	},
	methods: {
		_search: function() {
			var app=this, $=jQuery;
			app.loading = "Pesquisando...";
			$.get("<?php echo wpska_base('/search.php', __DIR__); ?>", {search:app.search}, function(resp) {
				app.loading = "0";
				Vue.set(app, "search", "");
				Vue.set(app, "value", resp);
			}, "json");
		},
		_currentLocation: function() {
			var app=this, $=jQuery;
			$.ajax({
				type : 'POST',
				data: '', 
				url: "https://www.googleapis.com/geolocation/v1/geolocate?key=<?php echo wpska_settings('google_key'); ?>", 
				success: function(resp){
					Vue.set(app, "search", (resp.location.lat+","+resp.location.lng));
					app._search();
				},
			});
		},
		_layout: function(n) {
			var app = _vm(this);
			Vue.set(app, "layout", n);
		},
	},
	mounted: function() {
		var app = this;
	},
});
</script>
