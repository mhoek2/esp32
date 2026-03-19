<?php if($is_backoffice && $user["is_admin"]): ?>
	<input type="checkbox" id="enable_map_edit">
	<label for="enable_map_edit">
		<i></i>
		<span>Edit mode</span>
	</label>
<?php endif ?>

<div id="map" class="device-map"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script {csp-script-nonce}>
	FP.init({ 
		id						: 'map',
		floorplan				: '/assets/map/floorplan.png',
		bounds					: [[0,0], [9933,12992]],
		devices 				: <?=json_encode($devices)?>,
		device_groups 			: <?=json_encode($device_groups)?>,
		device_update_map_url 	: "<?=($is_backoffice && $user["is_admin"]) ? base_url(route_to('admin.device.update_map')) : ''?>",
		is_admin				: <?=($is_backoffice && $user["is_admin"]) ? "true" : "false"?>
	});
	FP.map().setZoom(-4);
</script>