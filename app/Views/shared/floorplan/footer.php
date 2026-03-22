<?php if ( !empty($FP) && $FP['enabled'] ) { ?>
	<script {csp-script-nonce}>
        (function() {
		const _map = $('#map');
            if ( !_map.length ){
                // return for now,
                // substitute this for a overlay/modal view later
                console.log('Floorplan overlay/modal is not available');
                return;

                //$('body').append('<div id=\'map\' class=\'device-map modal\'></div>');
            }

            <?php $FP_config = &$FP['config']; ?>
            FP.init({ 
                id						: '<?= $FP_config['id'] ?>',
                floorplan				: '<?= $FP_config['floorplan'] ?>',
                bounds					: <?= $FP_config['bounds'] ?>,
                devices 				: <?= $FP_config['devices'] ?>,
                device_groups 			: <?= $FP_config['device_groups'] ?>,
                device_update_map_url 	: '<?= $FP_config['device_update_map_url'] ?>',
                is_admin				: <?= $FP_config['is_editable'] ? "true" : "false" ?>
            });
            FP.map().setZoom(-4);
        })();
	</script>
<?php } ?>