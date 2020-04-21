<?php
	require_once('utils.php');
	require_once('connectDB.php');

	$utils = new Utils();
	$requestMethod = $utils->getUri();

	$method = $_SERVER['REQUEST_METHOD']; //Obtengo el METODO: PUT/GET/DELETE/POST.
	
	function pushSubscriber() {
		$post = json_decode(file_get_contents('php://input'));
		$idUsuario = $_SERVER['HTTP_USERID'];
		$endpoint = htmlspecialchars($post->endpoint);
		$p256dh = htmlspecialchars($post->keys->p256dh);
		$auth = htmlspecialchars($post->keys->auth);

        $db = new ConnectionDB();
        $conn = $db->getConnection();

	    $state = $conn->prepare('INSERT INTO usuariosuscripcion (idUsuario, endpoint, p256dh, auth) VALUES (?,?,?,?)');
	    $state->bind_param('ssss', $idUsuario, $endpoint, $p256dh, $auth);
	    if ($state->execute()) {
	    	echo json_encode($GLOBALS['utils']->getResponse(0, 'Suscripto correctamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}
	}

	switch ($method) {
		case 'POST': {
			switch ($requestMethod){
				case '/notifications/pushSubscriber':
					pushSubscriber();
				break;
			}
			break;    	
		}
	}
	
	
?>