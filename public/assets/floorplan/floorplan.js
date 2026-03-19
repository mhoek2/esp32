	window.FP = (function() {
		let map;
		let devices = {};
		let device_groups = {};

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
        let is_admin = false;
		let device_update_map_url = '';
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
						url: device_update_map_url,
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
					<div class="device tooltip closed" data-device-protocol="${device.protocol}" data-device-id="${device.id}">
						<div class="title">
							<div id="heartbeat" class="alive"></div>
							<span>${device.name}</span>
							<label for="ddt_${device.id}" class="map-device-details-dropdown-label">
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
			getSensorIcon( device_id, protocol ) 
			{
				return L.divIcon({
					className: `device-marker-container`,
					iconSize: [20, 20],
					iconAnchor: [10, 50],
					html: `
					<div class="device" data-device-protocol="${protocol}" data-device-id="${device_id}">
						<input type="checkbox" data-protocol-state>
						<div class="marker"></div>
					</div>`
				});
			},
			addDeviceToMap()
			{
				devices.forEach(device => {
					const marker = L.marker(
						[device.map_y * size_y, device.map_x * size_x],
						{ 
							pane:'devices',
							draggable: edit_enabled,
							icon: window.FP.getSensorIcon( device.id, device.protocol )
						}
					);

					const tooltip = L.tooltip({
						pane: 'devices',
						permanent	: true,
						direction: 'top',
						offset: [0, -45],
						interactive: true
					}).setContent(window.FP.renderDeviceLabel(device));

					marker.bindTooltip(tooltip);

					// toggle tooltip on marker click
					marker.on('click', function () {
						if ( !marker.tooltip_open )
							$(`#map .device.tooltip[data-device-id='${device.id}']`).removeClass('closed').addClass('open');
						else
							$(`#map .device.tooltip[data-device-id='${device.id}']`).removeClass('open').addClass('closed');

						marker.tooltip_open = !marker.tooltip_open;
						
						const tooltip = marker.getTooltip();

						if ( tooltip && tooltip._map )
							tooltip._updatePosition();
					});

					
					marker.on('dragend', function(e) {
                        if ( !is_admin )
                            return;

						const pos = e.target.getLatLng();

						window.FP.updateDevicePosition(
							device.id,
							pos.lng / size_x, 
							pos.lat / size_y
						);
					});


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
					marker.tooltip = tooltip;
					marker.tooltip_open = false;
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
			init( config ) 
			{
    		    devices = config.devices;
		        device_groups = config.device_groups;
                device_update_map_url = config.device_update_map_url;
                is_admin = config.is_admin;

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
				
                // admin features
                $('#enable_map_edit').change(function() {
                    edit_enabled = $(this).is(':checked');

                    window.FP.updateMarkerDragging();
                });
	
				//console.log(map._layers);
				// map.on('click', function(e) {
				//  console.log("X:", e.latlng.lng / size_x );
				// 	console.log("Y:", e.latlng.lat / size_y );

				// 	L.marker(e.latlng).addTo(map);
				// });
			},
			map() 
			{
				return map;
			}
		};
	})();