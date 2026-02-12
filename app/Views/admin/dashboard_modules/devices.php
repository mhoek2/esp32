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
		[data-device-protocol="27"] #state > span {
			display: block;
			font-weight: bold;
			line-height: 50px;
			width:100%;
			text-align: center;
			font-size:0.7em;
		}
</style>
<div class="devices grid">
<?php foreach( $devices as $idx => $device ){ ?>
	<div class="device" data-device-protocol="<?=$device['protocol']?>" data-device-id="<?=$device['id']?>">
		<div class="title">
			<span><?=$device['name']?></span>
			<span>MAC: <?=$device['mac']?></span>
		</div>
		
		<?php if( (int)$device['protocol'] === 27 ){ ?>
			<input type="checkbox" data-protocol-state <?=$device['data']['state'] ? 'checked="checked"' : '';?>">
			
			<?php $states = ["Closed", "Open"]; ?>
			<div id="state" data-state-text="<?=$states[$device['data']['state']];?>"></div>
		<?php } ?>
	</div>
<?php } ?>
</div>