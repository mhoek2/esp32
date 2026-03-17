<?php echo $header; ?>

<div class="breadcrumbs">
   	<ul>
		<li><span>Devices</span></li>
    </ul>
</div>

<section class="main">
    <div class="content">

        <table>
            <thead>
                <tr>
                    <th width="200">Name</th>
                    <th width="200">Group</th>
                    <th width="150">MAC</th>
                    <th>Protocol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $devices as $id => $item):?>
                    <tr>
                        <td>
							<a href="<?=base_url(route_to('admin.device', $item['id']))?>">
								<?= !empty($item['name']) ? $item['name'] : "undefined" ?>
							</a>
						</td>
                        <td>
							<a href="<?= $item['group_id'] > 0 ? base_url(route_to('admin.device_group', $item['group_id'])) : '#'?>">
								<?= !empty($item['group_name']) ? $item['group_name'] : "None" ?>
							</a>
						</td>
                        <td><?=$item['mac']?></td>
                        <td><?=$item['protocol']?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php echo $footer; ?>