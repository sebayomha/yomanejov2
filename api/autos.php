<?php
	require_once('autosDB.php');
	iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
	//Va a ser utilizada cuando existan sesiones
	//require_once('token.php');
	require_once('utils.php');

	$utils = new Utils();
	$requestMethod = $utils->getUri();

	$method = $_SERVER['REQUEST_METHOD']; //Obtengo el METODO: PUT/GET/DELETE/POST.
	
	function obtenerAutos() {
		$autos = new Auto();
		$resultAuto = $autos->obtenerAutos();
		echo json_encode($GLOBALS['utils']->getResponse(0, $resultAuto));	
	}

	function obtenerZonas() {
		$zonas = new Auto();
		$resultZona = $zonas->obtenerZonas();
		echo json_encode($GLOBALS['utils']->getResponse(0, $resultZona));	
	}

	function crearAuto() {

		$post = json_decode(file_get_contents('php://input'));
		$zonaAuto = $post->zonaDeAuto;
		$patenteAuto = $post->patenteDeAuto;
		$modeloAuto = $post->modeloDeAuto;
		$colorAuto = $post->colorDeAuto;
		$dispoAuto = $post->dispoDeAuto;
		$descripAuto = $post->descripDeAuto;

		$auto = new Auto();
		$resultCrearAuto = $auto->crearAuto($zonaAuto, $patenteAuto, $dispoAuto, $descripAuto, $modeloAuto, $colorAuto);
		if ($resultCrearAuto == true) {
			echo json_encode($GLOBALS['utils']->getResponse(0, 'Auto creado correctamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}

	}

	function modificarAuto() {

		$post = json_decode(file_get_contents('php://input'));

		$idAuto = $post->idDeAuto;
		$zonaAuto = $post->zonaDeAuto;
		$patenteAuto = $post->patenteDeAuto;
		$modeloAuto = $post->modeloDeAuto;
		$colorAuto = $post->colorDeAuto;
		$dispoAuto = $post->dispoDeAuto;
		$descripAuto = $post->descripDeAuto;

		$auto = new Auto();
		$resultCrearAuto = $auto->modificarAuto($idAuto, $zonaAuto, $patenteAuto, $dispoAuto, $descripAuto, $modeloAuto, $colorAuto);
		if ($resultCrearAuto == true) {
			echo json_encode($GLOBALS['utils']->getResponse(0, 'Auto creado correctamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}

	}

	switch ($method) {
		case 'GET': {
		  	//Obtengo la URL Final para saber cual accion ejecutar.
		  	switch ($requestMethod){
				case '/autos':
					obtenerAutos();
					break;
				case '/autos/zonas':
					obtenerZonas();
					break;
		  	}	    
			break;	
		} 
		case 'POST': {
			//Obtengo la URL Final para saber cual accion ejecutar.
			switch ($requestMethod){
				case '/autos/crear':
					crearAuto();
					break;
				case '/autos/modificar':
					modificarAuto();
					break;
				case '/autos/bajar':
					bajarAuto();
					break;
			}
			break;    	
		  }
	}
	
?>