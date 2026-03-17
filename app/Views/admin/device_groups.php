<?php echo $header; ?>

<div class="breadcrumbs">
   	<ul>
		<li><span>Device groups</span></li>
    </ul>
</div>

<section class="main">
    <div class="content">
		<div class="actions left" style="margin-bottom:25px;">
            <a class="button-primary" href="<?=base_url(route_to('admin.device_group.new'))?>">
                <i class="fa-solid fa-circle-plus"></i> Create Device Group
            </a>
		</div>

        <table>
            <thead>
                <tr>
                    <th width="200">Name</th>
                    <th width="50">Devices</th>
                    <th width="50">Actions</th>
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
						<td>
							<?= !empty($item['count']) ? $item['count'] : 0 ?>
						</td>
                        <td>
                            <button id="delete" data-group-id="<?=$item['id']?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script {csp-script-nonce}>
    $(document).ready(function () {
		<?=updateCSRFMeta() // csrf helper ?>

        $(document).on('click', '#delete', function ()
        {
            const group_id = $(this).data('group-id');
			const confirmation = confirm('Are you sure you want to remove this device group');

			if (confirmation) {
                $.ajax({
		            url: '<?=base_url(route_to('admin.device_group.delete'))?>',
		            method: 'POST',
		            data: {
			            group_id: group_id,
						<?=setCSRFPostData()?>
		            },
		            success: function (response) {
						updateCSRFMeta(response);

			            if (response.status === 'success') {
                            location.reload();
			            }
                        else{
                            alert('Failed to delete device group: ' + response.message);
                        }
		            }
	            });
            }
        });
    });
</script>

<?php echo $footer; ?>