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
	.protocol_27 .state {
		width: 50px;
		height: 50px;
    	border-radius: 5px;
		margin: 0 auto;
	}
		.protocol_27 .state.window_state_0 {
			background: #afffd3;
			border: 1px solid #7ed3a8;
		}
		.protocol_27 .state.window_state_1 {
			background: #ffafaf;
			border: 1px solid #d37e7e;
		}
		.protocol_27 .state > span {
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
	<div class="device protocol_<?=$device['protocol']?>" data-mac-address="<?=$device['mac']?>">
		<div class="title">
			<span><?=$device['name']?></span>
			<span>MAC: <?=$device['mac']?></span>
		</div>
		
		<?php if( (int)$device['protocol'] === 27 ){ ?>
			<div class="state window_state_<?=$device['data']['state']?>">
				<?php $states = ["Closed", "Open"]; ?>
				<span><?=$states[$device['data']['state']];?></span>
			</div>
		<?php } ?>
	</div>
<?php } ?>
</div>