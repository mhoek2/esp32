<style>
	.state {
		width: 150px;
		height: 150px;
	}
		.state.window_state_0 {
			background: red;
		}
		.state.window_state_1 {
			background: green;
		}
</style>
<?php foreach( $devices as $idx => $device ){ ?>
	<div class="device" data-mac-address="<?=$device['mac']?>">
		<?=$device['mac']?>
		<div class="state window_state_<?=$device['data']['state']?>"></div>
	</div>
<?php } ?>