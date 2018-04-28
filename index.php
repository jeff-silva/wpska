<?php

class Tab
{
	static $tabs = array();
	static $tab = null;

	static function add($title, $callback)
	{
		$id = base64_encode($title);
		self::$tabs[] = array('id'=>$id, 'title'=>$title, 'callback'=>$callback);
		self::$tab = isset($_GET['tab'])? $_GET['tab']: false;
		self::$tab = (!self::$tab AND sizeof(self::$tabs)>=1)? self::$tabs[0]['id']: self::$tab;
	}


	static function menu()
	{
		echo '<ul class="list-group">';
		foreach(self::$tabs as $tab) {
			echo "<li class='list-group-item'><a href='?tab={$tab['id']}'>{$tab['title']}</a></li>";
		}
		echo '</ul>';
	}


	static function render()
	{
		foreach(self::$tabs as $tab) {
			if ($tab['id']==self::$tab) {
				ob_start();
				call_user_func($tab['callback']);
				$content = ob_get_clean();
				echo $content;
				echo '<br><br><textarea data-codemirror="{readOnly:true}">'. htmlspecialchars($content) .'</textarea>';
			}
		}
	}
}



Tab::add('Form', function() {

$checks = array(
	'wpska-radio',
	'wpska-radio-o',
	'wpska-check',
	'wpska-check-o',
);

?>
<div class="panel panel-default">
	<div class="panel-heading">Checks</div>
	<div class="panel-body">
		<div class="row">
			<?php foreach($checks as $check): ?>
			<div class="col-xs-6 col-sm-3">
				<label>
					<input type="checkbox" class="<?php echo $check; ?>">
					<span class="<?php echo $check; ?>"></span>
					<?php echo $check; ?> 
				</label>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<div class="panel panel-default">
	<div class="panel-heading">Masks</div>
	<div class="panel-body">
		<div class="row">
			<div class="form-group col-xs-6">
				<label>CPF</label>
				<input type="text" class="form-control" data-mask="999.999.999-99">
			</div>

			<div class="form-group col-xs-6">
				<label>Phone</label>
				<input type="text" class="form-control" data-mask="(99) 99999-9999">
			</div>
		</div>
	</div>
</div>
<?php });



Tab::add('Vue2', function() { ?>
<div id="app-vue2">
	<button class="btn btn-default" @click="_itemAdd();">_itemAdd</button>
	<br><br>

	<div class="row">
		<div class="col-xs-6">
			<div class="panel panel-default">
				<div class="panel-heading">Items</div>
				<div class="panel-body">
					<div class="row">
						<draggable :list="items" :options="{animation:150}">
							<div class="col-xs-4" v-for="item in items">
								<div style="padding:5px;" :class="{'bg-primary':_exists(selecteds, item)}">
									<div class="text-right">
										<a href="javascript:;" class="fa fa-fw fa-remove" @click="_remove(false, 'items', item, null, '.col-xs-3');"></a>
									</div>
									<div class="wpska-cover" style="width:100%; height:100px;" @click="_toggle(false, 'selecteds', item);">
										<img :src="item.thumb" alt="">
									</div>
								</div><br>
							</div>
						</draggable>
					</div>
				</div>
			</div>
		</div>
		<div class="col-xs-6">
			<div class="panel panel-default">
				<div class="panel-heading">Selecteds</div>
				<div class="panel-body">
					<div class="list-grou">
						<div class="list-group-item" v-for="item in selecteds">
							<div class="wpska-cover" style="width:37px; height:37px; float:left; margin-right:5px;">
								<img :src="item.thumb" alt="">
							</div>
							<div style="float:left;">
								#{{ item._id }}
								<div v-if="!_exists(items, item)"><small class="text-muted">Removido</small></div>
							</div>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
new Vue2({
	el: "#app-vue2",
	data: {
		items: [],
		selecteds: [],
	},
	methods: {
		_item: function(item) {
			return this._default(item, {
				name: "Item #{id}",
				thumb: ("https://picsum.photos/200/200?rand="+Math.round(Math.random()*999)),
			});
		},
		_itemAdd: function(item) {
			item = this._item(item);
			return this._prepend(this, "items", item);
		},
	},
});
</script>
<?php });




Tab::add('Slick', function() { ?>
<div data-slick="{slidesToShow:1, arrows:true, asNavFor:'.slick-nav'}" class="slick slick-big">
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?0);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?1);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?2);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?3);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?4);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?5);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?6);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?7);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?8);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?9);"></div></div>
</div>
<div data-slick="{slidesToShow:5, arrows:false, centerMode:true, focusOnSelect:true, asNavFor:'.slick-big'}" class="slick slick-nav">
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?0);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?1);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?2);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?3);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?4);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?5);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?6);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?7);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?8);"></div></div>
	<div><div class="wpska-cover" style="background-image:url(https://picsum.photos/600/400?9);"></div></div>
</div>
<style>
.slick {background:#eee;}
.slick-big .wpska-cover {width:100%; height:400px;}
.slick-nav .wpska-cover {width:100%; height:100px; border:solid 5px #fff;}
.slick-next, .slick-prev {width:10% !important; height:100% !important; background:#0000006e !important; border:solid !important; z-index:9 !important;}
.slick-prev {left:0px !important;}
.slick-next {right:0px !important;}
</style>
<?php });






Tab::add('Flatpicker', function() { ?>
<div data-vue='{data:{pickr1:null, pickr2:null}}'>
	<div class="row">
		<div class="col-xs-6">
			<input type="text" class="form-control" data-flatpickr="{}" v-model="pickr1">
			<pre>{{ pickr1 }}&nbsp;</pre>
		</div>
		<div class="col-xs-6">
			<input type="text" class="form-control" data-flatpickr="{}" v-model="pickr2">
			<pre>{{ pickr2 }}&nbsp;</pre>
		</div>
	</div>
</div>
<?php });



Tab::add('Firebase', function() { ?>
<div data-firebase='{ref:"tarotTeller"}' data-firebase-vue="initVue">
	<button @click="_sync();">_sync();</button>
	<button @click="_projectAdd();">_projectAdd();</button>
	<input type="text" v-model="data.name">
	<hr>
	<draggable :list="data.todos" :options="{animation:150}">
		<div class="panel panel-default" v-for="proj in _array(data, 'projects')">
			<div class="panel-heading">
				<div class="pull-right">
					<a href="javascript:;" class="fa fa-fw fa-remove" @click="_projectRemove(proj);"></a>
				</div>
				<strong>{{ proj.title||'No title' }}</strong>
			</div>
			<div class="panel-body">
				<input type="text" v-model="proj.title" class="form-control" placeholder="Título">
				<hr>
				<button @click="_taskAdd(proj);">_projectAdd();</button>
				<hr>
				<div class="row">
					<draggable :list="data.todos" :options="{animation:150}">
						<div class="col-xs-4" v-for="task in _array(proj, 'tasks')">
							<div class="panel panel-default">
								<div class="panel-body">
									<div class="pull-right">
										<a href="javascript:;" class="fa fa-fw fa-remove" @click="_taskRemove(proj, task);"></a>
									</div>
									<input type="text" v-model="task.title" class="form-control">
									<select class="form-control" v-model="task.status">
										<option value="">Seleciones</option>
										<option :value="status" v-for="status in _taskStatus()">{{ status.title }}</option>
									</select>
								</div>
							</div>
						</div>
					</draggable>
				</div>
			</div>
		</div>
	</draggable>
	<pre>{{ $data }}</pre>
</div>
<script>
var initVue = function() {
	return {
		data: {
			test: false,
		},
		methods: {
			_project: function(project) {
				return this._default(project, {
					title: "Project #{$id}",
				});
			},
			_projectAdd: function() {
				this._add(this.data, 'projects', this._project());
			},
			_projectRemove: function(project) {
				this._remove(this.data, 'projects', project, 'Deseja deletar este projeto?');
			},
			_task: function(task) {
				return this._default(task, {
					title: "Task #{$id}",
					status: null,
				});
			},
			_taskAdd: function(project) {
				this._add(project, 'tasks', this._task());
			},
			_taskRemove: function(project, task) {
				this._remove(project, 'tasks', task);
			},
			_taskStatus: function() {
				return [
					{_id:"not-started", title:"Not started"},
					{_id:"working", title:"Working"},
					{_id:"pendencies", title:"Pendencias"},
					{_id:"Closed", title:"Finalizado"},
				];
			},
		},
	};
};
</script>
<?php });



Tab::add('Codemirror', function() { ?>
<textarea id="codemirror-01" data-codemirror="{}">&lt;div class="link-container"&gt;
	&lt;a href="link-url"&gt;Link&lt;/a&gt;
&lt;/div&gt;

&lt;script&gt;
var numer = 1;
&lt;/script&gt;

&lt;style&gt;
.link-container a {border:solid 1px #ddd;}
&lt;/style&gt;</textarea>

<button class="btn btn-default" onclick="_setText();">_setText();</button>

<script>
var _setText = function() {
	var editor = $("#codemirror-01").get(0).codemirror();
	editor.getDoc().setValue('var msg = "Hi";');
};
</script>
<?php });



Tab::add('Wpska test', function() { ?>
<div data-vue="{data:{test:true}}">
	<wpska-test name="aaa" v-model="test"></wpska-test>
	<pre>{{ $data }}</pre>
</div>
<?php });

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
	<title>Wpska</title>
	<script src="wpska.js"></script>
</head>
<body>
	
	<!-- Loading -->
	<div class="wpska-loading" style="position:fixed; top:0; left:0; width:100%; height:100%; background:#fff; z-index:99;">
		<div style="position:absolute; left:50%; top:50%; transform:translate(-50%, -50%); text-align:center;">
			<div class="wpska-loading-01" style="width:50px; height:50px;"></div>
		</div>
	</div>

	<br>
	<div class="container">
		<div class="row">
			<div class="col-xs-3">
				<?php Tab::menu(); ?>
			</div>
			<div class="col-xs-9">
				<?php Tab::render(); ?>
			</div>
		</div>		
	</div>
	<br>
</body>
</html>