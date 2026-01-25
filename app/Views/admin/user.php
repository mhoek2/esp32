<?php echo $header; ?>

<?php
$action = '';
$action_button = '';
$breadcrumb_title = '';

if (!empty($selected_user)){
	$action_button = 'Opslaan';
	$action = base_url(route_to('admin.user.update', $selected_user['id']));
	$breadcrumb_title = $selected_user['fullname'];
}
else {
	$action_button = 'Nieuwe gebruiker aanmaken';
	$action = base_url(route_to('admin.user.new'));
	$breadcrumb_title = 'Nieuwe Gebruiker';
}
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

	<?php if(! empty($selected_user) ): ?>
		.edit-user-info {
			display: flex;
			align-items: center;
			padding: 10px 15px;
			margin: 10px 0;
			width: 100%;
		}
			.edit-user-info .profile {
				width: 150px;
				height: 150px;
				border-radius: 50%;
				background-color: var(--header-user-dropdown-button-background);
				color: white;
				display: flex;
				justify-content: center;
				align-items: center;
				font-size: 4em;
				font-weight: bold;
				margin-right: 15px;
			}
			.edit-user-info .meta {
				display: flex;
				flex-direction: column;
			}
				.edit-user-info .meta span {
					color: #333;
					font-size: 14px;
					margin-bottom: 5px;
				}
				.edit-user-info .meta span.name {
					font-weight:600;
				}
				.edit-user-info .meta span.email {
					font-size: 14px;
					color: #555;
				}
				.edit-user-info .meta span.username {
					font-size: 10px;
					color: #e186ab;
				}

		/*
		#change_password_form {
			padding: 1em;
			border: 1px solid rgb(245 218 227);
			border-radius: 3px;
			box-shadow: 0 2px 8px var(--button-alert-background-color);
		}*/
	@media (max-width: 1199px) {
		.container {
			display:block;
		}
	}
	<?php endif ?>
</style>

<div class="breadcrumbs">
   	<ul>
		<li><a href="<?=base_url(route_to('admin.users'))?>">Gebruikers</a></li>
		<li><span><?=$breadcrumb_title?></span></li>
    </ul>
</div>

<section class="main">
    <div class="content">

		<form action="<?=$action?>" method="post" id="user_form">
			<div class="container">
				<section class="block">
					<?php if(empty($selected_user) ): ?>
						<?php if( 1 == 0 ) { // disabled, switched to xhr reqruest ?>
						<div class="form-group">
							<label for="username">Username</label>
							<input type="text" id="username" name="username" class="form-control" value="<?= old('username') ?>">
							<div class="text-danger">
								<?= \Config\Services::validation()->getError('username') ?>
							</div>
						</div>
						<?php } ?>

						<div class="form-group">
							<label for="email">Email</label>
							<input type="email" id="email" name="email" class="form-control" value="<?= old('email') ?>">
						</div>

						<h3>Wachtwoord</h3>

						<!-- Password -->
						<div class="form-group">
							<label for="floatingPasswordInput"><?= lang('Auth.password') ?></label>
							<input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="new-password" placeholder="<?= lang('Auth.password') ?>" required>
						</div>

						<!-- Password (Again) -->
						<div class="form-group">
							<label for="floatingPasswordConfirmInput"><?= lang('Auth.passwordConfirm') ?></label>
							<input type="password" class="form-control" id="floatingPasswordConfirmInput" name="password_confirm" inputmode="text" autocomplete="new-password" placeholder="<?= lang('Auth.passwordConfirm') ?>" required>
						</div>

					<?php else: ?>
						<div class="edit-user-info">
							<div class="profile"><?=$selected_user['shortname']?></div>
							<div class="meta">
								<span class="name"><?=$selected_user['fullname']?></span>
								<span class="email"><?=$selected_user['email']?></span>
								<span class="username"><?=$selected_user['username']?></span>
							</div>

						</div>
					<?php endif ?>
				</section>
				<section>
					<h3>Persoonsgegevens</h3>

					<?php 
					$additional_fields = [
						['field' => 'firstname', 	'name' => "First name"],
						['field' => 'middlename', 	'name' => "Middle name"],
						['field' => 'lastname', 	'name' => "Last name"],
					];
	
					foreach($additional_fields as $item): ?>
						<div class="form-group">
							<label for="name"><?=$item['name']?></label>
							<input type="text" id="<?=$item['field']?>" name="<?=$item['field']?>" class="form-control" value="<?= !empty($selected_user) ? $selected_user[$item['field']] : old($item['field']) ?>">
							<div class="text-danger">
								<?= \Config\Services::validation()->getError($item['field']) ?>
							</div>
						</div>
					<?php endforeach ?>
				</section>
			</div>
			
			<?php if( 1 == 0 ) { // disabled, switched to xhr reqruest ?>
				<div class="request-response">
					<?php if (session('error') !== null) : ?>
							<p class="alert"><?= esc(session('error')) ?></p>
					<?php elseif (session('errors') !== null) : ?>
						<?php if (is_array(session('errors'))) : ?>
							<?php foreach (session('errors') as $error) : ?>
								<p class="alert"><?= esc($error) ?></p>
							<?php endforeach ?>
						<?php else : ?>
							<p class="alert"><?= esc(session('errors')) ?></p>
						<?php endif ?>
					<?php endif ?>
				</div>
			<?php } ?>

			<?= csrf_field() ?>

			<div class="actions">
				<button type="submit" class="button-primary button-action save">
					<div class="icon"></div>
					<div class="text"><?=$action_button?></div>
				</button>
			</div>

			<div id="form_response_container" class="request-response"></div>
		</form>

		<?php if(!empty($selected_user) ): ?>
		<div class="alert-actions">
			<p>Speciale handelingen:</p>

			<a id="change_password" class="button-alert">
				<i class="fa-solid fa-key"></i> Wachtwoord wijzigen
			</a>
			<div style="display:none;">
				<form method="post" id="change_password_form">
					<!-- Password -->
					<div class="form-group">
						<label for="floatingPasswordInput"><?= lang('Auth.password') ?></label>
						<input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="new-password" placeholder="<?= lang('Auth.password') ?>" required>
					</div>

					<!-- Password (Again) -->
					<div class="form-group">
						<label for="floatingPasswordConfirmInput"><?= lang('Auth.passwordConfirm') ?></label>
						<input type="password" class="form-control" id="floatingPasswordConfirmInput" name="password_confirm" inputmode="text" autocomplete="new-password" placeholder="<?= lang('Auth.passwordConfirm') ?>" required>
					</div>

					<?= csrf_field() ?>

					<div class="actions">
						<button type="submit" class="button-alert button-action save">
							<div class="icon"></div>
							<div class="text">Wijzigen</div>
						</button>
					</div>

					<div id="password_response_container" class="request-response"></div>
				</form>
			</div>

			<?php if ($selected_user['id'] !== $user['id']): ?>
				<a id="delete_user" class="button-alert">
					<i class="fa-solid fa-ban"></i> Gebruiker verwijderen
				</a>
			<?php endif ?>
		</div>
		<?php endif ?>
    </div>
</section>

<script {csp-script-nonce}>
    $(document).ready(function () {

		<?=updateCSRFMeta() // csrf helper ?>

		$('#user_form').submit(function (event) {
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
		
		
		<?php if ( !empty($selected_user) ): ?>

			$(document).on('click', '#change_password', function()
			{
				const confirmation = confirm('Weet je zeker dat je het wachtwoord wilt wijzigen?');

				if (confirmation)
				{
					$('#change_password_form').parent().css('display', 'block');
				}
			});

            $('#change_password_form').submit(function (event) {
                event.preventDefault();

                const formData = $(this).serialize();

				button_handler( event.originalEvent.submitter, BUTTON_LOADING );
				$('#password_response_container').empty();
				
                $.ajax({
					url: '<?=base_url(route_to('admin.user.change_password', $selected_user['id']))?>',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
						updateCSRFMeta(response);
						
                       	if (response.status === 'success') {
							$('#password_response_container').append('<p class="success">' + response.message + '</p>');
							$(this).find('.form-group').remove();
							$(this).find('.actions').remove();
							
							button_handler( event.originalEvent.submitter, BUTTON_SUCCESS );
							
							if ( response.redirect != null ) {
								window.location.href = response.redirect;
							}
							return;
						}

						if (response.status === 'error' && response.errors) {
							$.each(response.errors, function(field, errorMessage) {
								$('#password_response_container').append('<p class="error">' + errorMessage + '</p>');
							});
							
							button_handler( event.originalEvent.submitter, BUTTON_ERROR );
						}
                    }.bind(this),
                    error: function(xhr, status, error) {
						button_handler( event.originalEvent.submitter, BUTTON_ERROR );
                        $('#change_password_form').parent().html('<p class="error">Er is iets mis gegaan!</p>');
                    }
                });
            });

			$(document).on('click', '#delete_user', function()
			{
				const confirmation = confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?');
				
				if (confirmation) {
					//
					// Use a virtual form for csrf tokenization and redirect back functionalities
					//

					const form = document.createElement('form');
					form.method = 'POST';
					form.action = '<?= base_url(route_to('admin.user.delete', $selected_user['id'])) ?>';

					const csrfFieldToken = document.createElement('input');
					csrfFieldToken.type = 'hidden';
					csrfFieldToken.name = '<?= csrf_token() ?>';
					csrfFieldToken.value = '<?= csrf_hash() ?>';

					form.appendChild(csrfFieldToken);
					document.body.appendChild(form);
					form.submit();
				}
			});
		<?php endif ?>
    });
</script>

<?php echo $footer; ?>