<script>
	import {notifications} from "../js/stores";
	import Notification from "./Notification.svelte";

	export let component = Notification;
	export let tab = "";
	export let tabParent = "";
</script>

{#if $notifications.length}
	<div id="notifications" class="notifications wrapper">
		{#each $notifications as notification (notification.render_key)}
			{#if !notification.dismissed && (notification.only_show_on_tab === tab || notification.only_show_on_tab === tabParent || !notification.only_show_on_tab)}
				<svelte:component this={component} notification={notification}>
					{#if notification.message}
						<p>{@html notification.message}</p>
					{/if}
				</svelte:component>
			{/if}
		{/each}
	</div>
{/if}
