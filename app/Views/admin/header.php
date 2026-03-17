<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ESP32 - Administration</title>
    <meta name="description" content="The small framework with powerful features">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?=setCSRFHeaderMeta()?>
	
	<link rel="shortcut icon" type="image/png" href="<?=base_url('favicon.ico')?>">
	<link rel="apple-touch-icon" sizes="180x180" href="<?=base_url('apple-touch-icon.png')?>">
	<link rel="icon" type="image/png" sizes="32x32" href="<?=base_url('favicon-32x32.png')?>">
	<link rel="icon" type="image/png" sizes="16x16" href="<?=base_url('favicon-16x16.png')?>">
	<link rel="manifest" href="<?=base_url('site.webmanifest')?>">
	
    <link rel="stylesheet" href="<?=base_url('assets/css/header.css')?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/backend.css')?>">
    <link rel="stylesheet" href="<?=base_url('assets/css/backend_entries.css')?>">
	<link rel="stylesheet" href="<?=base_url('assets/css/upload.css')?>">

    <?=service('text_editor')->load_style()?>

<?php 
	$local_assets = false;
	if ( $local_assets ) {
?>
		<link rel="stylesheet" href="<?=base_url('assets/vendor/jquery-ui.css')?>">
		<link rel="stylesheet" href="<?=base_url('assets/vendor/ckeditor5.css')?>">
		<link rel="stylesheet" href="<?=base_url('assets/vendor/fontawesome_all.min.css')?>">
		<script src="<?=base_url('assets/vendor/jquery-3.7.1.js')?>"></script>
		<script src="<?=base_url('assets/vendor/jquery-ui.min.js')?>"></script>
<?php } else { ?>
		<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
		<script src="https://code.jquery.com/jquery-3.7.1.js"
				integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
				crossorigin="anonymous"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>	
<?php } ?>
		<link rel="stylesheet" href="<?=base_url('assets/datetimepicker/jquery.datetimepicker.min.css')?>">
		<script src="<?=base_url('assets/datetimepicker/jquery.datetimepicker.full.js')?>"></script>
</head>
<body>

<header>
    <div class="menu">
        <ul>
            <li class="menu-toggle">
                <button id="menuToggle">&#9776;</button>
            </li>
            <li class="menu-item hidden"><a href="<?=base_url(route_to('admin'))?>"><i class="fa-solid fa-house"></i> Home</a></li>
            <li class="menu-item hidden"><a href="<?=base_url(route_to('admin.devices'))?>"><i class="fa-solid fa-microchip"></i> Devices</a></li>
            <li class="menu-item hidden"><a href="<?=base_url(route_to('admin.device_groups'))?>"><i class="fa-solid fa-layer-group"></i> Device Groups</a></li>
            <li class="menu-item hidden"><a href="<?=base_url(route_to('admin.users'))?>"><i class="fa-solid fa-user-cog"></i> Users</a></li>
        </ul>
        <ul>
            <li class="logo">
                <a href="<?=site_url()?>">
					<img src="<?=base_url('assets/images/logo.svg')?>" width="50"/>
					<span><b>ESP</b> 32</span>
                </a>
            </li>
        <?php if ($user): ?>
            <input type="checkbox" id="user_dropdown"/>
            <label for="user_dropdown"><?=$user["shortname"]?></label>
            <ul class="user_dropdown">
                <div class="titlebar">
                    <span><b>ESP</b> 32</span>
                    <a href="<?=site_url()."logout"?>">Logout</a>
                </div>
                <div class="userinfo">
                    <div class="profile"><?=$user["shortname"]?></div>
                    <div class="meta">
                        <span><b><?=$user["firstname"]?> <?=$user["middlename"]?></b> <?=$user["lastname"]?></span>
                        <span class="email"><?=$user["email"]?></span>
                    </div>
                </div>
                <ul>
                    <?php if($user["is_admin"]): ?>
                        <li><a href="<?=base_url(route_to('home'))?>">Terug naar Home</a></li>
                    <?php endif ?>
                </ul>
            </ul>
        <?php endif ?>
        </ul>
    </div>
</header>