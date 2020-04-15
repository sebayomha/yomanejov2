<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	require_once('authDB.php');
	require_once('utils.php');
	require_once('token.php');
	require_once('authGuard.php');

	//Obtengo la URL que se desea procesar.
	$utils = new Utils();
	$requestMethod = $utils->getUri();

	$method = $_SERVER['REQUEST_METHOD']; //Obtengo el METODO: PUT/GET/DELETE/POST.

	
	function validateRefreshToken() {
		$params = json_decode(file_get_contents('php://input'),true);
		$auth = new Auth();
		
		$validateRefreshTokenResult = $auth->validateRefreshToken($params['refreshToken'], $params['idUsuario']);
		
		if ($validateRefreshTokenResult) { //refresh token sigue siendo valido (no expiro)
			$token = new Token();

			$jwt = $token->generateToken($auth->generatePayload($params['idUsuario']), getPasswordById($params['idUsuario']));
			$data = (object) [
				'code' => 0,
				'jwt' => $jwt,
				'result' => 'Token actualizado correctamente'
			];
			echo json_encode($data);
		} else {
			header("HTTP/1.1 401 Unauthorized");
			exit;
		}
	}

	function login(){
		$params = json_decode(file_get_contents('php://input'),true);
		$auth = new Auth();

		$email = htmlspecialchars($params['email']);
		$password = htmlspecialchars($params['password']);

		if(isset($params) && !empty($params['email'] && !empty($params['password']))){
			$authResult = $auth->login($params);
			if ($authResult->code == 0) {
				echo json_encode($GLOBALS['utils']->getResponse(0, $authResult));	
			} else {
				echo json_encode($authResult);	
			}
		}else{
			echo json_encode($authResult);
		}
	}

	function firstPasswordChange() {
		$params = json_decode(file_get_contents('php://input'),true);
		$auth = new Auth();

		$email = htmlspecialchars($params['email']);
		$password = htmlspecialchars($params['password']);

		if(isset($params) && !empty($params['email'] && !empty($params['password']))){
			$authResult = $auth->firstPasswordChange($params);
			if ($authResult->code == 0) {
				echo json_encode($GLOBALS['utils']->getResponse(0, $authResult));	
			} else {
				echo json_encode($authResult);	
			}
		}else{
			echo json_encode($authResult);
		}
	}

	function logout(){
		$authGuard = new AuthGuard();
		$allowedAccessResult = $authGuard->allowedAccess();
		if ($allowedAccessResult != false && $allowedAccessResult != "expired") {
			$params = json_decode(file_get_contents('php://input'),true);
			$auth = new Auth();
	
			$idUsuario = htmlspecialchars($params);
	
			if(isset($idUsuario) && !empty($idUsuario)){
				$logoutResult = $auth->logout($idUsuario);
				if ($logoutResult) {
					echo json_encode($GLOBALS['utils']->getResponse(0, $logoutResult));	
				} else {
					echo json_encode($GLOBALS['utils']->getResponse(1, $logoutResult));	
				}
			}else{
				echo json_encode($GLOBALS['utils']->getResponse(2, 'Ingrese los campos'));	
			}
		} else {
			if ($allowedAccessResult == "expired") {
				header("HTTP/1.1 401 Token Expired");
				exit;
			} else {
				header("HTTP/1.1 401 Unauthorized");
				exit;
			}
		}
	}

	function getPasswordById($idUsuario) {
		$idUsuario = htmlspecialchars($idUsuario);
		$auth = new Auth();
		return $auth->getPasswordById($idUsuario);
	}

	function changePassword() {
		$authGuard = new AuthGuard();
		$allowedAccessResult = $authGuard->allowedAccess();
		if ($allowedAccessResult != false && $allowedAccessResult != "expired") {
			$params = json_decode(file_get_contents('php://input'),true);
			$auth = new Auth();
	
			$idUsuario = htmlspecialchars($params['idUsuario']);
			$oldPassword = htmlspecialchars($params['oldPassword']);
			$newPassword = htmlspecialchars($params['newPassword']);
	
			if(isset($idUsuario) && !empty($idUsuario) && isset($oldPassword) && isset($newPassword)){
				$changePasswordResult = $auth->changePassword($idUsuario, $oldPassword, $newPassword);
				if (is_object($changePasswordResult)) {
					echo json_encode($GLOBALS['utils']->getResponse(0, $changePasswordResult));	
				} else {
					echo json_encode($GLOBALS['utils']->getResponse(1, 'La nueva contraseña no puede ser igual a la anterior'));	
				}
			} else {
				echo json_encode($GLOBALS['utils']->getResponse(2, 'Ingrese los campos'));	
			}
		} else {
			if ($allowedAccessResult == "expired") {
				header("HTTP/1.1 401 Token Expired");
				exit;
			} else {
				header("HTTP/1.1 401 Unauthorized");
				exit;
			}
		}
	}

	switch ($method) {
		case 'POST':
	  	{
	    	switch($requestMethod){
	    		case 'auth':
	    			validateToken();
	    		break;
	    		case '/auth/login':
	    			login();
				break;
				case '/auth/refresh':
	    			validateRefreshToken();
				break;
				case '/auth/firstPasswordChange':
	    			firstPasswordChange();
				break;
				case '/auth/logout':
	    			logout();
				break;
				case '/auth/changePassword':
					changePassword();
				break;
	    		default:
	    		break;
	    	}
		}
	    break;
	  default:
	    break;
	}
?>