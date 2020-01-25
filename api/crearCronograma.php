<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	require_once('crearCronogramaDB.php');
	//Va a ser utilizada cuando existan sesiones
	//require_once('token.php');
	require_once('utils.php');

	$utils = new Utils();
	$requestMethod = $utils->getUri();

	$method = $_SERVER['REQUEST_METHOD']; //Obtengo el METODO: PUT/GET/DELETE/POST.

	//Defino todas las funciones que voy a utilizar
	function calcularCronograma(){

		$cantClases = 4;

		$fechaInicio = '2020-01-07';
	
		$disponibilidad = [
			'Monday' => ['09:00', '12:00', '15:00', '19:00'], 
			'Tuesday' => null,
			'Wednesday' => ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00','19:00'],
			'Thursday' => ['08:00', '09:00', '10:00', '11:00', '12:00', '17:00', '18:00', '19:00'],
			'Friday' => ['09:00', '12:00', '15:00', '19:00'],
			'Saturday' => null,
			'Sunday' => ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00','19:00']
		];
	
		$direccion = [ (object) [
				'street' => '123',
				'diag' => false,
			],
			(object) [
				'street_a' => '47',
				'diag' => false,
			],
			(object) [
				'street' => '48',
				'diag' => false,
			],
			(object) [
				'altitud' => '755'
			],
			(object) [
				'city' => 'Ensenada'
			]
		];

		$params = json_decode(file_get_contents('php://input'), true);

		$cronograma = new Cronograma();
		$cronogramaResultante = $cronograma->calcularCronograma($cantClases, $disponibilidad, $direccion, $fechaInicio);

		if (!empty($cronogramaResultante)) {
			echo json_encode($GLOBALS['utils']->getResponse(0, $cronogramaResultante));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al calcular el cronograma, por favor vuelva a intentar."));
		}
		
	}

	switch ($method) {
		case 'GET': {
		  //Obtengo la URL Final para saber cual accion ejecutar.
		  $id = substr($requestMethod, strrpos($requestMethod, '/') + 1);
		  switch ($requestMethod){
			  case '/calcularCronograma':
				calcularCronograma();
				break;
			  default:
				echo "podriamos agregar otra consulta mas";
				break;
		  }	    	
		}  
		break;
	  }
?>