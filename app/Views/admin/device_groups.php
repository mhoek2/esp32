<?php echo $header; ?>

<div class="breadcrumbs">
   	<ul>
		<li><span>Device groups</span></li>
    </ul>
</div>

<section class="main">
    <div class="content">
		
        <table>
            <thead>
                <tr>
                    <th width="200">Name</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $device_groups as $id => $item):?>
                    <tr>
                        <td>
							<a href="<?=base_url(route_to('admin.device_group', $item['id']))?>">
								<?= !empty($item['name']) ? $item['name'] : "undefined" ?>
							</a>
						</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php echo $footer; ?>