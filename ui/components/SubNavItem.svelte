<script>
	import {link, location} from "svelte-spa-router";

	export let page;

	let focus = false;
	let hover = false;
</script>

<li class="subnav-item" class:active={$location === page.route} class:focus class:hover>
	<a
		href={page.route}
		title={page.title()}
		use:link
		on:focusin={() => focus = true}
		on:focusout={() => focus = false}
		on:mouseenter={() => hover = true}
		on:mouseleave={() => hover = false}
	>
		{page.title()}
	</a>
</li>
