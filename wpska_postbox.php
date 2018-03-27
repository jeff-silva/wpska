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
			<div id="wpska_settings_postbox">
				<button type="button" class="btn btn-default" @click="_add(postboxes, _postbox());">Add postbox</button>
				<br><br>
				<div class="panel panel-default" v-for="postbox in postboxes">
					<div class="panel-heading">
						<a href="javascript:;" class="a fa fa-fw fa-remove pull-right" @click="_remove(postboxes, postbox, 'Tem certeza que deseja deletar este postbox?');"></a>
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
									<label>Description</label>
									<textarea v-model="postbox.text" class="form-control"></textarea>
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
								<button type="button" class="btn btn-default" @click="_add(postbox.fields, _field());">Add field</button>
								<br><br>
								<draggable :list="postbox.fields" :options="{handle:'._handle'}">
									<div class="panel panel-default" v-for="field in postbox.fields">
										<div class="panel-heading _handle">::: {{ field.title }}</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-xs-6">
													<strong>Título</strong>
													<input type="text" v-model="field.title" class="form-control">
												</div>
												<div class="col-xs-4">
													<strong>Name</strong>
													<input type="text" v-model="field.field_name" class="form-control">
												</div>
												<div class="col-xs-2 text-right">
													<a href="javascript:;" class="fa fa-fw fa-remove" @click="_remove(postbox.fields, field, 'Remover este campo?');"></a>
												</div>
												<div class="col-xs-12"><br>
													<strong>Descrição</strong>
													<textarea v-model="field.text" class="form-control"></textarea>
												</div>
												<div class="col-xs-6"><br>
													<strong>Tipo</strong>
													<select v-model="field.field_type" class="form-control">
														<option value="">Nenhum</option>
														<option :value="ui_type.title" v-for="ui_type in ui_types">{{ ui_type.title }}</option>
													</select>
												</div>
												<div class="col-xs-6"><br>
													<strong>Parâmetros</strong>
													<input type="text" v-model="field.field_params" class="form-control">
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
			new Vue({
				el: "#wpska_settings_postbox",
				data: {
					postboxes: <?php echo json_encode($wpska_postbox_boxes); ?>,
					ui_types: <?php echo json_encode(Wpska_Ui::types()); ?>,
					post_types: <?php echo json_encode(get_post_types()); ?>,
				},
				methods: {
					_add: function(items, item, prepend) {
						if (prepend||false) {
							items.unshift(item);
						}
						else {
							items.push(item);
						}
					},

					_remove: function(items, item, msg_confirm) {
						if (msg_confirm||false) {
							if (! confirm(msg_confirm)) {
								return false;
							}
						}
						var index = items.indexOf(item);
						items.splice(index, 1);
					},

					_postbox: function(postbox) {
						var $=jQuery;
						var _id = Math.round(Math.random()*999);
						postbox = $.extend(postbox, {
							id: _id,
							title: ("Postbox #"+_id),
							text: "",
							post_types: [],
							fields: [],
						});
						return postbox;
					},

					_field: function(field) {
						var $=jQuery;
						var _id = Math.round(Math.random()*999);
						return $.extend(field, {
							id: _id,
							title: ("Field #"+_id),
							text: ("Input ID #"+_id),
							field_name: ("field_"+_id),
							field_type: "",
							field_params: "",
						});
					},
				},
			});
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
					if ($postbox['text']) echo "<div style='padding:10px 0px;'>{$postbox['text']}</div>";
					echo '<table style="width:100%;"><col width="*"><col width="*"><tbody>';
					foreach($postbox['fields'] as $field) {
						parse_str($field['field_params'], $field['field_params']);
						$field['field_params']['name'] = "postmeta[{$field['field_name']}]";
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