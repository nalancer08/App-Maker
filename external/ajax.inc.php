<?php
	/**
	 * ajax.inc.php
	 * Add your AJAX actions here
	 */

	# Sample AJAX action ---------------------------------------------------------------------------
	function ajax_contacto() {

		global $site;

		$nombre = get_item($_POST, 'nombre');
		$email = get_item($_POST, 'email');
		$celular = get_item($_POST, 'celular');
		$mensaje = get_item($_POST, 'mensaje');

		# Respuesta AJAX
		$ret = new AjaxResponse();

		# Include the library and at least one provider
		include $site->baseDir('/external/mailer.inc.php');
		include $site->baseDir('/external/provider/mandrill.provider.php');
		include $site->baseDir('/external/provider/smtp.provider.php');

		# Create a MailerMessage object and set the subject, from, to, contents, attachments, etc.
		$message = MailerMessage::newInstance()
			->setSubject('Contacto')
			->setFrom( array('no-reply@elchangodelaweb.com' => 'WebChimp Academy') )
			->setTo( array('soporte@thewebchi.mp' => 'Soporte WebChimp') )
			->setContents(
					'<h2>Hola, tienes un mensaje de contacto de tu sitio'.$site->urlTo('/').'</h2>'.
					'<ul>'.
						'<li>Nombre <strong>'.$nombre.'</strong></li>'.
						'<li>Correo Electrónico <strong>'.$email.'</strong></li>'.
						'<li>Correo Electrónico <strong>'.$celular.'</strong></li>'.
						'<li>mensaje <strong>'.$mensaje.'</strong></li>'.
					'</ul>'
				);
		$options = array(
			'key' => '2723e0df-e759-49a7-970e-eb2de33bd9f2'
		);
		if (Mailer::send($message, 'mandrill', $options)){
			$ret->result = 'success';
			$ret->message = 'Gracias por sus comentarios';
		} else {
			$ret->message = 'Ha ocurrido un error al enviar tu mensaje, por favor intenta nuevamente';
		}

		$ret->respond();
		exit;
	}
	$site->addAjaxAction('contacto', 'ajax_contacto');
?>