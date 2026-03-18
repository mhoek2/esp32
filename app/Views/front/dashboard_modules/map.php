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
		z-index: 9999;
	}
	.leaflet-tooltip #heartbeat {

	}
	#map {
		--map-device-titlebar-height: 2em;
	}
	#map svg {
		all:unset
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

	<?php if($is_backoffice && $user["is_admin"]): ?>
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
	<?php endif ?>

	/* legend */
	#map .legend {

	}
		#map .legend #legend_item {
			display: flex;
			flex-direction: row;
			align-items: center;
			gap: 0.5em;
			font-size: 14px;
			margin-bottom: 0.5em;
			padding: 0.2em 0.5em;

			background-color: #fff;
			border: 1px solid #fff;
			border-radius: 3px;
			box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
		}
		#map .legend #legend_item .item {
			width: 20px;
			height: 20px;
			border-radius: 3px;
		}
		#map .legend #legend_item .legend-color {
			border-radius: 100%;
		}
		#map .legend #legend_item .legend-name {
			flex: 1;
			font-weight: bold;
		}
		#map .legend #legend_item .legend-navigate {
			text-align: center;
			margin: 0 !important;
			padding: 2px 5px;
			cursor: pointer;
		}
		#map .legend #legend_item .legend-navigate:hover,
		#map .legend #legend_item .legend-toggle label:hover
		{
			background: rgba(0,0,0,0.1);
		}
		#map .legend #legend_item .legend-toggle {
			display: flex;
			flex-direction: row;
			align-items: center;
			gap: 0.2em;
		}
			#map .legend #legend_item .legend-toggle input {
				display:none;
			}
			#map .legend #legend_item .legend-toggle label {
				text-align: center;
				padding: 2px 5px;
				margin: 0 !important;
				cursor:pointer;
				border-radius: 3px;
			}
				#map .legend #legend_item .legend-toggle label::before {
					content: "\f070";
					font-family: "Font Awesome 6 Free";
					font-weight: 900;
					font-style: normal;
					font-variant: normal;
					text-rendering: auto;
					color: #000;
				}

				#map .legend #legend_item .legend-toggle input:checked + label::before {
					content: "\f06e";
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
	// floorplan module
	window.FP = (function() {
		let map;
		const devices = <?=json_encode($devices)?>;
		const device_groups = <?=json_encode($device_groups)?>;

		//console.log("Devices:", devices);
		//console.log("Device groups:", device_groups);

		const markers = [];
		const marker_groups = {};
		let highlight = null;

		// if this map is changed, the bounds must be updated accordingly.
		// the coordinates of devices are based on these bounds.
		let bounds = [[0,0], [9933,12992]];
		let size_x = bounds[1][1] - bounds[0][1];
		let size_y = bounds[1][0] - bounds[0][0]; 

		// wheter editing is enabled or not (only for admins in backoffice)
		let edit_enabled = false;

		return {
			configureBounds( new_bounds )
			{
				bounds = new_bounds;
				size_x = bounds[1][1] - bounds[0][1];
				size_y = bounds[1][0] - bounds[0][0]; 
			},
			findDeviceGroupById( id ) 
			{
				return device_groups.find(group => group.id == id);
			},
			findDeviceById( id ) 
			{
				return devices.find(device => device.id == id);
			},
			findMarkerById( id ) 
			{
				return markers[id] ? markers[id] : null;
			},
			locateDevice( device_id )
			{
				let device = window.FP.findDeviceById( device_id );
				let marker = window.FP.findMarkerById( device_id );

				if ( !device || !marker ) {
					console.log("Device or Marker not found:", device_id);
					return;
				}
	
				map.panTo( marker.getLatLng() );

				// highlight the marker
				console.log(highlight);
				if ( highlight == null ) {
					highlight = L.circle( marker.getLatLng(), {
						radius		: 150, 
						color		: 'blue',
						weight		: 2,
						fillOpacity	: 0.2
					}).addTo(map);
				}
				else
				{
					highlight.setLatLng( marker.getLatLng() );
				}
			},
	
				// admin features
				updateMarkerDragging() 
				{
					markers.forEach(marker => {
						if (edit_enabled)
							marker.dragging.enable();
						else
							marker.dragging.disable();
					});
				},
				updateGroupRectangles() 
				{
					Object.entries(marker_groups).forEach(([group_id, marker_group]) => {
						const aabb = marker_group.getBounds();
						marker_group.rectangle.setBounds(aabb.pad(0.1));
					});
				},
				updateDevicePosition( id, map_x, map_y ) 
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
							window.FP.updateGroupRectangles();

							console.log("Device position updated successfully");
						}
					});
				},
			
			renderDeviceLabel( device ) 
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
			},
			addDeviceToMap( )
			{
				devices.forEach(device => {
					const marker = L.marker(
						[device.map_y * size_y, device.map_x * size_x],
						{ 
							pane:'devices',
							draggable: edit_enabled 
						}
					);

					marker.bindTooltip( window.FP.renderDeviceLabel(device), {
						pane		:'devices',
						permanent	: true,
						direction	: "top",
						offset		: [0, -10],
						interactive	: true
					});

					<?php if( $is_backoffice && $user["is_admin"] ): ?>
					marker.on('dragend', function(e) {
						const pos = e.target.getLatLng();

						window.FP.updateDevicePosition(
							device.id,
							pos.lng / size_x, 
							pos.lat / size_y
						);
					});
					<?php endif ?>

					// group doesn't exist, add to map without group
					if (device.group_id < 0 || !window.FP.findDeviceGroupById(device.group_id)) {
						marker.addTo(map);
						markers.push(marker);
						return;
					}

					// create group if it doesn't exist and add the marker to the group
					if (!marker_groups[device.group_id]) {
						marker_groups[device.group_id] = L.featureGroup().addTo(map);
						marker_groups[device.group_id].meta = window.FP.findDeviceGroupById(device.group_id);
					}
					marker.addTo( marker_groups[device.group_id] );

					// add to flat list for easy access
					marker.device_group_id = device.group_id;
					marker.device_id = device.id;
					markers[device.id] = marker;
				});
			},
			renderDeviceGroups( )
			{
				Object.entries(marker_groups).forEach(([group_id, marker_group]) => {
					const aabb = marker_group.getBounds();

					const rectangle = L.rectangle(aabb.pad(0.1), {
						pane		: 'groups',
						color		: marker_group.meta.color,
						weight		: 2,
						fillOpacity	: 0.07,
						lineJoin	: 'round',
					}).addTo(map);

					marker_group.rectangle = rectangle;

					// zoom in on a specific group
					// map.fitBounds(aabb);
				});
			},
			renderLegendDeviceGroup( )
			{
				// render legend for device groups
				Object.entries(marker_groups).forEach(([group_id, marker_group]) => {
					const legendItem = `
						<div id="legend_item" data-group-id="${group_id}">
							<span class="legend-color item" style="background:${marker_group.meta.color}"></span>
							<span class="legend-name">${marker_group.meta.name}</span>
							<span class="legend-navigate item">
								<i class="fa-solid fa-location-crosshairs"></i>
							</span>
							<span class="legend-toggle item">
								<input type="checkbox" id="legend_toggle_${group_id}" checked/>
								<label for="legend_toggle_${group_id}"></label>
							</span>
						</div>
					`;

					$('#map .legend').append(legendItem);
				});

				$('#map #legend_item .legend-navigate').on('click', function() {
					const group_id = $(this).closest('#legend_item').data('group-id');
					const marker_group = marker_groups[group_id];

					if (marker_group) {
						const aabb = marker_group.getBounds();
						map.fitBounds(aabb);
					}
				});

				$("#map #legend_item .legend-toggle > input").on('change', function() {
					const group_id = $(this).closest('#legend_item').data('group-id');
					const marker_group = marker_groups[group_id];

					if (marker_group) {
						if ($(this).is(':checked')) {
							marker_group.addTo(map);
							marker_group.rectangle.addTo(map);
						} else {
							map.removeLayer(marker_group);
							map.removeLayer(marker_group.rectangle);
						}
					}
				});
			},
			createLegend( )
			{
				var legend = L.control({position: 'topleft', pane:'legend'});

				legend.onAdd = function(map) {
					return L.DomUtil.create('div', 'legend');
				};

				legend.addTo( map );
				window.FP.renderLegendDeviceGroup();
			},
			init(config) 
			{
				map = L.map( config.id, {
					crs		: L.CRS.Simple,
					minZoom	: -5,
					maxZoom	: 0,
					renderer: L.svg()
				});

				// panes for z-fighting
				map.createPane('floor');
				map.createPane('groups');
				map.createPane('devices');
				map.createPane('legend');
				map.getPane('floor').style.zIndex = 200;
				map.getPane('groups').style.zIndex = 400;
				map.getPane('devices').style.zIndex = 500;
				map.getPane('legend').style.zIndex = 600;

				// set bounds and add floorplan image
				window.FP.configureBounds( config.bounds );
				var image = L.imageOverlay( config.floorplan, bounds, {pane:'floor'} ).addTo(map);
				map.fitBounds( bounds );

				// add devices and groups to map
				window.FP.addDeviceToMap();
				window.FP.renderDeviceGroups();
				window.FP.createLegend();
				
				<?php if($is_backoffice && $user["is_admin"]): ?>
					// admin features
					$('#enable_map_edit').change(function() {
						edit_enabled = $(this).is(':checked');

						window.FP.updateMarkerDragging();
					});
				<?php endif ?>
	
				//console.log(map._layers);
				//map.on('click', function(e) {
				//	console.log("X:", e.latlng.lng);
				// 	console.log("Y:", e.latlng.lat);

				//L.marker(e.latlng).addTo(map);
				//});
			},
			map() 
			{
				return map;
			}
		};
	})();

	FP.init({ 
		id			: 'map',
		floorplan	: '/assets/map/floorplan.png',
		bounds		: [[0,0], [9933,12992]] 
	});
	FP.map().setZoom(-4);
</script>