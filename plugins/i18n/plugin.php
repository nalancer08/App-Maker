<?php
	/**
	 * I18N plugin
	 * Provides basic internationalization services to Hummingbird.
	 * Version: 	1.0
	 * Author(s):	biohzrdmx <github.com/biohzrdmx>
	 * ToDo:		Maybe improve routing
	 * 				More helpers
	 */

	class I18N {
		protected $locales;
		protected $locale;

		/**
		 * Constructor
		 */
		function __construct() {
			global $site;
			# Initialize variables
			$this->locales = array();
			$locale = '';
			# Register router
			$site->addRoute('/:lang', 'I18N::getPage', true);
			$site->addRoute('/:lang/*params', 'I18N::getPage', true);
		}

		/**
		 * Handle a (possibly) localized route
		 * @param  mixed $params          Route or array
		 * @param  string $templates_dir  Templates dir
		 * @return boolean                TRUE if routing was successful, FALSE otherwise
		 */
		static function getPage($params, $templates_dir = '') {
			global $site;
			global $i18n;
			# We must check whether this is a localized route or not
			$lang = $params[1];
			if ( array_key_exists( $lang, $i18n->getLocales() ) ) {
				# A registered locale, strip the locale identifier and rebuild the route
				$page = '';
				for ($i = 2; $i < count($params); $i++) {
					$page .= sprintf('/%s', $params[$i]);
				}
				if ( empty($page) ) {
					$page = '/home';
				}
				# Override the current locale
				$i18n->setLocale($lang);
				$site->addBodyClass( sprintf('lang-%s', $lang) );
				# Call the base router again with the new route
				$site->matchRoute($page);
				return true;
			} else {
				# Otherwise just set the default locale slug
				$site->addBodyClass( sprintf('lang-%s', $i18n->getLocale() ) );
			}
			return false;
		}

		/**
		 * Register a new locale
		 * @param string $key         	Locale identifier (es, en, it, etc)
		 * @param string $translation 	The translation file
		 */
		function addLocale($key, $translation) {
			$ret = $this->loadLocale($translation);
			$this->locales[$key] = $this->loadLocale($translation);
		}

		/**
		 * Load a locale from a file
		 * @param  string $translation 	The translation file
		 * @return array              	The array of translation strings
		 */
		function loadLocale($translation) {
			return include($translation);
		}

		/**
		 * Set the current locale
		 * @param string $key 			Locale identifier
		 */
		function setLocale($key) {
			$this->locale = $key;
		}

		/**
		 * Get the current locale
		 * @return string  				The current locale identifier
		 */
		function getLocale() {
			$ret = $this->locale;
			return $ret;
		}

		/**
		 * Get the list of registered locales
		 * @return array 				List of registered locales
		 */
		function getLocales() {
			return $this->locales;
		}

		/**
		 * Get a localized URL
		 * @param  string  $path   		URL path
		 * @param  boolean $echo   		Whether to print the result or not
		 * @param  string  $locale 		Locale identifier to override the current locale
		 * @return string          		The well-formed URL
		 */
		function urlTo($path, $echo = false, $locale = '') {
			global $site;
			if ( empty($locale) ) {
				$locale = $this->getLocale();
			}
			$ret = $site->baseUrl( sprintf('/%s%s', $locale, $path) );
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Get specified translation
		 * @param  string $key Translation key
		 * @return string      			The specified translation or the key if it wasn't found
		 */
		function translate($key, $echo = true) {
			$ret = $key;
			if (! empty($this->locale) && isset( $this->locales[$this->locale][$key] ) ) {
				$ret = $this->locales[$this->locale][$key];
			}
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Get a translated selection box and optionally print it
		 * @param  string  $key   		Translation key
		 * @param  boolean $echo  		Whether to print the result or not
		 * @param  string  $sel   		The value of the selected item
		 * @param  array   $attrs 		Any extra attribute to add to the select tag
		 * @return string         		Translated select tag markup
		 */
		function select($key, $echo = true, $sel = false, $attrs = array()) {
			$options = $this->translate($key, false);
			if ( is_array($options) ) {
				$attr_text = '';
				foreach ($attrs as $attr => $value) {
					$attr_text .= sprintf(' %s="%s"', $attr, $value);
				}
				$ret = sprintf('<select%s>', $attr_text);
				foreach ($options as $option => $name) {
					$ret .= '<option '.($sel !== false && $option == $sel ? 'selected="selected" ' : '').'value="'.$option.'">'.$name.'</option>';
				}
				$ret .= '</select>';
			} else {
				$ret = $key;
			}
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}

		/**
		 * Get a translated list and optionally print it
		 * @param  string  $key   Translation key
		 * @param  boolean $echo  Whether to print the result or not
		 * @param  array   $attrs Any extra attribute to add to the list tag
		 * @param  array   $tags  Array with list tags (defaults to ['ul', 'li'])
		 * @return string         Translated select tag markup
		 */
		function lists($key, $echo = true, $tags = array('ul', 'li'), $attrs = array()) {
			$items = $this->translate($key, false);
			$attr_text = '';
			foreach ($attrs as $attr => $value) {
				$attr_text .= sprintf(' %s="%s"', $attr, $value);
			}
			$ret = sprintf('<'.$tags[0].'%s>', $attr_text);
			foreach ($items as $item) {
				$ret .= '<'.$tags[1].'>'.$item.'</'.$tags[1].'>';
			}
			$ret .= '</'.$tags[0].'>';
			if ($echo) {
				echo $ret;
			}
			return $ret;
		}
	}

	# Instantiate the plugin object
	$i18n = new I18N();
?>