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
                    <th width="150">MAC</th>
                    <th>Protocol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach( $devices as $id => $item):?>
                    <tr>
                        <td><?=$item['mac']?></td>
                        <td><?=$item['protocol']?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php echo $footer; ?>