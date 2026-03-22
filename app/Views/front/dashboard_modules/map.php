<?php if($is_backoffice && $user["is_admin"]): ?>
	<input type="checkbox" id="enable_map_edit">
	<label for="enable_map_edit">
		<i></i>
		<span>Edit mode</span>
	</label>
<?php endif ?>

<div id="map" class="device-map"></div>