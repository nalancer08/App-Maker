<?php
	/**
	 * config.inc.php
	 * Here's where you configure your Hummingbird instance
	 */

	# Set the active profile
	define( 'PROFILE', 'development' );

	/**
	 * Site settings
	 * @var array 	Array with configuration options
	 */
	$settings = array(
		'development' => array(
			# Global settings
			'site_url' => 'http://localhost:1234/appbuilders/am/',
			# Database settings
			'db_driver' => 'mysql', # none, sqlite or mysql
			'db_host' => 'localhost',
			'db_user' => 'root',
			'db_pass' => '',
			'db_name' => '',
			'db_file' => ABSPATH . '/include/schema.sqlite',
			# Plugins
			'plugins' => array(
				'painLESS'
			)
		),
		'testing' => array(
			# Global settings
			'site_url' => 'http://dev.yoursite.com',
			# Database settings
			'db_driver' => 'none',
			'db_host' => '',
			'db_user' => '',
			'db_pass' => '',
			'db_name' => '',
			'db_file' => ABSPATH . '/include/schema.sqlite',
			# Plugins
			'plugins' => array(
				'i18n'
			)
		),
		'production' => array(
			# Global settings
			'site_url' => 'http://yoursite.com',
			# Database settings
			'db_driver' => 'none',
			'db_host' => '',
			'db_user' => '',
			'db_pass' => '',
			'db_name' => '',
			'db_file' => ABSPATH . '/include/schema.sqlite',
			# Plugins
			'plugins' => array(
				'i18n'
			)
		),
		'shared' => array(
			# General
			'site_name' => 'Deja plastico',
			# Security settings
			'pass_salt' => '',
			'token_salt' => ''
		)
	);
?>