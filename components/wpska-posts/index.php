<?php

include __DIR__ . '/../../wpska.php';

$sitebase = 'http://projetos.jsiqueira.com/alexcoutinhoimoveis/';

?>

<!-- https://codex.wordpress.org/pt-br:Refer%C3%AAncia_de_Classe/WP_Query -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"></script>
<script src="../vuel.js"></script>

<template id="wpska-posts">
	<div>
		<div class="row">
			<div class="col-sm-6">
				<ul class="nav nav-tabs" role="tablist">
					<li role="presentation" :class="{active:(tab=='basic')}"><a href="javascript:;" @click="tab='basic';">Básico</a></li>
					<li role="presentation" :class="{active:(tab=='meta_query')}"><a href="javascript:;" @click="tab='meta_query';">Meta query</a></li>
				</ul><br>

				<!-- basic -->
				<div v-if="tab=='basic'">
					<div class="form-group">
						<label>Post type</label>
						<input type="text" class="form-control" v-model="query.post_type">
					</div>

					<div class="form-group">
						<label>Status</label>
						<select class="form-control" v-model="query.post_status">
							<option value="">Selecione</option>
							<option value="publish">Publicado</option>
						</select>
					</div>

					<div class="form-group">
						<label>Autores</label>
						<div class="wpska-select">
							<input type="text" class="form-control">
							<select class="form-control" v-model="query.author__in" multiple="multiple">
								<option :value="user.id" v-for="user in users">{{ user.name }}</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<label>Order by</label>
						<div class="input-group">
							<input type="text" class="form-control" v-model="query.orderby">
							<div class="input-group-btn" style="width:0px;"></div>
							<select class="form-control" v-model="query.order">
								<option value="ASC">Crescente</option>
								<option value="DESC">Decrescente</option>
								<option value="RAND">Aleatório</option>
							</select>
						</div>
					</div>
				</div>

				<!-- meta_query -->
				<div v-if="tab=='meta_query'">
					<div class="text-right">
						<button type="button" class="btn btn-xs btn-primary" @click="_meta_query_add();">
							<i class="fa fa-fw fa-plus"></i>
						</button>
					</div><br>
					<table class="table table-stripped table-bordered">
						<thead>
							<tr>
								<th>key</th>
								<th>compare</th>
								<th>value</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(row, keyrow) in query.meta_query" v-if="keyrow!='relation'">
								<td><input type="text" class="form-control" v-model="row.key"></td>
								<td><input type="text" class="form-control" v-model="row.compare"></td>
								<td><input type="text" class="form-control" v-model="row.value"></td>
								<td><a href="javascript:;" class="fa fa-fw fa-remove"></a></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="col-sm-6">
				<button type="button" class="btn btn-defaul" @click="_preview();"><i class="fa fa-search"></i></button>
				<pre>{{ $data }}</pre>
			</div>
		</div>
	</div>
</template>

<script>
Vuel("wpska-posts", {
	data: {
		name: "",
		value: "",
		tab: "basic",
		map: "0",
		search: "",
		query: {
			post_type: "post",
			post_status: "publish",
			author__in: [],
			// author__not_in: [],
			cat: "",
			category__in: "",
			category__not_in: "",
			tag: "",
			tag__in: "",
			tag__not_in: "",
			meta_query: {relation:"OR"},
			orderby: "ID",
			order: "DESC",
		},
		users: [],
		posts: [],
	},
	methods: {
		_populate: function() {
			var app=this;
			$.get("<?php echo $sitebase; ?>/wp-json/wp/v2/users", function(resp) {
				Vue.set(app, "users", resp);
			}, "json");
		},

		_preview: function() {
			var app=this;

			var params = {};
			for(var i in app.query) {
				if (! app.query) continue;
				params[i] = app.query[i];
			}

			$.post("<?php echo $sitebase; ?>/wp-json/wp/v2/posts", params, function(resp) {
				Vue.set(app, "posts", resp);
			}, "json");
		},


		_meta_query_add: function() {
			var app=this;
			var id = Math.round(Math.random()*99999);
			Vue.set(app.query.meta_query, id, {key:"", value:"", compare:"="});
		},
	},
	mounted: function() {
		this._populate();
	},
});
</script>