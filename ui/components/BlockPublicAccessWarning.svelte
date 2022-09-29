<script>
	import {bapa, storage_provider, delivery_provider} from "../js/stores";
	import Notification from "./Notification.svelte";
</script>

{#if $bapa && !$delivery_provider.block_public_access_supported}
	<Notification warning heading={$storage_provider.block_public_access_warning.heading}>
		<p>{@html $storage_provider.block_public_access_warning.message}</p>
	</Notification>
{/if}
