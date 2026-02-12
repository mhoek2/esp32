<?php echo $header; ?>

<style>
	.users-table .user-meta {
		display:flex;
		flex-direction: row;
	}
		.users-table .user-meta .profile {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			background-color: var(--header-user-dropdown-button-background);
			color: white;
			display: flex;
			justify-content: center;
			align-items: center;
			font-size: 1em;
			font-weight: bold;
			margin-right: 15px;
		}
		.users-table .user-meta a {
			display:flex;
			align-items: center;
		}
</style>

<div class="breadcrumbs">
   	<ul>
		<li><span>Users</span></li>
    </ul>
</div>

<section class="main">
    <div class="content">
		
		<div class="actions left" style="margin-bottom:25px;">
            <a class="button-primary" href="<?=base_url(route_to('admin.user.new'))?>">
                <i class="fa-solid fa-circle-plus"></i> Create User
            </a>
		</div>
		
        <table class="users-table">
            <thead>
                <tr>
                    <th width="150">Username</th>
                    <th>Name</th>
                    <th width="150">Rol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $users as $id => $item):?>
                    <tr>
                        <td class="user-meta">
							<div class="profile">
								<?=$item['shortname']?>
							</div>
							<a href="<?=base_url(route_to('admin.user', $item['id']))?>">
								<?=$item['email']?>
							</a>
						</td>
                        <td><?=$item['fullname']?></td>
                        <td><?=$item['group']?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php echo $footer; ?>