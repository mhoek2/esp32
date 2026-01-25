<style>
.hw_info > div {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
	width: 100%;
	border-bottom: 1px solid #edebeb;
    padding: 10px 0 15px 0;
}

	.hw_info > div > span:nth-child(1) {
		display: flex;
		flex-direction: column;
		flex-basis: 100%;
		flex: 1;
		display: inline-block;
		font-weight: bold;
	}

	.hw_info > div > span:nth-child(2) {
		display: flex;
		flex-grow: initial;
		text-decoration: none;
		color: #000;
	}
</style>

<div class="hw_info">
	<div>
		<span>Platform</span>
		<span id="os_version"></span>
	</div>
	<div>
		<span>Hardware</span>
		<span id="hw_version"></span>
	</div>
	<div>
		<span>CPU Cores</span>
		<span id="cpu_cores"></span>
	</div>
	<div>
		<span>CPU Temp</span>
		<span id="cpu_temp"></span>
	</div>
	<div>
		<span>CPU Load</span>
		<span id="cpu_load"></span>
	</div>
	<div>
		<span>RAM Usage</span>
		<span id="memory"></span>
	</div>
</div>

<script {csp-script-nonce}>
    $(document).ready(function () {

		<?=updateCSRFMeta() // csrf helper ?>
		
		function set_hw_info( data )
		{
			if ( typeof data.os_version !== 'undefined') {
				$("#os_version").html( data.os_version )
			}
			if ( typeof data.hw_version !== 'undefined') {
				$("#hw_version").html( data.hw_version )
			}
			if ( typeof data.cpu_temp !== 'undefined') {
				$("#cpu_temp").html( data.cpu_temp )
			}
			if ( typeof data.cpu_cores !== 'undefined') {
				$("#cpu_cores").html( data.cpu_cores )
			}
			if ( typeof data.cpu_load !== 'undefined') {
				$("#cpu_load").html( data.cpu_load )
			}
			if ( typeof data.memory !== 'undefined') {
				$("#memory").html( data.memory )
			}
		}

		function get_hw_info(  )
		{
			$.ajax({
				url: '<?=base_url(route_to('hw_info'))?>',
				type: 'GET',
				data: {
					<?=setCSRFPostData()?>
				},
				success: function(response) {
					updateCSRFMeta(response);
					
					if ( typeof response.data !== 'undefined') {
						set_hw_info( response.data );
					}
				}
			});
		}
		
		get_hw_info();
	});
</script>