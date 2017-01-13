<?php $site->getParts(array( 'shared/header-html', 'sticky-footer/header' )); ?>

	<?php //$site->getParts('sections/section-inicio') ?>
	<?php //$site->getParts('sections/section-somos') ?>
	<?php //$site->getParts('sections/section-problema') ?>
	<?php //$site->getParts('sections/section-solucion') ?>
	<?php //$site->getParts('sections/section-contacto') ?>

	<section class="section section-maker">

		<div class="inner boxfix-vert">
			<div class="margins">
				<div class="row">

					<div class="col col-3">
						<h1 class="text-center">Elemts</h1><br>
						<a class="button button-block tab_bar">Tab</a><br>
						<a class="button button-block">Nav</a><br>
						<a class="button button-block">Image</a><br>
						<a class="button button-block">Button</a><br>
						<a class="button button-block">Text</a><br>
					</div>

					<div class="col col-6">
						<div class="row">
							<div class="col col-10 col-offset-1">
								<div class="screen">
									<div class="nav_screen"></div>
									<div class="tab_screen"></div>
								</div>
							</div>
						</div>
					</div>

					<div class="col col-3">
						<div class="inspector">
							<h2 class="text-center">Inspector</h2>
						</div>
					</div>

				</div>
			</div>
		</div>

	</section>

<?php $site->getParts(array( 'sticky-footer/footer', 'shared/footer-html' )); ?>