<?php
	class Utils{

		public function __construct(){}

		public function getResponse($code, $data){
			$response = (object) [
				'code' => $code,
				'data' => $data
			];

			return $response;
		}    

		public function getUri() {
			$uri;

			$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
			$uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));
			if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));
			$uri = '/' . trim($uri, '/');

			return $uri;
		}

	}
?>