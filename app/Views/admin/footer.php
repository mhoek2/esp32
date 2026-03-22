
<?php if( isset($live_url) && $live_url ) { ?>
	<a href="<?=$live_url?>" class="live-page">
		<i class="fa-regular fa-eye"></i>
	</a>
<?php } ?>

<!--
<footer>
    <div class="environment">

        <p>Page rendered in {elapsed_time} seconds using {memory_usage} MB of memory.</p>

        <p>Environment: <?= ENVIRONMENT ?></p>

    </div>
</footer>
-->
<p>Environment: <?= ENVIRONMENT ?></p>
				
<script {csp-script-nonce}>
    document.getElementById('menuToggle').addEventListener('click', toggleMenu);
    function toggleMenu() {
        const menuItems = document.getElementsByClassName('menu-item');
        for (let i = 0; i < menuItems.length; i++) {
            const menuItem = menuItems[i];
            menuItem.classList.toggle('hidden');
        }
    }
	
	document.addEventListener('scroll', function (event) {
		if (window.scrollY > 0)
		{
			$('header').addClass('collapse');	
		}
		else {
			$('header').removeClass('collapse');	
		}
	}, true );
	
	// button state handling
	const BUTTON_SAVE 			= "save";
	const BUTTON_TASH 			= "trash";
	const BUTTON_LOADING 		= "loading";
	const BUTTON_SUCCESS 		= "success";
	const BUTTON_ERROR 			= "error";
	const BUTTON_PLACEHOLDER 	= "placeholder";
	const BUTTON_PLACEHOLDER_AC = "placeholder_active";
	const BUTTON_STATES 		= [BUTTON_SAVE, BUTTON_TASH, BUTTON_LOADING, BUTTON_SUCCESS, BUTTON_ERROR, BUTTON_PLACEHOLDER, BUTTON_PLACEHOLDER_AC];

	function button_handler( button, type )
	{
		for ( const state of BUTTON_STATES )
		{
			if ( state === type ) {
				$(button).addClass( state); 
				continue;
			}
			
			$(button).removeClass( state );
		}
	}
</script>

<!-- floorplan map init -->
<?= $FP_footer ?>

<!-- device update handler -->
<?= deviceUpdateHandlerJS() ?>

<!-- device action handler -->
<?= deviceActionsJS() ?>

</body>
</html>