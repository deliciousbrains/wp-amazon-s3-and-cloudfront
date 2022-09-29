<script>
	import {ooe, storage_provider, delivery_provider} from "../js/stores";
	import Notification from "./Notification.svelte";
</script>

{#if $ooe && !$delivery_provider.object_ownership_supported}
	<Notification warning heading={$storage_provider.object_ownership_enforced_warning.heading}>
		<p>{@html $storage_provider.object_ownership_enforced_warning.message}</p>
	</Notification>
{/if}
