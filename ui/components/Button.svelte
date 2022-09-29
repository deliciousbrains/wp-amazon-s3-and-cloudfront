<script>
	const classes = $$props.class ? $$props.class : "";

	// Button sizes, medium is the default.
	export let extraSmall = false;
	export let small = false;
	export let large = false;
	export let medium = !extraSmall && !small && !large;

	// Button styles, outline is the default.
	export let primary = false;
	export let outline = !primary;

	// Is the button disabled? Defaults to false.
	export let disabled = false;

	// A button can have a title, most useful to give a reason when disabled.
	export let title = "";
</script>

<button
	on:click
	class:btn-xs={extraSmall}
	class:btn-sm={small}
	class:btn-md={medium}
	class:btn-lg={large}
	class:btn-primary={primary}
	class:btn-outline={outline}
	class:btn-disabled={disabled}
	class={classes}
	{title}
	{disabled}
>
	<slot/>
</button>
