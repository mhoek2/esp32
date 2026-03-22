<?php
if (! function_exists('deviceActionsJS')) {
	function deviceActionsJS(){
	?>
		<script {csp-script-nonce}>
			$('.device[data-device-id]').each(function()
			{
				const device_id = $(this).data('device-id');
	
				$(this).find('#locate').click(function()
				{
					if ( window.FP && typeof window.FP.locateDevice === 'function' ) {
						window.FP.locateDevice( device_id );
					}
					else {
						console.log('locateDevice function not found in FP');
					}
				});
			});
		</script>
	<?php
	}
}

if (! function_exists('deviceUpdateHandlerJS')) {
    function deviceUpdateHandlerJS() 
	{
		//
		// Javascript below, perhaps load externally for more clarity?
		//
	?>
		<script {csp-script-nonce}>
			function set_device_protocol_27_stats( element, data )
			{
				if ( 'state' in data ) {
					const state = parseInt(data['state']);
					$(element).find('[data-protocol-state]').prop("checked", state );

					const states = ["Closed", "Open"];
					$(element).find('[data-state-text]').attr( 'data-state-text', states[state] );
				}
			}

			function set_device_stats( device )
			{
				if ( typeof device['protocol'] === 'undefined') {
					console.log('missing device protocol');
					return;
				}

				if ( typeof device['data'] === 'undefined') {
					console.log('missing device data');
					return;
				}

				const protocol = parseInt(device['protocol']);
				const data = device['data'];

				$('[data-device-id="'+ device['id'] +'"]').each(function( idx )
				{
					// heartbeat
					const sleep = !!parseInt(device['sleep']);
					$(this).find('#heartbeat').removeClass().addClass( sleep ? 'sleep' : 'alive');

					switch( protocol )
					{
						case 27: set_device_protocol_27_stats( $(this), data ); break;
						default: {
							console.log('unknown protocol: ' + protocol);
						}
					}
				});
			}
			
			function get_device_stats(  )
			{
				$.ajax({
					url: '<?=base_url(route_to('get_device_stats'))?>',
					type: 'GET',
					data: {
						<?=setCSRFPostData()?>
					},
					success: function(response) {
						//updateCSRFMeta(response);

						// dispatch
						if ( typeof response.status !== 'undefined' && response.status ) {
							if ( typeof response.devices !== 'undefined') {

								for( var i = 0; i < response.devices.length; i++ )
								{
									set_device_stats( response.devices[i] );
								}
							}
						}
					}
				});

				// update every 5 secs
				setTimeout(function()
				{
					get_device_stats();
				}, 5000 );
			}

			get_device_stats();
		</script>
	<?php
	}
}