<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	require_once('authDB.php');
	require_once('token.php');

	Class AuthGuard {
		
		function allowedAccess() {
			$jwt = false;
			if(array_key_exists('authorization',getallheaders()) && getallheaders()['authorization'] != false) {
				preg_match('/Bearer\s(\S+)/', getallheaders()['authorization'], $matches);
				$idUsuario = getallheaders()['userid'];
				$token = new Token();
				$auth = new Auth();
				$pass = $auth->getPasswordById($idUsuario);
				$jwt = $token->decodeToken($matches[1], $pass);
			}
			return $jwt;
		}
	}
?>