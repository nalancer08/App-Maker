<?php
	/**
	 * site.inc.php
	 * This class is the core of Hummingbird, so please try to keep it backwards-compatible if you modify it.
	 *
	 * Version: 	2.0.6
	 * Author(s):	biohzrdmx <github.com/biohzrdmx>
	 */

	class Site {
		protected $base_url;
		protected $base_dir;
		protected $routes;
		protected $default_route;
		protected $script_vars;
		protected $dirs;
		protected $actions;
		protected $scripts;
		protected $styles;
		protected $slugs;
		protected $request;
		protected $params;
		protected $page;
		protected $pages;
		protected $plugins;
		protected $metas;
		protected $site_title;
		protected $page_title;
		protected $pass_salt;
		protected $token_salt;
		protected $hooks;
		protected $filters;
		protected $profile;
		protected $dbh;

		/**
		 * Class constructor
		 */
		function __construct($settings) {
			# Load settings
			$this->profile = $settings[PROFILE];
			$this->globals = $settings['shared'];
			$this->base_dir = ABSPATH;
			$this->base_url = $this->profile['site_url'];
			# Create arrays
			$this->routes = array();
			$this->actions = array();
			$this->scripts = array();
			$this->styles = array();
			$this->enqueued_scripts = array();
			$this->enqueued_styles = array();
			$this->slugs = array();
			$this->params = array();
			$this->pages = array();
			$this->hooks = array();
			$this->metas = array();
			$this->plugins = $this->profile['plugins'];
			# Add routes
			$this->addRoute('/ajax', 'Site::ajaxRequest');
			$this->addRoute('/:page', 'Site::getPage');
			# Add pages
			$this->addPage('home', 'home-page');
			$this->default_route = '/home';
			# Initialize variables
			$this->pass_salt = $settings['shared']['pass_salt'];
			$this->token_salt = $settings['shared']['token_salt'];
			$this->site_title = $settings['shared']['site_name'];
			$this->page_title = $this->site_title;
			# Register base styles
			$this->registerStyle('twitter-bootstrap', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/css/bootstrap.min.css');
			$this->registerStyle('magnific-popup', '//cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/0.9.9/magnific-popup.css');
			# Register base scripts
			$this->registerScript('modernizr', '//cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.2/modernizr.min.js');
			$this->registerScript('respond', '//cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.js');
			$this->registerScript('jquery', '//cdnjs.cloudflare.com/ajax/libs/jquery/1.11.0/jquery.min.js');
			$this->registerScript('jquery.form', '//cdnjs.cloudflare.com/ajax/libs/jquery.form/3.50/jquery.form.min.js', array('jquery'));
			$this->registerScript('jquery.cycle', '//cdnjs.cloudflare.com/ajax/libs/jquery.cycle/3.03/jquery.cycle.all.min.js', array('jquery'));
			$this->registerScript('magnific-popup', '//cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/0.9.9/jquery.magnific-popup.min.js', array('jquery'));
			$this->registerScript('twitter-bootstrap', '//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.2.0/js/bootstrap.min.js', array('jquery'));
			$this->registerScript('underscore', '//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.6.0/underscore-min.js');
			$this->registerScript('backbone', '//cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.2/backbone-min.js', array('underscore'));
			# Default dirs
			$this->dirs = array(
				'plugins' => '/plugins',
				'pages'   => '/pages',
				'parts'   => '/parts',
				'images'  => '/images',
				'scripts' => '/js',
				'styles'  => '/css'
			);
			# Create database connection
			try {
				switch ( $this->profile['db_driver'] ) {
					case 'sqlite':
						$dsn = sprintf('sqlite:%s', $this->profile['db_file']);
						$this->dbh = new PDO($dsn);
						break;
					case 'mysql':
						$dsn = sprintf('mysql:host=%s;dbname=%s', $this->profile['db_host'], $this->profile['db_name']);
						$this->dbh = new PDO($dsn, $this->profile['db_user'], $this->profile['db_pass']);
						break;
				}
				# Change error and fetch mode
				if ($this->dbh) {
					$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
				}
			} catch (PDOException $e) {
				error_log( $e->getMessage() );
				$this->errorMessage( 'Database error ' . $e->getCode() );
			}
		}

		/**
		 * Get the specified page
		 * @param  mixed $params         String with slug or array with parameters
		 * @param  string $templates_dir Override default template dir
		 * @return boolean               TRUE if the page was found, FALSE otherwise
		 */
		static function getPage($params, $templates_dir = '', $whitelist = true) {
			global $site;
			if ( empty($templates_dir) ) {
				$templates_dir = sprintf('%s/pages', $site->base_dir);
			}
			if ( is_array($params) ) {
				$slug = isset( $params[1] ) ? $params[1] : 'home';
			} else {
				$slug = $params;
			}
			$slug = ltrim( rtrim($slug, '/'), '/' );
			$template = isset($site->pages[$slug]) && $whitelist ? $site->pages[$slug] : $slug;

			#Check if redirect
			if(preg_match( '#((https?|ftp)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i', $template ) === 1 ){
				$site->redirectTo($template, null, '302');
				exit();
			}

			$page = sprintf('%s/%s.php', $templates_dir, $template);
			if ( (!isset($site->pages[$slug]) && $whitelist ) || !file_exists($page) ) {
				# The page does not exist
				$slug = '404';
				$site->addBodyClass('error-404');
				$page = sprintf('%s/pages/%s.php', $site->base_dir, $slug);
				header('HTTP/1.0 404 Not Found');
			} else {
				$site->addBodyClass( $slug . ( preg_match('/^(.*)-page$/', $slug) === 1 ? '' : '-page') );
			}
			# Save the current page slug
			$site->page = str_replace('-page', '', $slug);
			# Include the file
			extract($GLOBALS, EXTR_REFS | EXTR_SKIP);
			include $page;
			return true;
		}

		/**
		 * Handle AJAX request
		 */
		static function ajaxRequest() {
			global $site;
			$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
			if ( isset( $site->actions[$action] ) ) {
				call_user_func( $site->actions[$action] );
			} else {
				echo 0;
				exit;
			}
		}

		/**
		 * Get base folder
		 * @param  string  $path Path to append
		 * @param  boolean $echo Whether to print the resulting string or not
		 * @return string        The well-formed path
		 */
		function baseDir($path = '', $echo = false) {
			$ret = sprintf('%s%s', $this->base_dir, $path);
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Get base URL
		 * @param  string  $path     Path to append
		 * @param  boolean $echo     Whether to print the resulting string or not
		 * @param  string  $protocol Protocol to override default http (https, ftp, etc)
		 * @return string            The well-formed URL
		 */
		function baseUrl($path = '', $echo = false, $protocol = null) {
			$base_url = rtrim($this->base_url, '/');
			if (!$protocol && $this->isSecureRequest() ) {
				$base_url = str_replace('http://', 'https://', $base_url);
			} else if ($protocol) {
				$protocol .= strrpos($protocol, ':') > 0 ? '' : ':';
				$base_url = str_replace('http:', $protocol, $base_url);
			}
			if ( !empty($path) && $path[0] != '/' ) {
				$path = '/' . $path;
			}
			$ret = sprintf('%s%s', $base_url, $path);
			# Print and/or return the result
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Get the specified directory path
		 * @param  string  $dir  Directory name
		 * @param  boolean $full Whether to return a relative or fully-qualified path
		 * @return mixed         The path to the specified directory or False if it doesn't exist
		 */
		function getDir($dir, $full = true) {
			if ( isset( $this->dirs[$dir] ) ) {
				return ($full ? $this->baseDir( $this->dirs[$dir] ) : $this->dirs[$dir]);
			}
			return false;
		}

		/**
		 * Set the path to the specified directory
		 * @param string $dir  Directory name, if it exists it will be overwritten
		 * @param string $path Path to the directory, relative to the site root
		 */
		function setDir($dir, $path) {
			$this->dirs[$dir] = $path;
		}

		/**
		 * Add a new route
		 * @param  string  $route     Parametrized route
		 * @param  string  $functName Handler function name
		 * @param  boolean $insert    If set, the route will be inserted at the beginning
		 */
		function addRoute($route, $functName, $insert = false) {
			if ($insert) {
				$this->routes = array_reverse($this->routes, true);
			    $this->routes[$route] = $functName;
			    $this->routes = array_reverse($this->routes, true);
			} else {
				$this->routes[$route] = $functName;
			}
		}

		/**
		 * Removes the specified route
		 * @param  string $route Parametrized route
		 * @return boolean       True if the route was found and removed, false otherwise
		 */
		function removeRoute($route) {
			if ( isset( $this->routes[$route] ) ) {
				unset( $this->routes[$route] );
				return true;
			}
			return false;
		}

		/**
		 * Get the default route
		 * @return string The default route
		 */
		function getDefaultRoute() {
			return $this->default_route;
		}

		/**
		 * Set the default route
		 * @param string $route Full route, defaults to '/home'
		 */
		function setDefaultRoute($route) {
			$this->default_route = $route;
		}

		/**
		 * Process current request
		 * @return boolean TRUE if routing has succeeded, FALSE otherwise
		 */
		function routeRequest() {
			# Routing stuff, first get the site url
			$site_url = trim($this->base_url, '/');

			# Remove the protocol from it
			$domain = preg_replace('/^(http|https):\/\//', '', $site_url);

			# Now remove the path
			$segments = explode('/', $domain, 2);
			if (count($segments) > 1) {
				$domain = array_pop($segments);
			}

			# Get the request and remove the domain
			$request = trim($_SERVER['REQUEST_URI'], '/');
			$request = preg_replace("/".str_replace('/', '\/', $domain)."/", '', $request, 1);
			$request = ltrim($request, '/');

			# Save current request string
			$this->request = $request;

			# Get the parameters
			$segments = explode('?', $request);
			if (count($segments) > 1) {
				$params_str = array_pop($segments);
				parse_str($params_str, $this->params);
			}

			# And the segments
			$cur_route = array_shift($segments);
			$segments = explode('/', $cur_route);

			# Now make sure the current route begins with '/' and doesn't end with '/'
			$cur_route = '/' . $cur_route;
			$cur_route = rtrim($cur_route, '/');

			# Make sure we have a valid route
			if ( empty($cur_route) ) {
				$cur_route = $this->default_route;
			}

			if (! $this->matchRoute($cur_route) ) {
				# Nothing was found, show a 404 page
				Site::getPage('404');
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Try to match the given route with one of the registered handlers and process it
		 * @param  string $route  		The route to match
		 * @return boolean        		TRUE if the route matched with a handler, FALSE otherwise
		 */
		function matchRoute($spec_route) {
			# And try to match the route with the registered ones
			$matches = array();
			foreach ($this->routes as $route => $handler) {
				# Compile route into regular expression
				$a = preg_replace('/[\-{}\[\]+?.,\\\^$|#\s]/', '\\$&', $route); // escapeRegExp
				$b = preg_replace('/\((.*?)\)/', '(?:$1)?', $a);                // optionalParam
				$c = preg_replace('/(\(\?)?:\w+/', '([^\/]+)', $b);             // namedParam
				$d = preg_replace('/\*\w+/', '(.*?)', $c);                      // splatParam
				$pattern = "~^{$d}$~";
				if ( preg_match($pattern, $spec_route, $matches) == 1) {
					# We've got a match, try to route with this handler
					$ret = call_user_func($handler, $matches);
					if ($ret) {
						# Exit the loop only if the handler did its job
						return true;
					}
				}
			}
			return false;
		}

		/**
		 * Get the registered routes
		 * @return array The registered routes
		 */
		function getRoutes() {
			return $this->routes;
		}

		/**
		 * Load the specified template parts
		 * @param  mixed $mixed An string or array of parts
		 */
		function getParts($mixed, $parts_dir = '', $params = array()) {
			# Check parameter type
			if ( is_array($mixed) ) {
				# If is an array we should call this recursively for each part
				foreach($mixed as $part) {
					$this->getParts($part, $parts_dir, $params);
				}
			} else if ( is_string($mixed) ) {
				# If it's an string we just include the file
				if ($parts_dir == '') {
					$parts_dir = sprintf('%s/parts', $this->base_dir);
				}
				$part = sprintf('%s/%s.php', $parts_dir, $mixed);
				if (file_exists($part)) {
					global $site;
					# Include the file
					extract($GLOBALS, EXTR_REFS | EXTR_SKIP);
					include $part;
					echo "\n";
				}
			}
		}

		/**
		 * Get the current slug list
		 * @param  boolean $echo Whether to print the result or not
		 * @return string        String with space-delimited slugs
		 */
		function bodyClass($echo = true) {
			$ret = implode(' ', $this->slugs);
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Append a class to the body classes array
		 * @param mixed $class 	Class name or array with class names
		 */
		function addBodyClass($class) {
			if ( is_array($class) ) {
				foreach ($class as $item) {
					$this->addBodyClass($item);
				}
			} else {
				if (! in_array($class, $this->slugs) ) {
					$this->slugs[] = $class;
				}
			}
		}

		/**
		 * Check whether the given page slug is the current one
		 * @param  string  $slug The page slug
		 * @return boolean       True if the slug is in the current one, False otherwise
		 */
		function isPage($slug) {
			return (strcasecmp($slug, $this->page) == 0);
		}

		/**
		 * Return the current page slug
		 * @return string  		The page slug
		 */
		function getCurPage() {
			return $this->page;
		}


		/**
		 * Get the current request string
		 * @return string The current request string
		 */
		function getCurRequest() {
			return $this->request;
		}

		/**
		 * Check whether the given slug is on the current list of slugs
		 * @param  string  $slug The slug
		 * @return boolean       True if the slug is in the slugs array, False otherwise
		 */
		function hasSlug($slug) {
			return in_array($slug, $this->slugs);
		}

		/**
		 * Retrieve the current list of slugs
		 */
		function getSlugs() {
			return $this->slugs;
		}

		/**
		 * Add a new page to the whitelist
		 * @param  string $slug     Page slug
		 * @param  string $template Page template name (without extension)
		 */
		function addPage($slug, $template = '') {
			if ( empty($template) ) {
				$template = $slug;
			}
			$this->pages[$slug] = $template;
		}

		/**
		 * Removes the specified page
		 * @param  string $slug  Page slug
		 * @return boolean       True if the page was found and removed, false otherwise
		 */
		function removePage($slug) {
			if ( isset( $this->pages[$slug] ) ) {
				unset( $this->pages[$slug] );
				return true;
			}
			return false;
		}

		/**
		 * Sanitize the given string (slugify it)
		 * @param  string $str       The string to sanitize
		 * @param  array  $replace   Optional, an array of characters to replace
		 * @param  string $delimiter Optional, specify a custom delimiter
		 * @return string            Sanitized string
		 */
		function toAscii($str, $replace = array(), $delimiter = '-') {
			setlocale(LC_ALL, 'en_US.UTF8');
			# Remove spaces
			if( !empty($replace) ) {
				$str = str_replace((array)$replace, ' ', $str);
			}
			# Remove non-ascii characters
			$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
			# Remove non alphanumeric characters and lowercase the result
			$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			# Remove other unwanted characters
			$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
			return $clean;
		}

		/**
		 * Redirect to given route
		 * @param  string $route    Route to redirect to
		 * @param  string $protocol Protocol to override default http (https, ftp, etc)
		 */
		function redirectTo($route, $protocol = null, $http_response_code = 302) {
			if ( preg_match('/^(http:\/\/|https:\/\/).*/', $route) !== 1 ) {
				$url = $this->baseUrl($route, false, $protocol);
			} else {
				$url = $route;
			}
			$header = sprintf('Location: %s', $url);
			header($header, true, $http_response_code);
			exit;
		}

		/**
		 * Get a well formed url to the specified route or page slug
		 * @param  string  $route    Route or page slug
		 * @param  boolean $echo     Whether to print out the resulting url or not
		 * @param  string  $protocol Protocol to override default http (https, ftp, etc)
		 * @return string            The resulting url
		 */
		function urlTo($route, $echo = false, $protocol = null) {
			$url = $this->baseUrl($route, false, $protocol);
			if ($echo) {
				echo $url;
			}
			return $url;
		}

		/**
		 * Get a well formed url to the specified image file and optionally echo it
		 * @param  string  $filename Image file name (e.g. 'logo.png')
		 * @param  boolean $echo     Whether to print out the resulting url or not
		 * @return string            The resulting url
		 */
		function img($filename, $echo = true) {
			$dir = $this->getDir('images', false);
			$ret = $this->urlTo( sprintf('%s/%s', $dir, $filename), $echo);
			return $ret;
		}

		/**
		 * Add an stylesheet to the list
		 * @param  string $name      Name of the stylesheet
		 * @param  string $url       URL to the stylesheet (absolute)
		 * @param  array  $requires  Array of stylesheets this stylesheet depends on (they'll be automatically added to the page)
		 */
		function registerStyle($name, $url, $requires = array()) {
			$this->styles[$name] = array(
				'resource' => $url,
				'requires' => $requires
			);
		}

		/**
		 * Add an script to the list
		 * @param  string $name      Name of the script
		 * @param  string $url       URL to the script (absolute)
		 * @param  array  $requires  Array of scripts this script depends on (they'll be automatically added to the page)
		 */
		function registerScript($name, $url, $requires = array()) {
			$this->scripts[$name] = array(
				'resource' => $url,
				'requires' => $requires
			);
		}

		/**
		 * Add an script variable
		 * @param string $var   Variable name
		 * @param mixed  $value Variable value
		 */
		function addScriptVar($var, $value) {
			$this->script_vars[$var] = $value;
		}

		/**
		 * Remove an script variable
		 * @param  string $var Variable name
		 * @return nothing
		 */
		function removeScriptVar($var) {
			unset( $this->script_vars[$var] );
		}

		/**
		 * Print the registered script variables
		 * @return nothing
		 */
		function includeScriptVars() {
			$vars = '';
			if ($this->script_vars) {
				foreach ($this->script_vars as $var => $value) {
					if ( is_array($value) || is_object($value) ) {
						$value = json_encode($value);
					} elseif (! is_numeric($value) ) {
						$value = "'{$value}'";
					}
					$vars .= "var {$var} = {$value};\n";
				}
				$output = sprintf("<script type=\"text/javascript\">\n%s</script>", $vars);
				echo($output."\n");
			}
		}

		/**
		 * Add a previously registered stylesheet to the inclusion queue
		 * @param  string $name Name of the registered stylesheet
		 */
		function enqueueStyle($name) {
			if ( isset( $this->styles[$name] ) ) {
				if (! isset($this->enqueued_styles[$name]) ) {
					$item = $this->styles[$name];
					foreach ($item['requires'] as $dep) {
						$this->enqueueStyle($dep);
					}
					$this->enqueued_styles[$name] = $name;
				}
			}
		}

		/**
		 * Add a previously registered script to the inclusion queue
		 * @param  string $name 	Name of the registered script
		 */
		function enqueueScript($name) {
			if ( isset( $this->scripts[$name] ) ) {
				if (! isset($this->enqueued_scripts[$name]) ) {
					$item = $this->scripts[$name];
					foreach ($item['requires'] as $dep) {
						$this->enqueueScript($dep);
					}
					$this->enqueued_scripts[$name] = $name;
				}
			}
		}

		/**
		 * Remove a previously enqueued stylesheet from the inclusion queue
		 * @param string $name  Name of the enqueued stylesheet
		 * @param boolean $dependencies Dequeue dependencies too (not recommended)
		 */
		function dequeueStyle($name, $dependencies = false) {
			if ( isset( $this->styles[$name] ) ) {
				if ( isset($this->enqueued_styles[$name]) ) {
					$item = $this->styles[$name];
					if ($dependencies) {
						foreach ($item['requires'] as $dep) {
							$this->dequeueStyle($dep);
						}
					}
					unset( $this->enqueued_styles[$name] );
				}
			}
		}

		/**
		 * Remove a previously enqueued script from the inclusion queue
		 * @param string $name  Name of the enqueued script
		 * @param boolean $dependencies Dequeue dependencies too (not recommended)
		 */
		function dequeueScript($name, $dependencies = false) {
			if ( isset( $this->scripts[$name] ) ) {
				if ( isset($this->enqueued_scripts[$name]) ) {
					$item = $this->scripts[$name];
					if ($dependencies) {
						foreach ($item['requires'] as $dep) {
							$this->dequeueScript($dep);
						}
					}
					unset( $this->enqueued_scripts[$name] );
				}
			}
		}

		/**
		 * Output the specified style
		 * @param  string $style 	Registered style name
		 */
		function includeStyle($style) {
			global $site;
			if ( isset( $this->styles[$style] ) ) {
				$item = $this->styles[$style];
				$output = $site->executeHook('core.includeStyle', $item);
				$output = $output ? $output : sprintf('<link rel="stylesheet" type="text/css" href="%s">', $item['resource']);
				echo($output."\n");
			}
		}

		/**
		 * Output the specified script
		 * @param  string $script 	Registered script name
		 */
		function includeScript($script) {
			global $site;
			if ( isset( $this->scripts[$script] ) ) {
				$item = $this->scripts[$script];
				$output = $site->executeHook('core.includeScript', $item);
				$output = $output ? $output : sprintf('<script type="text/javascript" src="%s"></script>', $item['resource']);
				echo($output."\n");
			}
		}

		/**
		 * Output all the registered stylesheets
		 */
		function includeStyles() {
			global $site;
			foreach ($this->enqueued_styles as $style) {
				$this->includeStyle($style);
			}
			$site->executeHook('core.includeStyles');
		}

		/**
		 * Output all the registered scripts
		 */
		function includeScripts() {
			global $site;
			foreach ($this->enqueued_scripts as $script) {
				$this->includeScript($script);
			}
			$site->executeHook('core.includeScripts');
		}

		/**
		 * Retrieve all the registered stylesheets
		 */
		function getStyles() {
			return $this->styles;
		}

		/**
		 * Retrieve all the registered scripts
		 */
		function getScripts() {
			return $this->scripts;
		}

		/**
		 * Set the page title
		 * @param string $title New page title
		 */
		function setPageTitle($title) {
			$this->page_title = $title;
		}

		/**
		 * Return page title with optional prefix/suffix
		 * @param  string $prefix    Prefix to prepend
		 * @param  string $suffix    Suffix to append
		 * @param  string $separator Separator character
		 * @return string            Formatted and escaped title
		 */
		function getPageTitle($prefix = '', $suffix = '', $separator = '-') {
			$ret = $this->page_title;
			if (! empty($prefix) ) {
				$ret = sprintf('%s %s %s', htmlspecialchars($prefix), $separator, $ret);
			}
			if (! empty($suffix) ) {
				$ret = sprintf('%s %s %s', $ret, $separator, htmlspecialchars($suffix));
			}
			return $ret;
		}

		/**
		 * Get the site name
		 * @param  boolean $echo Print the result?
		 * @return string        Site name
		 */
		function getSiteTitle($echo = false) {
			$ret = $this->site_title;
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Check if the current request was made via AJAX
		 * @return boolean Whether the request was made via AJAX or not
		 */
		function isAjaxRequest() {
			return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
		}

		/**
		 * Check if the current request was made via HTTPS
		 * @return boolean Whether the request was made via HTTPS or not
		 */
		function isSecureRequest() {
			$https = isset( $_SERVER['HTTPS'] ) ? $_SERVER['HTTPS'] : 'off';
			return ($https != '' && $https != 'off');
		}

		/**
		 * Add a new AJAX action and register its handler function
		 * @param string $action    Action slug
		 * @param string $functName Callback function name
		 */
		function addAjaxAction($action, $functName) {
			$this->actions[$action] = $functName;
		}

		/**
		 * Get registered plugins
		 * @return array Array of registered plugins
		 */
		function getPlugins() {
			return $this->plugins;
		}

		/**
		 * Hash the specified token
		 * @param  mixed  $action  Action name(s), maybe a single string or an array of strings
		 * @param  boolean $echo   Whether to output the resulting string or not
		 * @return string          The hashed token
		 */
		function hashToken($action, $echo = false) {
			if ( is_array($action) ) {
				$action_str = '';
				foreach ($action as $item) {
					$action_str .= $item;
				}
				$ret = md5($this->token_salt.$action_str);
			} else {
				$ret = md5($this->token_salt.$action);
			}
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Hash the specified password
		 * @param  string  $password 	Plain-text password
		 * @param  boolean $echo   		Whether to output the resulting string or not
		 * @return string          		The hashed password
		 */
		function hashPassword($password, $echo = false) {
			$ret = md5($this->pass_salt.$password);
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Validate the given token with the specified action
		 * @param  string $token  Hashed token
		 * @param  string $action Action name
		 * @return boolean        True if the token is valid, False otherwise
		 */
		function validateToken($token, $action) {
			$check = $this->haskToken($action);
			return ($token == $check);
		}

		/**
		 * Register a hook listener
		 * @param  string  $hook      Hook name
		 * @param  string  $functName Callback function name
		 * @param  boolean $prepend   Whether to add the listener at the beginning or the end
		 */
		function registerHook($hook, $functName, $prepend = false) {
			if (! isset( $this->hooks[$hook] ) ) {
				$this->hooks[$hook] = array();
			}
			if ($prepend) {
				array_unshift($this->hooks[$hook], $functName);
			} else {
				array_push($this->hooks[$hook], $functName);
			}
		}

		/**
		 * Execute a hook (run each listener incrementally)
		 * @param  string $hook   	Hook name
		 * @param  mixed  $params 	Parameter to pass to each callback function
		 * @return mixed          	The processed data or the same data if no callbacks were found
		 */
		function executeHook($hook, $param = '') {
			if ( isset( $this->hooks[$hook] ) ) {
				$hooks = $this->hooks[$hook];
				$ret = true;
				foreach ($hooks as $hook) {
					$ret = call_user_func($hook, $param);
				}
				return $ret;
			}
			return false;
		}

		/**
		 * Get the specified option from the current profile
		 * @param  string $key     Option name
		 * @param  string $default Default value
		 * @return mixed           The option value (array, string, integer, boolean, etc)
		 */
		function getOption($key, $default = '') {
			$ret = $default;
			if ( isset( $this->profile[$key] ) ) {
				$ret = $this->profile[$key];
			}
			return $ret;
		}

		/**
		 * Get the specified option from the global profile
		 * @param  string $key     Option name
		 * @param  string $default Default value
		 * @return mixed           The option value (array, string, integer, boolean, etc)
		 */
		function getGlobal($key, $default = '') {
			$ret = $default;
			if ( isset( $this->globals[$key] ) ) {
				$ret = $this->globals[$key];
			}
			return $ret;
		}

		/**
		 * Return the current database connection object
		 * @return object 			PDO instance for the current connection
		 */
		function getDatabase() {
			return $this->dbh;
		}

		/**
		 * Add a meta tag to the site
		 * @param string $name      Meta name
		 * @param string $content   Meta content (optional)
		 * @param string $attribute Attribute to use for 'name' (charset, etc)
		 */
		function addMeta($name, $content = '', $attribute = 'name') {
			$this->metas[$name] = array(
				'name' => $name,
				'content' => $content,
				'attribute' => $attribute
			);
		}

		/**
		 * Remove a meta tag from the site
		 * @param  string $name Meta name
		 * @return nothing
		 */
		function removeMeta($name) {
			unset( $this->metas[$name] );
		}

		/**
		 * Print the meta tags added to the site
		 * @return nothing
		 */
		function metaTags() {
			foreach ($this->metas as $meta) {
				echo( $meta['content'] ?
						"<meta {$meta['attribute']}=\"{$meta['name']}\" content=\"{$meta['content']}\">\n" :
						"<meta {$meta['attribute']}=\"{$meta['name']}\">\n"
					);
			}
		}

		/**
		 * Display a generic error message
		 * @param  string $message The error message
		 */
		function errorMessage($message) {
			$markup = '<!DOCTYPE html> <html lang="en"> <head> <meta charset="UTF-8"> <title>{$title}</title> <style> body { font-family: sans-serif; font-size: 14px; background: #F8F8F8; } div.center { width: 960px; margin: 0 auto; padding: 1px 0; } p.message { padding: 15px; border: 1px solid #DDD; background: #F1F1F1; color: #656565; } </style> </head> <body> <div class="center"> <p class="message">{$message}</p> </div> </body> </html>';
			$markup = str_replace('{$title}', $this->getSiteTitle(), $markup);
			$markup = str_replace('{$message}', $message, $markup);
			echo $markup;
			exit;
		}
	}
?>