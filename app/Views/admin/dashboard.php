<?php echo $header; ?>

<!-- CONTENT -->
<style>
	.dashboard-modules {
		  display: grid;
		  grid-template-columns: repeat(4, 1fr);
		  gap: 16px;
	}
		.dashboard-modules article {
			background:#fff;
			border-radius: 10px;
			overflow: hidden;
			word-wrap: break-word;
			padding: 10px;
		}
		.dashboard-modules article.small {
			grid-column: span 1;
		}
		.dashboard-modules article.medium {
			grid-column: span 2;
		}
		.dashboard-modules article.large {
			grid-column: span 3;
		}
		.dashboard-modules article.full {
			grid-column: span 4;
		}
</style>

<section class="main">
	<div class="content">
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