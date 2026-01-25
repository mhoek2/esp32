<?php echo $header; ?>

<div class="notifications">
	<?php if (session()->has('errors')): ?>
		<?php foreach (session('errors') as $error): ?>
			<div class="item error">
				<div class="icon">
					<i class="fa-solid fa-triangle-exclamation"></i>
				</div>
				<div>
					<?=esc($error)?>
				</div>	
			</div>
		<?php endforeach ?>
	<?php endif ?>

	<?php if (session()->has('success')): ?>
		<div class="item success">
			<div class="icon">
				<i class="fa-regular fa-circle-check"></i>
			</div>
			<div>
				<?=session('success')?>
			</div>	
		</div>
	<?php endif ?>
</div>

<!-- CONTENT -->
<section class="main">
    <div class="content">

		<div class="upload-container">
			<?= form_open_multipart(base_url(route_to('admin.files_upload'))) ?>
				<label for="file-upload" class="drop-area" id="drop-area">
					<i class="fas fa-cloud-upload-alt"></i>
					<p>Plaats het bestand dat je wilt uploaden</p>
				</label>

				<input type="file" id="file-upload" name="userfile"/>
				<button type="submit" class="button-primary" id="upload">Uploaden</button>
			</form>

			<p id="file-name"></p>
		</div>	
			
		<table>
			<thead>
				<tr>
					<th>Bestand</th>
					<th width="150">Type</th>
					<th width="150">Grootte</th>
					<th width="150">Geupload</th>
					<th width="150">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach( $uploads as $file):?>
					<tr>
						<td>
							<a href="<?=download_url( $file['path'] )?>" target="_blank"><?=$file['filename']?></a>
						</td>
						<td><?=$file['extension']?></td>
						<td><?=readable_filesize($file['bytes'])?></td>
						<td><?=$file['created_at']?></td>
						<td>
							<button id="delete_file" data-file-id="<?=$file['id']?>" data-filename="<?=$file['filename']?>">
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

		var $fileInput = $('#file-upload');
		var $dropArea = $('#drop-area');
		var $fileName = $('#file-name');
		var $submitButton = $('#upload');
		
		$submitButton.on('click', function(e)
						 {
			if (!$(this).hasClass('active'))
				e.preventDefault();
		})
		
		$fileInput.on('change', function() 
		{
			var file = this.files[0];
			if (file) {
				$fileName.text('Selected file: ' + file.name);
				$submitButton.addClass('active');
				
			} else {
				$fileName.text('');
				$submitButton.removeClass('active');
			}
		});

		$dropArea.on('dragenter dragover', function(e) 
		{
			e.preventDefault();
			e.stopPropagation();
			$(this).addClass('highlight');
		});

		$dropArea.on('dragleave', function(e) 
		{
			e.preventDefault();
			e.stopPropagation();
			$(this).removeClass('highlight');
		});

		$dropArea.on('drop', function(e) 
		{
			e.preventDefault();
			e.stopPropagation();
			$(this).removeClass('highlight');

			var files = e.originalEvent.dataTransfer.files;
			if (files.length > 0) {
				$fileInput[0].files = files;
				// Trigger change event to update filename display
				$fileInput.trigger('change');
			}
		});
		
        $(document).on('click', '#delete_file', function ()
        {
            const file_id = $(this).data('file-id');
			const confirmation = confirm(`Are you sure you want to remove ${$(this).data('filename')}` );

			if (confirmation) {
                $.ajax({
		            url: '<?=base_url(route_to('admin.files_delete'))?>',
		            method: 'POST',
		            data: {
			            file_id: file_id,
						<?=setCSRFPostData()?>
		            },
		            success: function (response) {
						updateCSRFMeta(response);

			            if (response.status === 'success') {
                            location.href = "<?=base_url(route_to('admin.files'))?>";
			            }
		            }
	            });
            }
        });
	});
	</script>

<?php echo $footer; ?>