<?php if( isset($edit_url) && $edit_url ) { ?>
	<a href="<?=$edit_url?>" class="edit-page">
		<i class="fas fa-edit"></i>
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
</script>

</body>
</html>