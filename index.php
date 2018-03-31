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
				echo '<br><textarea data-codemirror="{}">'. htmlspecialchars($content) .'</textarea>';
			}
		}
	}
}




Tab::add('Vue', function() { ?>
<div data-vue="{init:vueInit}">
	<div class="text-right">
		<button class="btn btn-default" @click="_todoAdd();">_todoAdd();</button>
	</div>
	<br>
	<div class="row">
		<draggable :list="todos" :options="{handle:'._handle', animation:150}">
			<div class="col-xs-3 todos-each" v-for="todo in todos">
				<div class="panel panel-default">
					<div class="panel-heading _handle">
						<div class="pull-right">
							<a href="javascript:;" class="fa fa-fw fa-remove" @click="_remove(todos, todo, {ev:$event, closest:'.todos-each'});"></a>
						</div>
						<strong>{{ todo.title }}</strong>
					</div>
					<div class="panel-body">
						Basic panel example
					</div>
				</div>
			</div>
		</draggable>

		<div class="col-xs-12 jumbotron text-center" v-if="!todos.length">
			<small class="text-muted">Nenhum ítem</small>
		</div>
	</div>
	<pre>{{ $data }}</pre>
</div>

<script>
function vueInit(scope) {
	scope.data.todos = [];

	scope.methods._todoAdd = function(todo) {
		this._add(this.todos, this._todo());
	};

	scope.methods._todo = function(todo) {
		return this._default(todo, {
			title: "#{$_id}",
			description: "",
			status: "ok",
		});
	};
	return scope;
}
</script>
<?php });




Tab::add('Slick', function() { ?>
<div data-slick="{slidesToShow:1, arrows:true, asNavFor:'.slick-nav'}" class="slick slick-big">
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?0);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?1);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?2);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?3);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?4);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?5);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?6);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?7);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?8);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?9);"></div></div>
</div>
<div data-slick="{slidesToShow:6, dots:true, asNavFor:'.slick-big'}" class="slick slick-nav">
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?0);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?1);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?2);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?3);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?4);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?5);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?6);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?7);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?8);"></div></div>
	<div><div class="cover" style="background-image:url(https://picsum.photos/600/400?9);"></div></div>
</div>
<style>
.slick {background:#eee;}
.slick-big .cover {width:100%; height:400px;}
.slick-nav .cover {width:100%; height:100px; border:solid 5px #fff;}
</style>
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