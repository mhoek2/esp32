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

	.device .title #heartbeat 
	{
		width:50px;
		position:relative;
	}
		.device .title #heartbeat::after
		{
			content: "-";
			font-family: "Font Awesome 6 Free";
			font-weight: 900;
			position: absolute;
			top: 50%;
			left: 50%;
			line-height: inherit;
			text-align: center;
			padding: 5px;
			background: #454545;
			border-radius: 5px;
			transform: translateY(-50%) translateX(-50%);
		}
		.device .title #heartbeat.alive::after
		{
			content: "\f21e";
			color: #f97d7d
		}
		.device .title #heartbeat.sleep::after
		{
			content: "\f186";
			color: #fca523;
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
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<div id="map" class="device-map"></div>


<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script {csp-script-nonce}>
	$(document).ready(function() {
		const devices = <?=json_encode($devices)?>;
		const bounds = [[0,0], [650,1000]];

		function updateDevicePosition( id, map_x, map_y ) 
		{
			console.log("Device:", id);
			console.log("X:", map_x);
			console.log("Y:", map_y);

			$.ajax({
				url: '<?=base_url(route_to('admin.device.update_map'))?>',
				method: 'POST',
				data: {
					device_id	: id,
					map_x		: map_x,
					map_y		: map_y
				},
				success: function (response) {
					console.log('here');
				}
			});
		}

		function renderDeviceLabel( device ) 
		{
			console.log(device);
			return `
				<div class="device" data-device-protocol="${device.protocol}" data-device-id="${device.id}">
					<div class="title">
						<div>${device.name}</div>
						<div id="heartbeat" class="alive"></div>
					</div>
					
					<input type="checkbox" data-protocol-state>
					<div id="state" data-state-text="-"></div>
				</div>
			`;
		}

		function addDeviceToMap( map )
		{
			devices.forEach(device => {

				const marker = L.marker(
					[device.map_y * 1000, device.map_x * 1000],
					{ draggable: true }
				).addTo(map);

				$('')

				marker.bindTooltip( renderDeviceLabel(device), {
					permanent: true,
					direction: "top",
					offset: [0, -10]
				});

				// admin feature!
				marker.on('dragend', function(e) {
					const pos = e.target.getLatLng();

					updateDevicePosition(
						device.id,
						pos.lng / 1000,
						pos.lat / 1000
					);
				});

			});
		}

		function initDeviceMap() 
		{
			var map = L.map('map', {
				crs: L.CRS.Simple
			});
			var image = L.imageOverlay('/assets/floorplan2.webp', bounds).addTo(map);

			map.fitBounds(bounds);

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