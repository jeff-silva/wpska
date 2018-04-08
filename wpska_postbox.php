<?php

class Wpska_Postbox_Actions extends Wpska_Actions
{
	public function wpska_settings()
	{
		wpska_tab('Postbox', function() {

			if (! class_exists('Wpska_Ui_Actions')): ?>
			<div>Para usar este módulo, é necessário instalar o módulo "User interfaces".</div>
			<?php return null; endif;

			$wpska_postbox_boxes = get_option('wpska_postbox_boxes', '[]');
			$wpska_postbox_boxes = json_decode($wpska_postbox_boxes, true);
			$wpska_postbox_boxes = is_array($wpska_postbox_boxes)? $wpska_postbox_boxes: array();

			?>
			<div data-vue="vuePostbox">
				<div class="text-right">
					<button type="button" class="btn btn-default" @click="_postboxAdd();">
						<i class="fa fa-fw fa-plus"></i> Postbox
					</button>
				</div><br>

				<div class="text-center text-muted" v-if="postboxes.length==0">
					Nenhum Postbox criado
				</div>
				
				<div class="panel panel-default" v-for="postbox in postboxes">
					<div class="panel-heading">
						<a href="javascript:;" class="a fa fa-fw fa-remove pull-right" @click="_postboxRemove(postbox);"></a>
						{{postbox.title}}
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label>Título</label>
									<input type="text" v-model="postbox.title" class="form-control">
								</div>

								<div class="form-group">
									<label>Post types</label><br>
									<label style="padding:5px;" v-for="post_type in post_types">
										<input type="checkbox" v-model="postbox.post_types" :value="post_type">
										{{ post_type }}
									</label>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label>Description</label>
									<textarea v-model="postbox.text" class="form-control" style="height:250px;"></textarea>
								</div>
							</div>
							<div class="col-sm-12">
								<button type="button" class="btn btn-default" @click="_fieldAdd(postbox);">Add field</button>
								<br><br>
								<draggable :list="postbox.fields" :options="{handle:'._handle', animate:150}">
									<div class="panel panel-default" v-for="field in postbox.fields">
										<div class="panel-heading" style="cursor:move;">
											<a href="javascript:;" class="fa fa-fw fa-remove pull-right" @click="_fieldRemove(postbox, field);"></a>
											<span class="_handle"></span> {{ field.title }}
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-xs-6">
													<strong>Título</strong>
													<input type="text" v-model="field.title" class="form-control">
													<br>
													<strong>Name</strong>
													<input type="text" v-model="field.field_name" class="form-control">
												</div>
												<div class="col-xs-6">
													<strong>Tipo</strong>
													<select v-model="field.field_type" class="form-control">
														<option value="">Nenhum</option>
														<option :value="ui_type.method" v-for="ui_type in ui_types">{{ ui_type.title }}</option>
													</select>
													<br>
													<strong>Parâmetros</strong>
													<input type="text" v-model="field.field_params" class="form-control">
												</div>
												<div class="col-xs-12"><br>
													<strong>Descrição</strong>
													<textarea v-model="field.text" class="form-control"></textarea>
												</div>
												<div class="col-xs-12" v-if="field.field_type=='select'"><br>
													<strong>Options</strong>
													<textarea v-model="field.field_options" class="form-control"></textarea>
													<small class="text-muted"><strong>Format:</strong><br>
													Value 1 <br>
													value-2: Value 2</small>
												</div>
											</div>
										</div>
									</div>
								</draggable>
							</div>
						</div>
					</div>
				</div>
				<textarea name="wpska_postbox_boxes" style="display:none;">{{ postboxes }}</textarea>
				<!-- <pre>{{ $data }}</pre> -->
			</div>
			<script>
			var vuePostbox = function() {
				return {
					data: {
						postboxes: <?php echo json_encode($wpska_postbox_boxes); ?>,
						ui_types: <?php echo json_encode(Wpska_Ui::types()); ?>,
						post_types: <?php echo json_encode(get_post_types()); ?>,
					},
					methods: {
						_postbox: function(postbox) {
							postbox = this._default(postbox, {
								title: "",
								text: "",
								post_types: [],
								fields: [],
							});
							return postbox;
						},

						_postboxAdd: function(postbox) {
							postbox = this._postbox(postbox);
							this._add(this, 'postboxes', postbox);
						},

						_postboxRemove: function(postbox) {
							if (! confirm('Tem certeza que deseja deletar este postbox?')) return false;
							this._remove(this, 'postboxes', postbox);
						},

						_field: function(field) {
							field = this._default(field, {
								title: "",
								text: "",
								field_name: "",
								field_type: "",
								field_params: "",
								field_options: "",
							});
							return field;
						},

						_fieldAdd: function(postbox, field) {
							field = this._field(field);
							this._add(postbox, "fields", field);
						},

						_fieldRemove: function(postbox, field) {
							if (!confirm("Tem certeza que deseja remover este campo?")) return false;
							this._remove(postbox, "fields", field);
						},
					},
				};
			};
			</script>
		<?php });
	}


	public function add_meta_boxes()
	{
		$wpska_postbox_boxes = get_option('wpska_postbox_boxes', '[]');
		$wpska_postbox_boxes = json_decode($wpska_postbox_boxes, true);
		$wpska_postbox_boxes = is_array($wpska_postbox_boxes)? $wpska_postbox_boxes: array();
		// dd($wpska_postbox_boxes); die;

		foreach($wpska_postbox_boxes as $postbox) {
			foreach($postbox['post_types'] as $post_type) {
				add_meta_box($postbox['id'], $postbox['title'], function() use($postbox) {
					global $post;
					if ($postbox['text']) echo "<div style='padding:10px 0px;'>{$postbox['text']}</div>";
					echo '<table style="width:100%;"><col width="*"><col width="*"><tbody>';
					foreach($postbox['fields'] as $field) {
						parse_str($field['field_params'], $field['field_params']);
						$field['field_params']['name'] = "postmeta[{$field['field_name']}]";
						$field['field_params']['options'] = $field['field_options'];
						$value = get_post_meta($post->ID, $field['field_name'], true);

						echo '<tr>';
						echo "<td><strong>{$field['title']}</strong><br><small>{$field['text']}</small></td>";
						echo '<td>';
						call_user_func(array('Wpska_Ui', $field['field_type']), $value, $field['field_params']);
						echo '</td>';
						echo '</tr>';
					}
					echo '</tbody></table>';
				}, $post_type);
			}
		}
	}
}


new Wpska_Postbox_Actions();