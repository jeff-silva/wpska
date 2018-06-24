<?php include __DIR__ . '/../../wpska.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"></script>
<script src="../vuel.js"></script>

<template id="wpska-address">
	<div>
		<div class="row">
			<div class="col-sm-12">
				<div class="row"><div class="col-sm-6">
					<div class="input-group">
						<input type="text" class="form-control" placeholder="Pesquise endereço ou CEP" v-model="search" @keyup.13="_search();">
						<div class="input-group-btn">
							<button type="submit" class="btn btn-default" @click="_search();">
								<i class="fa fa-fw fa-search"></i>
							</button>
						</div>
					</div><br>
				</div></div>
			</div>
			<div class="col-xs-12 col-sm-6"><input type="text" class="form-control" placeholder="Rua" v-model="address.route" @change="_remap();"><br></div>
			<div class="col-xs-4 col-sm-2"><input type="text" class="form-control" placeholder="Nº" v-model="address.number" @change="_remap();"><br></div>
			<div class="col-xs-8 col-sm-4"><input type="text" class="form-control" placeholder="Complemento" v-model="address.complement" @change="_remap();"><br></div>
			<div class="col-xs-6 col-sm-4"><input type="text" class="form-control" placeholder="CEP" v-model="address.zip_code" @change="_remap();"><br></div>
			<div class="col-xs-6 col-sm-4"><input type="text" class="form-control" placeholder="Bairro" v-model="address.district" @change="_remap();"><br></div>
			<div class="col-xs-12 col-sm-4"><input type="text" class="form-control" placeholder="Cidade" v-model="address.city" @change="_remap();"><br></div>
			<div class="col-xs-6 col-sm-6"><input type="text" class="form-control" placeholder="Estado" v-model="address.state" @change="_remap();"><br></div>
			<div class="col-xs-6 col-sm-6"><input type="text" class="form-control" placeholder="País" v-model="address.country" @change="_remap();"><br></div>
			<div class="col-sm-12" v-if="map==1 && address.embed">
				<iframe :src="address.embed" style="width:100%; height:200px; border:none;"></iframe>
			</div>
		</div>
	</div>
</template>

<script>
Vuel("wpska-address", {
	data: {
		name: "",
		value: "",
		map: "0",
		search: "",
		address: {
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
	},
	methods: {
		_search: function() {
			var app=this, $=jQuery;
			$.get("<?php echo wpska_base(__DIR__, '/search.php'); ?>", {search:app.search}, function(resp) {
				Vue.set(app, "search", "");
				Vue.set(app, "address", resp);
				Vue.set(app, "value", app.address);
			}, "json");
		},
		_remap: function() {
			// alert('Aaa');
		},
	},
	mounted: function() {
		var app = this;
		if (typeof app.value=="object") {
			for(var i in app.address) {
				if (typeof app.value[i]=="undefined") continue;
				app.address[i] = app.value[i];
			}
		}
	},
});
</script>