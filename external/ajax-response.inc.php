<?php

	class AjaxResponse {
		public $result;
		public $status;

		function __construct() {
			$this->result = 'error';
			$this->status = 200;
		}

		function __toString() {
			return json_encode($this);
		}

		function respond() {
			header(":", true, $this->status);
			header('Content-Type: application/json');
			echo $this;
		}
	}

?>