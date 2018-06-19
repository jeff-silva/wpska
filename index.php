<?php

include __DIR__ . '/wpska.php';

class Route
{
	static $content=null;
	static function content()
	{
		echo self::$content;
	}

	static function match($method='get', $path, $callback)
	{
		$path = trim($path, '/');
		$_SERVER['PATH_INFO'] = trim((isset($_SERVER['PATH_INFO'])? $_SERVER['PATH_INFO']: '/'), '/');
		$go = array();
		$go['match'] = $path==$_SERVER['PATH_INFO'];
		$go['match_request_method'] = $_SERVER['REQUEST_METHOD']==strtoupper($method);
		$go['is_callable'] = is_callable($callback);
		if (array_sum($go)==sizeof($go)) {
			ob_start();
			call_user_func($callback);
			self::$content = ob_get_clean();
		}
	}

	static function get($path=null, $callback=null)
	{
		return self::match('get', $path, $callback);
	}

	static function post($path=null, $callback=null)
	{
		return self::match('post', $path, $callback);
	}
}



class Tab
{
	static $tabs = array();
	static $tab = null;

	static function add($title, $callback)
	{
		$id = strtolower(preg_replace('/[^a-zA-Z0-9-]+/', '-', $title));
		self::$tabs[] = array('id'=>$id, 'title'=>$title, 'callback'=>$callback);
		self::$tab = isset($_GET['tab'])? $_GET['tab']: false;
		self::$tab = (!self::$tab AND sizeof(self::$tabs)>=1)? self::$tabs[0]['id']: self::$tab;
	}


	static function menu()
	{
		echo '<ul class="list-group">';
		foreach(self::$tabs as $tab) {
			$theme = (isset($_GET['theme']) AND !empty($_GET['theme']))? $_GET['theme']: '';
			echo "<li class='list-group-item'><a href='?tab={$tab['id']}&theme={$theme}'>{$tab['title']}</a></li>";
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



Route::get('/logout', function() {
	wpska_auth(uniqid());
	wpska_redirect('back'); die;
});


Route::post('/login', function() {
	wpska_auth($_POST['pass']);
	wpska_redirect('back'); die;
});


Route::get('/login', function() { ?>
	<?php if (wpska_auth()): ?>
	<div class="panel panel-default">
		<div class="panel-heading">
			<a href="index.php/logout" class="pull-right">Sair</a>
			Bem vindo
		</div>
		<div class="panel-body">
			<a href="index.php">Home</a>
		</div>
	</div>
	<?php else: ?>
	<div class="panel panel-default" style="max-width:300px; margin:25px auto;">
		<div class="panel-body">
			<form action="" method="post">
				<div class="form-group">
					<label>Senha</label>
					<input type="password" name="pass" value="" class="form-control">
				</div>
				<div class="text-right">
					<a href="index.php" class="btn btn-link pull-left">Home</a>
					<input type="submit" value="Login" class="btn btn-default">
				</div>
			</form>
		</div>
	</div>
	<?php endif; ?>
<?php });


Route::get('', function() {
	Tab::add('Form', function() { ?>

	<div id="app-form">
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

		<div class="panel panel-default">
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 text-right">
						<button class="btn btn-default" @click="_inputAdd();">Add</button>
					</div>
					<div class="col-xs-12" v-for="input in inputs">
						<div class="panel panel-default">
							<div class="panel-body">
								<div class="row">
									<div class="col-xs-6">
										<div class="form-group">
											<label>Tipo</label>
											<select class="form-control wpska-select" v-model="input.type">
												<option value="">Selecione</option>
												<option value="checkbox">Checkbox</option>
												<option value="radio">Radio</option>
												<option value="select">Select</option>
											</select>
										</div>

										<div class="form-group">
											<label>Name</label>
											<input type="text" class="form-control" v-model="input.name">
										</div>

										<div class="form-group">
											<label>Options</label>
											<textarea class="form-control" v-model="input.options" @keyup="_inputOptionsToArr(input);"></textarea>
										</div>
									</div>

									<div class="col-xs-6">
										<div class="panel panel-default">
											<div class="panel-body">
												<div v-if="input.type==''">
													Nenhum selecionado
												</div>

												<div v-if="input.type=='checkbox'">
													<label>
														<input type="checkbox" class="wpska-check" :name="input.name" v-model="input.value">
														<span class="wpska-check-0"><i class="fa fa-fw fa-square-o"></i></span>
														<span class="wpska-check-1"><i class="fa fa-fw fa-check-square-o"></i></span>
													</label>
												</div>

												<div v-if="input.type=='radio'">
													<label style="display:block;" v-for="opt in input.optionsArr">
														<input type="checkbox" class="wpska-check" :name="input.name" v-model="opt.value" :checked="opt.selected">
														<span class="wpska-check-0"><i class="fa fa-fw fa-circle-o"></i> {{ opt.label }}</span>
														<span class="wpska-check-1"><i class="fa fa-fw fa-check-circle-o"></i> {{ opt.label }}</span>
													</label>
												</div>

												<div v-if="input.type=='select'">
													<select class="form-control wpska-select" :name="input.name">
														<option :value="opt.value" :selected="opt.selected" v-for="opt in input.optionsArr">{{ opt.label }}</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<pre>{{ $data }}</pre>
	</div>

	<script>
	window.wpskaInitForm = function() {
		new Vue2({
			el: "#app-form",
			data: {
				form: {},
				inputs: [],
			},
			methods: {
				_inputAdd: function() {
					this.inputs.push({
						type:"select",
						name:"",
						value:"",
						options:"",
						optionsArr:[],
					});
				},
				_inputOptionsToArr: function(input) {
					var opts = [];
					var optionsArr = input.options.split("\n");
					for(var i in optionsArr) {
						if (! optionsArr[i]) continue;
						opts.push({
							label: optionsArr[i],
							value: optionsArr[i],
							selected: false,
						});
					}
					input.optionsArr = opts;
				},
			},
		});
	};
	</script>
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
	window.wpskaInitVue2 = function() {
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
	};
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
	<div id="app-flatpickr">
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
	<script>
	window.wpskaInitFlatpickr = function() {
		new Vue2({
			el: "#app-flatpickr",
			data: {
				pickr1: null,
				pickr2: null,
			},
		});
	};
	</script>
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



	<div id="app-test">
		<div class="row">
			<div class="col-xs-6"><wpska-test v-model="vmodel1"></wpska-test></div>
			<div class="col-xs-6"><wpska-test v-model="vmodel2"></wpska-test></div>
		</div>
		<pre style="color:red;">{{ $data }}</pre>
	</div>

	<template id="wpska-test">
		<input type="text" v-model="attr.value" class="form-control">
		<pre style="color:green;">{{ $data }}</pre>
	</template>


	<script>
	window.Vuel2 = function(tagname, params) {
		console.log(params);
		params = typeof params=="object"? params: {};
		params.data = typeof params.data=="object"? params.data: {};
		var templateHTML = (document.currentScript? document.currentScript.ownerDocument.getElementById(tagname).innerHTML: "");
		var proto = Object.create(HTMLElement.prototype);


		proto.createdCallback = function() {};
		proto.attachedCallback = function() {
			var $=jQuery;
			var parent = this;
			var $parent = $(parent);

			params.data.attr = (typeof params.data.attr=="object")? params.data.attr: {};
			params.data.attr.value = params.data.attr.value||"";
			for(var i in params.data.attr) {
				var val =(this.getAttribute(i) || params.data.attr[i]);
				try { eval('val='+val); } catch(e) {}
				params.data.attr[i] = val;
			}


			if (this.getAttribute("template")) {
				templateHTML = document.getElementById( this.getAttribute("template") ).innerHTML;
			}

			templateHTML = templateHTML.replace(/\<content\>\<\/content\>/g, this.innerHTML);
			this.innerHTML = "<div>"+ templateHTML +"</div>";

			params.el = this.children[0];
			// params.data = function() { return params.data; };
			var app = new Vue(params);
			
			for(var i in (params.methods||[])) {
				proto[i] = app[i];
			}
		};

		return document.registerElement(tagname, {prototype: proto});
	};

	Vuel2("wpska-test", {
		data: {
			attr: {},
		},
	});

	new Vue2({
		el: "#app-test",
		data: {
			vmodel1: {value:"vmodel1"},
			vmodel2: {value:"vmodel2"},
			value1: {value:"value1"},
			value2: {value:"value2"},
			user: {
				name: "John Doe",
				email: "johndoe@mail.com",
				favoriteColors: [
					{hex:"#000000", name:"Black"},
					{hex:"#ff0000", name:"Red"},
				],
			},
		},
	});
	</script>
	<?php });

	?>
	<div class="row">
		<div class="col-xs-3">
			<?php Tab::menu(); ?>

			<?php $boots = array(
				'' => 'Nenhum',
				'cerulean' => 'Cerulean',
				'cosmo' => 'Cosmo',
				'cyborg' => 'Cyborg',
				'darkly' => 'Darkly',
				'flatly' => 'Flatly',
				'journal' => 'Journal',
				'lumen' => 'Lumen',
				'paper' => 'Paper',
				'readable' => 'Readable',
				'sandstone' => 'Sandstone',
				'simplex' => 'Simplex',
				'slate' => 'Slate',
				'spacelab' => 'Spacelab',
				'superhero' => 'Superhero',
				'united' => 'United',
				'yeti' => 'Yeti',
			); ?>

			<select class="form-control wpska-select" onchange="location.href = './?tab=<?php echo $_GET['tab']; ?>&theme='+ $(this).val();">
				<?php foreach($boots as $boot=>$name): ?>
				<option value="<?php echo $boot; ?>" <?php echo (isset($_GET['theme']) AND $_GET['theme']==$boot)? 'selected': null; ?> ><?php echo $name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="col-xs-9">
			<?php Tab::render(); ?>
		</div>
		<div class="col-xs-12 text-right">
			<br>
			<a href="index.php/login"><?php echo wpska_auth()? 'Admin': 'Login'; ?></a>
			<br>
		</div>
	</div>
	<?php
});








?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
	<title>Wpska</title>
	<base href="http://projetos.jsiqueira.com/git/wpska/">
	<script src="./wpska.js"></script>
</head>
<?php $theme = (isset($_GET['theme']) AND !empty($_GET['theme']))? $_GET['theme']: ''; ?>
<body data-bootswatch="<?php echo $theme; ?>">
	
	<!-- Loading -->
	<div class="wpska-loading" style="position:fixed; top:0; left:0; width:100%; height:100%; background:#fff; z-index:99;">
		<div style="position:absolute; left:50%; top:50%; transform:translate(-50%, -50%); text-align:center;">
			<div class="wpska-loading-01" style="width:50px; height:50px;"></div>
		</div>
	</div>

	<br>
	<div class="container">
		<?php Route::content(); ?>
	</div>
	<br>
</body>
</html>