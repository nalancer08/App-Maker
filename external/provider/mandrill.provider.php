<?php

	/**
	 * MandrillMailerProvider class
	 * @uses Mandrill PHP SDK - Install the Mandrill PHP SDK into your project's lib/mandrill folder
	 */
	class MandrillMailerProvider extends MailerProvider {

		/**
		 * Process the send request, you must override this in your derived classes
		 * @param  object $message  An instance of MailerMessage
		 * @param  array  $options  Additional or provider-specific options
		 * @return boolean          True if the message was sent, False otherwise
		 */
		public function send($message, $options = null) {
			global $site;
			$ret = false;
			# Build arrays
			$to = array();
			$global_vars = get_item($options, 'vars', array());
			$vars = array();
			if (! is_array($message->to) ) {
				$message->to = array($message->to);
			}
			if ( $message->to ) {
				foreach ($message->to as $key => $value) {
					$name = '';
					$email = '';
					$usr_vars = array();
					$extra = array();
					# Check destination type
					if ( is_object($value) ) {
						# Item is an user object
						$email = $value->email;
						$name = "{$value->first_name} {$value->last_name}";
						# Include additional merge vars
						if ( isset($value->vars) ) {
							foreach ($value->vars as $code => $content) {
								$extra[] = array(
									'name' => $code,
									'content' => $content
								);
							}
						}
					} else {
						# Item is an 'email' => 'name' array
						$email = $key;
						$name = $value;
					}
					# Add destination
					$to[] = array(
						'email' => $email,
						'name' => $name,
						'type' => 'to'
					);
					# Build merge vars
					$usr_vars = array(
						array(
							'name' => 'email',
							'content' => $email
						),
						array(
							'name' => 'name',
							'content' => $name
						)
					);
					$usr_vars = array_merge($usr_vars, $extra);
					# Add merge vars
					$vars[] = array(
						'rcpt' => $email,
						'vars' => $usr_vars
					);
				}
			}
			$subject = $message->subject;
			$from_email = key($message->from);
			$from_name = $message->from[$from_email];
			$headers = get_item($options, 'headers', array());
			$contents = $message->contents;
			# Load template
			$template = $message->template;
			if ($template) {
				$html = file_get_contents( $template );
				$html = str_replace('%email-site%', $site->urlTo('/'), $html);
				$html = str_replace('%email-body%', $contents, $html);

				if($message->replacements) {
					foreach ($message->replacements as $shortcode => $value) {
						$html = str_replace($shortcode, $value, $html);
					}
				}
			} else {
				$html = $contents;
			}
			# Attachments
			$attachments = array();
			foreach ($message->attachments as $name => $attachment) {
				# File must exist
				if (! file_exists($attachment) ) continue;
				# Determine (guess by extension) mime type
				$ext = strtolower( substr( $attachment, strrpos($attachment, '.') + 1 ) );
				switch ($ext) {
					case 'gif':
					case 'png':
						$mime = "image/{$ext}";
					case 'jpg':
					case 'jpeg':
						$mime = 'image/jpeg';
						break;
					case 'mpeg':
					case 'mp4':
					case 'ogg':
					case 'webm':
						$mime = "video/{$ext}";
						break;
					case 'pdf':
					case 'zip':
						$mime = "application/{$ext}";
						break;
					case 'csv':
					case 'xml':
						$mime = "text/{$ext}";
						break;
					default:
						$mime = 'application/octet-stream';
				}
				# Add to attachments array
				$attachments[] = array(
					'type' => $mime,
					'name' => $name,
					'content' => base64_encode( file_get_contents($attachment) )
				);
			}
			# Images
			$images = array();
			foreach ( $message->images as $name => $image) {
				# File must exist
				if (! file_exists($image) ) continue;
				# Determine (guess by extension) mime type
				$ext = strtolower( substr( $image, strrpos($image, '.') + 1 ) );
				switch ($ext) {
					case 'gif':
					case 'png':
						$mime = "image/{$ext}";
					case 'jpg':
					case 'jpeg':
						$mime = 'image/jpeg';
						break;
				}
				# Add to images array
				$images[] = array(
					'type' => $mime,
					'name' => $name,
					'content' => base64_encode( file_get_contents($image) )
				);
			}
			# Include library
			include_once $site->baseDir('/lib/Mandrill/Mandrill.php');
			#
			try {
				$mandrill = new Mandrill( get_item($options, 'key', '') );
				$message = array(
					'html' => $html,
					'text' => strip_tags($contents),
					'subject' => $subject,
					'from_email' => $from_email,
					'from_name' => $from_name,
					'to' => $to,
					'headers' => $headers,
					'attachments' => $attachments,
					'important' => true,
					'track_opens' => true,
					'track_clicks' => true,
					'merge' => true,
					'preserve_recipients' => false,
					'global_merge_vars' => $global_vars,
					'merge_vars' => $vars,
					'inline_css' => true,
					'images' => $images
				);
				# Use SSL always
				curl_setopt($mandrill->ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($mandrill->ch, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($mandrill->ch, CURLOPT_CAINFO, dirname(__FILE__) . '/cacert.pem');
				#
				$async = get_item($options, 'async', false);
				$ip_pool = get_item($options, 'pool', 'Main Pool');
				$send_at = '';
				$result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);
				$ret = true;
			} catch(Mandrill_Error $e) {
				echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
				throw $e;
			}
			return $ret;
		}

	}

?>