<template id="wpska-test">
	{{ attr.name }}
	<pre>{{ $data }}</pre>
</template>

<script>
Vuel("wpska-test", {
	data: {
		test: true,
		attr: {
			name: null,
		},
	},
});
</script>

<style>
wpska-test {display:block; border:solid 5px green;}
</style>