<?php

class Wpska_Posttypes_Actions extends Wpska_Actions
{
	public function init()
	{
		$wpska_posttypes = get_option('wpska_posttypes', '[]');
		$wpska_posttypes = json_decode($wpska_posttypes, true);
		$wpska_posttypes = is_array($wpska_posttypes)? $wpska_posttypes: array();
		foreach($wpska_posttypes as $pottype) {
			if ($pottype['posttype_active']=='0') continue;
			register_post_type($pottype['posttype_slug'], $pottype['posttype_data']);
		}


		$wpska_taxonomies = get_option('wpska_taxonomies', '[]');
		$wpska_taxonomies = json_decode($wpska_taxonomies, true);
		$wpska_taxonomies = is_array($wpska_taxonomies)? $wpska_taxonomies: array();
		foreach($wpska_taxonomies as $taxonomy) {
			if ($taxonomy['taxonomy_active']=='0') continue;
			register_taxonomy($taxonomy['taxonomy_slug'], $taxonomy['taxonomy_posttypes'], $taxonomy['taxonomy_data']);
		}
	}



	public function wpska_settings()
	{
		wpska_tab('Post types', function() {

		$wpska_posttypes = get_option('wpska_posttypes', '[]');
		$wpska_posttypes = json_decode($wpska_posttypes, true);
		$wpska_posttypes = is_array($wpska_posttypes)? $wpska_posttypes: array();

		$wpska_taxonomies = get_option('wpska_taxonomies', '[]');
		$wpska_taxonomies = json_decode($wpska_taxonomies, true);
		$wpska_taxonomies = is_array($wpska_taxonomies)? $wpska_taxonomies: array();

		?>
		<div data-vue="vuePosttypes">
			<div class="text-right">
				<button type="button" class="btn btn-default" @click="_posttypeAdd();"><i class="fa fa-fw fa-plus"></i> Post Type</button>
			</div><br>

			<!-- Modal edit -->
			<div class="modal fade modal-posttype-edit" id="modal-posttype-edit" v-if="posttypeEdit">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">{{ posttypeEdit.posttype_data.labels.name||'No name' }}</h4>
						</div>
						<div class="modal-body">
							<div role="tabpanel">
								<!-- Nav tabs -->
								<ul class="nav nav-tabs" role="tablist">
									<li role="presentation" class="active"><a href="#wpska-posttype-edit-basics" data-toggle="tab">Basics</a></li>
									<li role="presentation"><a href="#wpska-posttype-edit-labels" data-toggle="tab">labels</a></li>
									<li role="presentation"><a href="#wpska-posttype-edit-options" data-toggle="tab">options</a></li>
								</ul><br>
							
								<!-- Tab panes -->
								<div class="tab-content">
									<div role="tabpanel" class="tab-pane active" id="wpska-posttype-edit-basics">
										<div class="row">
											<div class="form-group col-sm-3">
												<label>Ícone</label>
												<div class="input-group">
													<div class="input-group-addon"><i :class="'dashicons '+posttypeEdit.posttype_data.menu_icon"></i></div>
													<select class="form-control" v-model="posttypeEdit.posttype_data.menu_icon">
														<option value="">Selecionar</option>
														<option :value="icon.slug" v-for="icon in icons">{{ icon.name }}</option>
													</select>
												</div>
											</div>
											<div class="form-group col-sm-3">
												<label>Ativar</label>
												<select class="form-control" v-model="posttypeEdit.posttype_active">
													<option value="0">Inativo</option>
													<option value="1">Ativo</option>
												</select>
											</div>
											<div class="form-group col-sm-6">
												<label>Slug</label>
												<input type="text" class="form-control" placeholder="Name" v-model="posttypeEdit.posttype_slug">
											</div>
											<div class="form-group col-sm-6">
												<label>Plural</label>
												<input type="text" class="form-control" placeholder="Name" v-model="posttypeEdit.posttype_data.labels.name" @keyup="_posttypeFix(posttypeEdit);">
											</div>
											<div class="form-group col-sm-6">
												<label>Singular</label>
												<input type="text" class="form-control" placeholder="Singular_name" v-model="posttypeEdit.posttype_data.labels.singular_name" @keyup="_posttypeFix(posttypeEdit);">
											</div>
											<div class="form-group col-sm-12">
												<label>Descrição</label>
												<textarea class="form-control" v-model="posttypeEdit.posttype_data.description"></textarea>
												<input type="text" class="form-control" placeholder="Singular_name"  @keyup="_posttypeFix(posttypeEdit);">
											</div>
										</div>
									</div>

									<div role="tabpanel" class="tab-pane" id="wpska-posttype-edit-labels">
										<div class="row">
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Menu_name" readonly v-model="posttypeEdit.posttype_data.labels.menu_name">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Name_admin_bar" readonly v-model="posttypeEdit.posttype_data.labels.name_admin_bar">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Add_new" readonly v-model="posttypeEdit.posttype_data.labels.add_new">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Add_new_item" readonly v-model="posttypeEdit.posttype_data.labels.add_new_item">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="New_item" readonly v-model="posttypeEdit.posttype_data.labels.new_item">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Edit_item" readonly v-model="posttypeEdit.posttype_data.labels.edit_item">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="View_item" readonly v-model="posttypeEdit.posttype_data.labels.view_item">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="All_items" readonly v-model="posttypeEdit.posttype_data.labels.all_items">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Search_items" readonly v-model="posttypeEdit.posttype_data.labels.search_items">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Parent_item_colon" readonly v-model="posttypeEdit.posttype_data.labels.parent_item_colon">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Not_found" readonly v-model="posttypeEdit.posttype_data.labels.not_found">
											</div>
											<div class="form-group col-sm-6">
												<input type="text" class="form-control" placeholder="Not_found_in_trash" readonly v-model="posttypeEdit.posttype_data.labels.not_found_in_trash">
											</div>
										</div>
									</div>

									<div role="tabpanel" class="tab-pane" id="wpska-posttype-edit-options">
										<div class="row">
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.public"> public</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.publicly_queryable"> publicly_queryable</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.show_ui"> show_ui</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.show_in_menu"> show_in_menu</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.query_var"> query_var</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.has_archive"> has_archive</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.hierarchical"> hierarchical</label></div>

											<div class="col-xs-12"><strong>Supports</strong></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.supports" value="title"> title</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.supports" value="editor"> editor</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.supports" value="author"> author</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.supports" value="thumbnail"> thumbnail</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.supports" value="excerpt"> excerpt</label></div>
											<div class="form-group col-sm-6"><label><input type="checkbox" v-model="posttypeEdit.posttype_data.supports" value="comments"> comments</label></div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button class="btn btn-primary" type="submit" name="wpska-settings">Salvar</button>
						</div>
					</div>
				</div>
			</div>
			<!-- Modal edit -->


			<div class="list-group">
				<div class="list-group-item text-center text-muted" v-if="posttypes.length==0">Nenhum post type criado</div>

				<draggable :list="posttypes" :options="{animation:150, handle:'._handle'}">
					<div class="list-group-item" :class="'posttype-active-'+posttype.posttype_active" v-for="posttype in posttypes">
						<div class="pull-right">
							<a href="javascript:;" class="fa fa-fw fa-pencil" @click="_posttypeEdit(posttype);"></a>
							<a href="javascript:;" class="fa fa-fw fa-remove" @click="_posttypeRemove(posttype);"></a>
						</div>
						<span class="_handle"></span>
						<strong>{{ posttype.posttype_data.labels.name }} &nbsp;</strong>
					</div>
				</draggable>
			</div>

			<!-- taxonomies -->

			<br><div class="text-right">
				<button type="button" class="btn btn-default" @click="_taxonomyAdd();">
					<i class="fa fa-fw fa-plus"></i> Taxonomia
				</button>
			</div><br>

			<div class="list-group">
				<div class="list-group-item text-center text-muted" v-if="taxonomies.length==0">Nenhuma taxonomia criada</div>

				<draggable :list="taxonomies" :options="{animation:150, handle:'._handle'}">
					<div class="list-group-item" :class="'taxonomy-active-'+taxonomy.taxonomy_active" v-for="taxonomy in taxonomies">
						<div class="pull-right">
							<a href="javascript:;" class="fa fa-fw fa-pencil" @click="_taxonomyEdit(taxonomy);"></a>
							<a href="javascript:;" class="fa fa-fw fa-remove" @click="_taxonomyRemove(taxonomy);"></a>
						</div>
						<span class="_handle"></span>
						<strong>{{ taxonomy.taxonomy_data.labels.name }} &nbsp;</strong>
						<small class="text-muted">{{ taxonomy.taxonomy_posttypes.join(', ') }}</small>
					</div>
				</draggable>
			</div>

			<!-- taxonomies edit -->
			<div class="modal fade" id="modal-taxonomy-edit" v-if="taxonomyEdit">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							<h4 class="modal-title">{{ taxonomyEdit.taxonomy_data.labels.name||'No title' }}</h4>
						</div>
						<div class="modal-body">
							<div role="tabpanel">
								<div role="tabpanel">
									<!-- Nav tabs -->
									<ul class="nav nav-tabs" role="tablist">
										<li role="presentation" class="active"><a href="#modal-taxonomy-edit-basics" aria-controls="home" role="tab" data-toggle="tab">home</a></li>
										<li role="presentation"><a href="#modal-taxonomy-edit-labels" aria-controls="tab" role="tab" data-toggle="tab">labels</a></li>
										<li role="presentation"><a href="#modal-taxonomy-edit-posttypes" aria-controls="tab" role="tab" data-toggle="tab">posttypes</a></li>
									</ul><br>
								
									<!-- Tab panes -->
									<div class="tab-content">
										<div role="tabpanel" class="tab-pane active" id="modal-taxonomy-edit-basics">
											<div class="row">
												<div class="col-sm-6 form-group">
													<label>Ativar</label>
													<select class="form-control" v-model="taxonomyEdit.taxonomy_active">
														<option value="0">Inativo</option>
														<option value="1">Ativo</option>
													</select>
												</div>

												<div class="col-sm-6 form-group">
													<label>Slug</label>
													<input type="text" class="form-control" v-model="taxonomyEdit.taxonomy_slug" @keyup="_taxonomyFix(taxonomyEdit);">
												</div>

												<div class="col-sm-6 form-group">
													<label>Plural</label>
													<input type="text" class="form-control" v-model="taxonomyEdit.taxonomy_data.labels.name" @keyup="_taxonomyFix(taxonomyEdit);">
												</div>

												<div class="col-sm-6 form-group">
													<label>Singular</label>
													<input type="text" class="form-control" v-model="taxonomyEdit.taxonomy_data.labels.singular_name" @keyup="_taxonomyFix(taxonomyEdit);">
												</div>
											</div>
										</div>

										<div role="tabpanel" class="tab-pane" id="modal-taxonomy-edit-labels">
											<div class="row">
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.search_items"></div>
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.all_items"></div>
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.parent_item"></div>
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.parent_item_colon"></div>
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.edit_item"></div>
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.update_item"></div>
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.add_new_item"></div>
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.new_item_name"></div>
												<div class="col-sm-6 form-group"><input type="text" class="form-control" readonly v-model="taxonomyEdit.taxonomy_data.labels.menu_name"></div>
											</div>
										</div>

										<div role="tabpanel" class="tab-pane" id="modal-taxonomy-edit-posttypes">
											<div class="row">
												<label class="col-xs-6" v-for="ptype in all_post_types">
													<input type="checkbox" v-model="taxonomyEdit.taxonomy_posttypes" :value="ptype">
													{{ ptype }}
												</label>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button class="btn btn-primary" type="submit" name="wpska-settings">Salvar</button>
						</div>
					</div>
				</div>
			</div>
			<!-- taxonomies edit -->

			<textarea name="wpska_posttypes" style="display:none;">{{ posttypes }}</textarea>
			<textarea name="wpska_taxonomies" style="display:none;">{{ taxonomies }}</textarea>
			<!-- <pre>{{ $data }}</pre> -->
		</div>

		<style>
		._handle:after {display:inline-block; width:20px; text-align:center; content:":::"; cursor:move; opacity:.7;}
		
		.posttype-active-0 {background:#eee;}
		.posttype-active-1 {}

		.taxonomy-active-0 {background:#eee;}
		.taxonomy-active-1 {}
		</style>

		<script>
		var vuePosttypes = function() {
			return {
				data: {
					all_post_types: <?php echo json_encode(array_values(get_post_types())); ?>,
					taxonomyEdit: false,
					taxonomies: <?php echo json_encode($wpska_taxonomies); ?>,
					posttypeEdit: false,
					posttypes: <?php echo json_encode($wpska_posttypes); ?>,
					icons: <?php echo json_encode(wpska_icons('dashicons')); ?>,
				},
				methods: {
					_posttypeDefault: function(posttype) {
						posttype = this._default(posttype, {
							posttype_active: "0",
							posttype_slug: "",
							posttype_data: {
								"labels": {
									"name": "", // Projetos
									"singular_name": "", // Projeto
									"menu_name": "", // Projetos
									"name_admin_bar": "", // Projeto
									"add_new": "", // Add New
									"add_new_item": "", // Add New Projeto
									"new_item": "", // New Projeto
									"edit_item": "", // Edit Projeto
									"view_item": "", // View Projeto
									"all_items": "", // All Projetos
									"search_items": "", // Search Projetos
									"parent_item_colon": "", // Parent Projetos:
									"not_found": "", // No projetos found.
									"not_found_in_trash": "", // No projetos found in Trash.
								},
								"description": "",
								"public": true,
								"publicly_queryable": true,
								"show_ui": true,
								"show_in_menu": true,
								"query_var": true,
								"rewrite": {"slug": ""},
								"capability_type": "post",
								"has_archive": true,
								"hierarchical": false,
								"menu_position": null,
								"menu_icon": null,
								"supports": [
									"title",
									"editor",
									"author",
									"thumbnail",
									"excerpt",
									"comments",
								],
							},
						});

						var name = posttype.posttype_data.labels.name||"";
						var singular_name = posttype.posttype_data.labels.singular_name||"";
						var defaults = {
							"menu_name": name,
							"name_admin_bar": singular_name,
							"add_new": "Novo",
							"add_new_item": ("Novo "+singular_name),
							"new_item": ("Novo "+singular_name),
							"edit_item": ("Editar "+singular_name),
							"view_item": ("Ver "+name),
							"all_items": ("Todos os "+name),
							"search_items": ("Pesquisar "+singular_name),
							"parent_item_colon": (singular_name+" pai"),
							"not_found": ("Nenhum "+singular_name+" encontrado"),
							"not_found_in_trash": ("Nenhum "+singular_name+" na lixeira"),
						};

						for(var i in defaults) { posttype.posttype_data.labels[i] = defaults[i]; }
						posttype.posttype_data.rewrite.slug = (posttype.posttype_slug||"");
						return posttype;
					},

					_posttypeAdd: function(posttype) {
						var $=jQuery;
						posttype = this._posttypeDefault(posttype);
						this._add(this, 'posttypes', posttype);

						var posttypeEdit = this.posttypes[ this.posttypes.length-1 ];
						Vue.set(this, 'posttypeEdit', posttypeEdit);
						setTimeout(function() {
							$("#modal-posttype-edit").modal('show');
						}, 100);
					},

					_posttypeEdit: function(posttype) {
						Vue.set(this, "posttypeEdit", posttype);
						setTimeout(function() {
							$("#modal-posttype-edit").modal('show');
						}, 100);
					},

					_posttypeFix: function(posttype) {
						posttype = this._posttypeDefault(posttype);
					},

					_posttypeRemove: function(posttype) {
						if (! confirm("Tem certeza que deseja deletar este projeto?")) return false;
						this._remove(this, 'posttypes', posttype);
					},

					_taxonomyDefault: function(taxonomy) {
						taxonomy = this._default(taxonomy, {
							taxonomy_slug: null,
							taxonomy_active: 0,
							taxonomy_posttypes: [],
							taxonomy_data: {
								"hierarchical": true,
								"labels": {
									"name": "", //Genres
									"singular_name": "", //Genre
									"search_items": "", //Search Genres
									"all_items": "", //All Genres
									"parent_item": "", //Parent Genre
									"parent_item_colon": "", //Parent Genre:
									"edit_item": "", //Edit Genre
									"update_item": "", //Update Genre
									"add_new_item": "", //Add New Genre
									"new_item_name": "", //New Genre Name
									"menu_name": "", //Genre
								},
								"show_ui": true,
								"show_admin_column": true,
								"query_var": true,
								"rewrite": {"slug":""},
							},
						});

						var name = taxonomy.taxonomy_data.labels.name||"";
						var singular_name = taxonomy.taxonomy_data.labels.singular_name||"";
						var defs = {
							"search_items": ("Pesquisar "+name),
							"all_items": ("Todos os(as) "+name),
							"parent_item": (singular_name+" pai"),
							"parent_item_colon": (singular_name+" pai"),
							"edit_item": ("Editar "+singular_name),
							"update_item": ("Alterar "+singular_name),
							"add_new_item": ("Adicionar novo "+singular_name),
							"new_item_name": ("Novo nome de "+singular_name),
							"menu_name": (name),
						};

						for(var i in defs) { taxonomy.taxonomy_data.labels[i] = defs[i]; }
						taxonomy.taxonomy_data.rewrite.slug = taxonomy.taxonomy_slug;
						return taxonomy;
					},

					_taxonomyAdd: function(taxonomy) {
						taxonomy = this._taxonomyDefault(taxonomy);
						this._add(this, 'taxonomies', taxonomy);
						var taxonomyEdit = this.taxonomies[ this.taxonomies.length-1 ];
						this._taxonomyEdit(taxonomyEdit);
					},

					_taxonomyRemove: function(taxonomy) {
						if (!confirm("Tem certeza que deseja deletar esta taxonomia?")) return false;
						this._remove(this, 'taxonomies', taxonomy);
					},

					_taxonomyEdit: function(taxonomy) {
						Vue.set(this, "taxonomyEdit", taxonomy);
						setTimeout(function() {
							$("#modal-taxonomy-edit").modal('show');
						}, 100);
					},

					_taxonomyFix: function(taxonomy) {
						taxonomy = this._taxonomyDefault(taxonomy);
					},
				},
			};
		};
		</script>
		<?php });
	}

}


new Wpska_Posttypes_Actions();
