<input type="checkbox" data-protocol-state <?=$device['data']['state'] ? 'checked="checked"' : '';?>">
			
<?php $states = ["Closed", "Open"]; ?>
<div id="state" data-state-text="-"></div>