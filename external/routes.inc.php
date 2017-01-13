<?php
	/**
	 * routes.inc.php
	 * Add your additional routes here
	 */

	# Sample route ---------------------------------------------------------------------------------
	function route_status() {
		global $site;
		$site->errorMessage('This is the status page');
		exit;
	}
	$site->addRoute('/status', 'route_status', true);

?>