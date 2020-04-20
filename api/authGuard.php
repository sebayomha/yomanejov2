<?php
	require_once('authDB.php');
	require_once('token.php');

	Class AuthGuard {
		
		function allowedAccess() {
			$jwt = false;
			if($_SERVER['HTTP_AUTHORIZATION']) {
				preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches);
				$idUsuario = $_SERVER['HTTP_USERID'];
				$token = new Token();
				$auth = new Auth();
				$pass = $auth->getPasswordById($idUsuario);
				$jwt = $token->decodeToken($matches[1], $pass);
			}
			return $jwt;
		}

		function allowedAccessByToken($tokenJWT) {
			$jwt = false;
			if($tokenJWT) {
				try {
					$tokenParts = explode(".", $tokenJWT); 
					$tokenPayload = json_decode(base64_decode($tokenParts[1]));
					$idUsuario = $tokenPayload->idUsuario;
					$token = new Token();
					$auth = new Auth();
					$pass = $auth->getPasswordById($idUsuario);
					$jwt = $token->decodeToken($tokenJWT, $pass);
				} catch (Exception $e) {
					return false;
				}
			}
			return $jwt;
		}
	}
?>