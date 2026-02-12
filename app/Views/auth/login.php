<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.login') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>

	<style>
		.bg-light {
			background-color: transparent !important;
		}
		.login-logo {
			background: url(assets/images/logo.svg) no-repeat;
			background-size: contain;
			width:100px;
			height: 100px;
			margin:0 auto;
		}
		
		.login-left-side {
			background: url( assets/images/login.jpg);
			background-size: cover;
			border-radius: 10px 0 0 10px;
			color:#f1f1f1;
		}
		
		.login-right-side {
			border-radius: 0 10px 10px 0;
		}
		
		.card {
			border: 0px;
		}
		
		.btn-container {
			width: fit-content;
			margin: 0 auto;
			display: grid;
		}
		.btn {
			border:2px solid #f8f4fd;
			color:#3d3d3d;
		}
		.btn.btn-bg-gradient {
			position: relative;
			display: inline-block;
			line-height: 35px;
			border-radius: 35px;
			padding: 0.25em 1.5em;
			z-index: 0;
			overflow: hidden;
		}

		.btn.btn-bg-gradient::before {
			content: "";
			position: absolute;
			top: -2px;
			left: -2px;
			right: -2px;
			bottom: -2px;
			border-radius: inherit;
			z-index: -1;
			opacity: 0.6;
		}

		.btn.btn-bg-gradient:hover::before {
			opacity: 1;
		}

		.btn.btn-bg-gradient::after {
			content: "";
			position: absolute;
			top: 2px;
			left: 2px;
			right: 2px;
			bottom: 2px;
			border-radius: inherit;
			z-index: -1;
		}
		
		.btn.btn-bg-gradient {
			background-color: #f9f9f9; /* fallback background */
			color:#3d3d3d;
		}
		.btn.btn-bg-gradient::before {
			background: linear-gradient(92deg, rgba(63, 116, 251, 1) 0%, rgb(114 78 211) 50%, rgb(70 218 252) 100%);
		}
		.btn.btn-bg-gradient::after {
			background-color: #f8f4fd;
		}
		@media only screen and (max-width: 768px) {
			.login-left-side {
				display:none !important;
			}
			.login-right-side {
				border-radius: 10px;
				width:100%; !important;
			}
		}
	</style>
    <div class="container d-flex justify-content-center p-5">
		<div class="card col-6 col-md-6 shadow-sm login-left-side d-flex flex-column justify-content-start align-items-center text-center pt-5">
			<h1 class="card-title mb-4">ESP32 Management</h1>
			<p>Manage IoT devices that are connected to this network</p>
		</div>
        <div class="card col-6 col-md-4 shadow-sm login-right-side">
            <div class="card-body">
				<div class="login-logo"></div>
                <h5 class="card-title mb-5"><?= lang('Auth.login') ?></h5>

                <?php if (session('error') !== null) : ?>
                    <div class="alert alert-danger" role="alert"><?= esc(session('error')) ?></div>
                <?php elseif (session('errors') !== null) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?php if (is_array(session('errors'))) : ?>
                            <?php foreach (session('errors') as $error) : ?>
                                <?= esc($error) ?>
                                <br>
                            <?php endforeach ?>
                        <?php else : ?>
                            <?= esc(session('errors')) ?>
                        <?php endif ?>
                    </div>
                <?php endif ?>

                <?php if (session('message') !== null) : ?>
                    <div class="alert alert-success" role="alert"><?= esc(session('message')) ?></div>
                <?php endif ?>

                <form action="<?= url_to('login') ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- Email -->
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="floatingEmailInput" name="email" inputmode="email" autocomplete="email" placeholder="<?= lang('Auth.email') ?>" value="<?= old('email') ?>" required>
                        <label for="floatingEmailInput"><?= lang('Auth.email') ?></label>
                    </div>

                    <!-- Password -->
                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="floatingPasswordInput" name="password" inputmode="text" autocomplete="current-password" placeholder="<?= lang('Auth.password') ?>" required>
                        <label for="floatingPasswordInput"><?= lang('Auth.password') ?></label>
                    </div>

                    <!-- Remember me -->
                    <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
                        <div class="form-check">
                            <label class="form-check-label">
                                <input type="checkbox" name="remember" class="form-check-input" <?php if (old('remember')): ?> checked<?php endif ?>>
                                <?= lang('Auth.rememberMe') ?>
                            </label>
                        </div>
                    <?php endif; ?>

                    <div class="btn-container">
                        <button type="submit" class="btn btn-bg-gradient"><?= lang('Auth.login') ?></button>
                    </div>

                    <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                        <p class="text-center"><?= lang('Auth.forgotPassword') ?> <a href="<?= url_to('magic-link') ?>"><?= lang('Auth.useMagicLink') ?></a></p>
                    <?php endif ?>

                    <?php if (setting('Auth.allowRegistration')) : ?>
                        <p class="text-center"><?= lang('Auth.needAccount') ?> <a href="<?= url_to('register') ?>"><?= lang('Auth.register') ?></a></p>
                    <?php endif ?>

                </form>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
