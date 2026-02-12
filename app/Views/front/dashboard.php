<?php echo $header; ?>

<style>
	.dashboard-modules {
		  display: grid;
		  grid-template-columns: repeat(4, 1fr);
		  gap: 16px;
	}
		.dashboard-modules article {
			background:#f1f1f1;
			border-radius: 10px;
			overflow: hidden;
			word-wrap: break-word;
			padding: 10px;
		}
		.dashboard-modules article.wide {
			grid-column: span 2;
		}
		.dashboard-modules article.full {
			grid-column: span 4;
		}
</style>

<section class="main">
	<div class="content" style="background:transparent; padding:0;">
		<div class="dashboard-modules">
			<?php foreach( $dashboard_modules as $module ): ?>
				<?php if(!$module['visible']) continue ?>

				<article class="<?=$module['css_class']?>">
					<?=$module['view']?>
				</article>
			<?php endforeach ?>
		</div>
	</div>
</section>

<?php echo $footer; ?>