<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.min.js"></script>
<script src="../vuel.js"></script>

<template id="wpska-test">
	<div>
		<input type="text" class="form-control" v-model="hello">
		Hello {{ hello }}
		<div style="border:solid 1px red;"><content></content></div>
		<pre>template: {{ $data }}</pre>
	</div>
</template>

<script>
Vuel("wpska-test", {
	data: {
		name: "",
		value: "",
		hello:"World",
	},
});
</script>