<style>
	.devices {
		
	}
	.devices.grid {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 16px;
	}
		.devices .device {
			grid-column: span 1;
			word-break: break-all;
			background: #e7e7e7;
			border-radius: 5px;
			border: 0px solid #292929;
			padding: 0 0 1em 0;
			overflow: hidden;
		}
			.devices .device .title {
				background: #292929;
				margin-bottom: 1em;
				color: #fff;
				flex: 1;
				display: flex;
				flex-direction: row;
			}
				.devices .device .title #heartbeat 
				{
					width:50px;
					position:relative;
				}
					.devices .device .title #heartbeat::after
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
					.devices .device .title #heartbeat.alive::after
					{
						content: "\f21e";
						color: #f97d7d
					}
					.devices .device .title #heartbeat.sleep::after
					{
						content: "\f186";
						color: #fca523;
					}

				.devices .device .title .info 
				{
					width: 100%;
					padding-left: 1em;
				}
				.devices .device .title .info span:nth-child(1){
					display: block;
					font-weight: bold;
					font-size: 0.8em;
					padding: 1em 0 0 0;
					flex
				}
				.devices .device .title .info span:nth-child(2){
					display: block;

					font-size: 0.6em;
					padding: 0em 0 1em 0;
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

	@media only screen and (min-width: 1024px) {
		.devices.grid {
			grid-template-columns: repeat(6, 1fr);
		}
	}
</style>

<div class="devices grid">
<?php foreach( $devices as $idx => $device ) { ?>

	<div class="device" data-device-protocol="<?=$device['protocol']?>" data-device-id="<?=$device['id']?>">
		<div class="title">
			<div class="info">
				<span><?=$device['name']?></span>
				<span><?=$is_backoffice ? $device['mac'] : ""?></span>
			</div>
			<div id="heartbeat"></div>
		</div>

		<?php
			try {
				if ( empty($device['view']) )
					throw new Exception("view is missing");

				echo $device['view'];
			}
			catch( Exception $e ) {
				echo $e->getMessage();
			}
		?>
	</div>
<?php } ?>
</div>