<?php echo $header; ?>

<?php
$action = base_url(route_to('admin.device.update', $device['id']));
$action_button = 'Opslaan';
?>

<style>
	.container {
		display:flex;
		flex-direction: row;
		gap:20px;
	}
		.container .block {
			background-color: #fff;
			border-radius: var(--secondary-border-radius);
			padding: 10px 15px;
			width: 100%;
			max-width:450px;
			height: max-content;
		}
		.container > section:nth-child(2) {
			flex:2
		}
</style>

<div class="breadcrumbs">
   	<ul>
		<li><a href="<?=base_url(route_to('admin.devices'))?>">Devices</a></li>
		<li><span><?=$device['name']?></span></li>
    </ul>
</div>

<section class="main">
    <div class="content">

		<form action="<?=$action?>" method="post" id="device_form">
			<div class="container">
				<div class="form-group">
					<label for="meta_name">Name</label>
					<input type="text" id="name" name="name" class="form-control" value="<?= old('name') ?? $device['name'] ?>">
				</div>
			</div>

			<?= csrf_field() ?>

			<div class="actions">
				<button type="submit" class="button-primary button-action save">
					<div class="icon"></div>
					<div class="text"><?=$action_button?></div>
				</button>
			</div>

			<div id="form_response_container" class="request-response"></div>
		</form>
    </div>
</section>

<script {csp-script-nonce}>
    $(document).ready(function () {

		<?=updateCSRFMeta() // csrf helper ?>

		$('#device_form').submit(function (event) {
			event.preventDefault();
			
			const formData = $(this).serialize();
			
			button_handler( event.originalEvent.submitter, BUTTON_LOADING );
			$('#form_response_container').empty();
			
			$.ajax({
				url: '<?= $action ?>',
				type: 'POST',
				data: formData,
				success: function(response) {
					updateCSRFMeta(response);

					// Handle the response from the server
					//$('#responseMessage').html('<p>' + response.message + '</p>');
					if (response.status === 'success') {
						$('#form_response_container').append('<p class="success">' + response.message + '</p>');
						
						button_handler( event.originalEvent.submitter, BUTTON_SUCCESS );
						
						if ( response.redirect != null ) {
							window.location.href = response.redirect;
						}

						setTimeout(function(){
							$('#form_response_container').html("");
						}, 1250);
						return;
					}

					if (response.status === 'error' && response.errors) {
						$.each(response.errors, function(field, errorMessage) {
							$('#form_response_container').append('<p class="error">' + errorMessage + '</p>');
						});

						button_handler( event.originalEvent.submitter, BUTTON_ERROR );
					}
				},
				error: function(xhr, status, error) {
					// Handle any error
					$('#form_response_container').append('<p class="error">An error occurred while submitting the form.</p>');

					button_handler( event.originalEvent.submitter, BUTTON_ERROR );
				}
			});
		});
    });
</script>

<?php echo $footer; ?>