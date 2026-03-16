<style>

	.device-map {
		width: 100%;
    	height: 768px;
	}

	.leaflet-tooltip {
		background: rgba(0, 0, 0, 0.7);
		color: white;
		font-size: 12px;
		padding: 3px 6px;
		border-radius: 4px;
	}
	.leaflet-tooltip #heartbeat {

	}
	#map {
		--map-device-titlebar-height: 2em;
	}
	#map .device .title {
		display: flex;
		flex-direction: row;
		align-items: center;
		gap: 1em;
		line-height: var(--map-device-titlebar-height);
	}
	#map .device .title span {
		flex: 2;
		font-weight: bold;
	}

	#map .device .title label {
		cursor: pointer;
		width: var(--map-device-titlebar-height);
		text-align: center;
		border-radius: 5px;
		margin: 0;
	}
	#map .device .title label:hover {
		background: rgba(236, 236, 236, 0.8);
	}

	#map .device .title #heartbeat 
	{
		position:relative;
		width: var(--map-device-titlebar-height);
		line-height: var(--map-device-titlebar-height);
	}
		#map .device .title #heartbeat::after
		{
			content: "-";
			font-family: "Font Awesome 6 Free";
			font-weight: 900;
			position: absolute;
			top: 50%;
			left: 50%;
			line-height: var(--map-device-titlebar-height);
			width: 100%;
			text-align: center;
			background: #454545;
			border-radius: 5px;
			transform: translateY(-50%) translateX(-50%);
		}
		#map .device .title #heartbeat.alive::after
		{
			content: "\f21e";
			color: #f97d7d
		}
		#map .device .title #heartbeat.sleep::after
		{
			content: "\f186";
			color: #fca523;
		}

		#map .map-device-details-dropdown-toggle {
			display: none;
		}
		#map .map-device-details-dropdown {
			display: none;
		}
		#map .map-device-details-dropdown-toggle:checked + .map-device-details-dropdown {
			display: block;
			margin-top: 1em;
		}

	/* protocols */
	[data-device-protocol="27"] #state {
		width: 150px;
		height: 30px;
    	border-radius: 5px;
		margin: 0 auto;
		position:relative;
	}
		[data-device-protocol="27"] input[data-protocol-state] {
			display: none;
		}
		[data-device-protocol="27"] input[data-protocol-state]:not(:checked) ~ #state {
			background: #afffd3;
			border: 1px solid #7ed3a8;
		}
		[data-device-protocol="27"] input[data-protocol-state]:checked ~ #state {
			background: #ffafaf;
			border: 1px solid #d37e7e;
		}
		[data-device-protocol="27"] #state::after {
			content: attr(data-state-text);
			position: absolute;
			top: 50%;
			transform: translateY(-50%);
			font-weight: bold;
			text-align: center;
			font-size:0.7em;
			width: 100%;
		}
	/* enable map edit */
	#enable_map_edit {
		display: none;
	}
	#enable_map_edit + label {
		width: fit-content;
		padding:0;
		background: rgba(255,255,255,0.8);
		border: 1px solid #292929;
		border-radius: 5px;
		display:flex;
		flex-direction: row;
		cursor:pointer;
	}
		#enable_map_edit + label > span {
			padding: 5px 10px;
		}
		#enable_map_edit + label > i {
			background: #292929;
			width: 20px;
			text-align: center;
			padding: 5px;
		}
		#enable_map_edit + label > i::before {
			content: "\e51f";
			font-family: "Font Awesome 6 Free";
			font-weight: 900;
			font-style: normal;
			font-variant: normal;
			text-rendering: auto;
			color: #fff;
		}
		#enable_map_edit:checked + label > i::before {
			content: "\f3c5";
		}
		#enable_map_edit:checked + label {
			border-color: #4caf50;
		}
		#enable_map_edit:checked + label > i {
			background: #4caf50;
		}
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

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
	$(document).ready(function() {
		const devices = <?=json_encode($devices)?>;
		let markers = [];
		
		// if this map is changed, the bounds must be updated accordingly.
		// the coordinates of devices are based on these bounds.
		const bounds = [[0,0], [9933,12992]];
		const size_x = bounds[1][1] - bounds[0][1];
		const size_y = bounds[1][0] - bounds[0][0]; 

		// wheter editing is enabled or not, the device positions can be updated by dragging the markers. 
		let edit_enabled = false;
	
		<?php if($is_backoffice && $user["is_admin"]): ?>
		function updateMarkerDragging() {
			markers.forEach(marker => {
				if (edit_enabled)
					marker.dragging.enable();
				else
					marker.dragging.disable();
			});
		}
		
		$('#enable_map_edit').change(function() {
			edit_enabled = $(this).is(':checked');

			updateMarkerDragging();
		});
		<?php endif ?>

		function updateDevicePosition( id, map_x, map_y ) 
		{
			$.ajax({
				url: '<?=base_url(route_to('admin.device.update_map'))?>',
				method: 'POST',
				data: {
					device_id	: id,
					map_x		: map_x,
					map_y		: map_y
				},
				success: function (response) {
					console.log("Device position updated successfully");
				}
			});
		}

		function renderDeviceLabel( device ) 
		{
			return `
				<div class="device" data-device-protocol="${device.protocol}" data-device-id="${device.id}">
					<div class="title">
						<div id="heartbeat" class="alive"></div>
						<span>${device.name}</span>
						<label for="ddt_${device.id}">
							<i class="fa-solid fa-ellipsis-vertical"></i>
						</label>
					</div>

					<input type="checkbox" id="ddt_${device.id}" class="map-device-details-dropdown-toggle"/>
					<div class="map-device-details-dropdown">
						<input type="checkbox" data-protocol-state>
						<div id="state" data-state-text="-"></div>
					</div>
				</div>
			`;
		}

		function addDeviceToMap( map )
		{
			devices.forEach(device => {
				const marker = L.marker(
					[device.map_y * size_y, device.map_x * size_x],
					{ draggable: edit_enabled }
				).addTo(map);
				markers.push(marker);

				marker.bindTooltip( renderDeviceLabel(device), {
					permanent: true,
					direction: "top",
					offset: [0, -10],
					interactive: true
				});

				<?php if( $is_backoffice && $user["is_admin"] ): ?>
				marker.on('dragend', function(e) {
					const pos = e.target.getLatLng();

					updateDevicePosition(
						device.id,
						pos.lng / size_x, 
						pos.lat / size_y
					);
				});
				<?php endif ?>
			});
		}

		function initDeviceMap() 
		{
			var map = L.map('map', {
				crs		: L.CRS.Simple,
				minZoom	: -5,
				maxZoom	: 0
			});

			var image = L.imageOverlay('/assets/map/floorplan.png', bounds).addTo(map);

			map.fitBounds(bounds);
			map.setZoom( -4);

			addDeviceToMap( map );

			//map.on('click', function(e) {
			//	console.log("X:", e.latlng.lng);
			// 	console.log("Y:", e.latlng.lat);

			//L.marker(e.latlng).addTo(map);
			//});
		}

		initDeviceMap();
	});
</script>