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
			background: #fff;
			border-radius: 5px;
			border: 1px solid #dadada;
			padding: 0 0 1em 0;
			overflow: hidden;
		}
			.devices .device .title {
				background:#f1f1f1;
				margin-bottom: 1em;
			}
				.devices .device .title span:nth-child(1){
					display: block;
					text-align: center;
					font-weight: bold;
					font-size: 0.8em;
					padding: 1em 0 0 0;
				}
				.devices .device .title span:nth-child(2){
					display: block;
					text-align: center;
					font-size: 0.6em;
					padding: 0em 0 1em 0;
				}
	
	/* protocols */
	[data-device-protocol="27"] #state {
		width: 50px;
		height: 50px;
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
<?php foreach( $devices as $idx => $device ){ ?>
	<div class="device" data-device-protocol="<?=$device['protocol']?>" data-device-id="<?=$device['id']?>">
		<div class="title">
			<span><?=$device['name']?></span>
			<span></span>
		</div>
		
		<?php if( (int)$device['protocol'] === 27 ){ ?>
			<input type="checkbox" data-protocol-state <?=$device['data']['state'] ? 'checked="checked"' : '';?>">
			
			<?php $states = ["Closed", "Open"]; ?>
			<div id="state" data-state-text="<?=$states[$device['data']['state']];?>"></div>
		<?php } ?>
	</div>
<?php } ?>
</div>