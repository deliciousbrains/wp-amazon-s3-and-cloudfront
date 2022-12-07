<script>
	import {getContext, hasContext, onMount, setContext} from "svelte";
	import {is_plugin_setup, settingsLocked, strings} from "../js/stores";
	import Page from "./Page.svelte";
	import Notifications from "./Notifications.svelte";
	import BlockPublicAccessWarning from "./BlockPublicAccessWarning.svelte";
	import ObjectOwnershipEnforcedWarning
		from "./ObjectOwnershipEnforcedWarning.svelte";
	import SubNav from "./SubNav.svelte";
	import SubPages from "./SubPages.svelte";
	import MediaSettings from "./MediaSettings.svelte";
	import UrlPreview from "./UrlPreview.svelte";
	import Footer from "./Footer.svelte";

	export let name = "media";
	export let params = {}; // Required for regex routes.
	const _params = params; // Stops compiler warning for params;

	let sidebar = null;
	let render = false;

	if ( hasContext( 'sidebar' ) ) {
		sidebar = getContext( 'sidebar' );
	}

	// Let all child components know if settings are currently locked.
	setContext( "settingsLocked", settingsLocked );

	// We have a weird subnav here as both routes could be shown at same time.
	// So they are grouped, and CSS decides which is shown when width stops both from being shown.
	// The active route will determine the SubPage that is given the active class.
	const routes = {
		'*': MediaSettings,
	}

	const items = [
		{ route: "/", title: () => $strings.storage_settings_title },
		{
			route: "/media/delivery",
			title: () => $strings.delivery_settings_title
		}
	];

	onMount( () => {
		if ( $is_plugin_setup ) {
			render = true;
		}
	} )
</script>

<Page {name} on:routeEvent>
	{#if render}
		<Notifications tab={name}/>
		<div id="provider-warning-notifications" class="notifications wrapper">
			<BlockPublicAccessWarning/>
			<ObjectOwnershipEnforcedWarning/>
		</div>
		<SubNav {name} {items} subpage/>
		<SubPages {name} {routes}/>
		<UrlPreview/>
	{/if}
</Page>

{#if sidebar && render}
	<svelte:component this={sidebar}/>
{/if}

<Footer on:routeEvent/>
