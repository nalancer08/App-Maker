<?php

	/**
	 * Mailer
	 *
	 * A simple, extensible abstraction layer for mail sending in PHP
	 *
	 * This library sends mail through different 'providers', which add support for Mandrill, SMTP
	 * or any other transport you may need. You may event create your own providers!
	 *
	 * @author biohzrdmx <github.com/biohzrdmx>
	 */

	/**
	 * MailerProvider class
	 */
	abstract class MailerProvider {

		/**
		 * Process the send request, you must override this in your derived classes
		 * @param  object $message  An instance of MailerMessage
		 * @param  array  $options  Additional or provider-specific options
		 * @return boolean          True if the message was sent, False otherwise
		 */
		public abstract function send($message, $options = null);

	}

	/**
	 * MailerMessage class
	 */
	class MailerMessage {

		public $subject;
		public $from;
		public $to;
		public $contents;
		public $template;
		public $attachments;
		public $images;
		public $replacements;

		/**
		 * Default constructor
		 */
		function __construct() {
			$this->subject = '';
			$this->from = '';
			$this->to = '';
			$this->contents = '';
			$this->template = '';
			$this->attachments = array();
			$this->images = array();
		}

		/**
		 * MailerMessage factory class for chaining
		 * @return object The newly-created MailerMessage instance
		 */
		static function newInstance() {
			$new = new self();
			return $new;
		}

		/**
		 * Set message subject
		 * @param string $subject Message subject
		 */
		public function setSubject($subject) {
			$this->subject = $subject;
			return $this;
		}

		/**
		 * Set message sender
		 * @param array $from Array of (name => email) pairs or email addresses
		 */
		public function setFrom($from) {
			$this->from = $from;
			return $this;
		}

		/**
		 * Set message destination
		 * @param array $from Array of (name => email) pairs or email addresses
		 */
		public function setTo($to) {
			$this->to = $to;
			return $this;
		}

		/**
		 * Set email contents
		 * @param string $contents Message contents (html or plain-text)
		 */
		public function setContents($contents) {
			$this->contents = $contents;
			return $this;
		}

		/**
		 * Set template path, expects a file-system path
		 * @param string $template Path to the template file
		 */
		public function setTemplate($template) {
			$this->template = $template;
			return $this;
		}

		/**
		 * Set message attachments
		 * @param array $attachments Array of (name => path) pairs
		 */
		public function setAttachments($attachments) {
			$this->attachments = $attachments;
			return $this;
		}

		/**
		 * Set message images
		 * @param array $images Array of (name => path) pairs
		 */
		public function setImages($images) {
			$this->images = $images;
			return $this;
		}

		/**
		 * Set message replacements for your template using shortcodes
		 * @param array $replacements Array of (shortcode => value) pairs
		 */
		public function setReplacements($replacements) {
			$this->replacements = $replacements;
			return $this;
		}

	}

	/**
	 * Mailer class
	 */
	class Mailer {

		/**
		 * Send a message using the speficied provider
		 * @param  object $message  The MailerMessage instance
		 * @param  string $provider The provider name, such as 'mandrill' or 'smtp'
		 * @param  array  $options  Provider-specific options
		 * @return boolean          True if the message was sent, False otherwise
		 */
		static function send($message, $provider, $options = array()) {
			global $site;
			$ret = false;
			$provider = ucfirst($provider);
			$class_name = "{$provider}MailerProvider";
			if ( class_exists($class_name) ) {
				$provider = new $class_name;
				$ret = $provider->send($message, $options);
			} else {
				$site->errorMessage('');
			}
			return $ret;
		}

	}

?>