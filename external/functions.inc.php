<?php
	/**
	 * functions.inc.php
	 * Add extra functions in this file
	 */

	# Basic set-up ---------------------------------------------------------------------------------

	# Include additional Hummingbird dependencies
	include $site->baseDir('/external/utilities.inc.php');
	include $site->baseDir('/external/routes.inc.php');
	include $site->baseDir('/external/ajax.inc.php');
	include $site->baseDir('/external/ajax-response.inc.php');

	# Include Google Fonts
	$fonts = array(
		'Open Sans' => array(400, '400italic', 700, '700italic'),
		'Open Sans Condensed' => array(700, '700italic'),
		'Raleway' => array(300, '300:latin', 500, '500:latin', 700, '700:latin'),
		'Montserrat' => array(400, 700, '700:latin')
	);
	$site->registerStyle('google-fonts', get_google_fonts($fonts) );
	$site->registerStyle('font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css' );
	$site->registerStyle('magnific-popup', '//cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css' );

	# Include styles
	$site->registerStyle('reset', $site->baseUrl('/css/reset.css') );
	$site->registerStyle('plugins', $site->baseUrl('/css/plugins.css') );
	$site->registerStyle('sticky-footer', $site->baseUrl('/css/sticky-footer.css') );
	$site->registerStyle('project', $site->baseUrl('/css/project.less'), array('reset' , 'google-fonts','font-awesome', 'magnific-popup', 'plugins') );
	$site->enqueueStyle('project');

	# Include scripts
	$site->registerScript('class', $site->baseUrl('/js/class.js') );
	$site->registerScript('plugins', $site->baseUrl('/js/plugins.js'), array('jquery') );
	$site->registerScript('velocity', 'https://cdnjs.cloudflare.com/ajax/libs/velocity/1.2.3/velocity.min.js' );
	$site->registerScript('jquery.pep', '//rawgithub.com/briangonzalez/jquery.pep.js/master/src/jquery.pep.js' );
	$site->registerScript('velocity.ui', 'https://cdnjs.cloudflare.com/ajax/libs/velocity/1.2.3/velocity.ui.min.js' );
	$site->registerScript('magnific-popup', '//cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js', array('jquery') );
	$site->registerScript('script', $site->baseUrl('/js/script.js'), array('class', 'jquery', 'magnific-popup','jquery.form', 'jquery.pep', 'plugins', 'velocity', 'velocity.ui') );
	$site->enqueueScript('script');

	# Include additional project dependencies
	// include $site->baseDir('/external/sample-module.php');

	# Meta tags
	$site->addMeta('UTF-8', '', 'charset');
	$site->addMeta('viewport', 'width=device-width, initial-scale=1');

	$site->addMeta('og:title', $site->getPageTitle(), 'property');
	$site->addMeta('og:site_name', $site->getSiteTitle(), 'property');
	$site->addMeta('og:description', $site->getSiteTitle(), 'property');
	$site->addMeta('og:image', $site->urlTo('/favicon.png'), 'property');
	$site->addMeta('og:type', 'website', 'property');
	$site->addMeta('og:url', $site->urlTo('/'), 'property');

	# Pages
	// $site->addPage('sample', 'sample-page');
?>