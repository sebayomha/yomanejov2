<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	require_once('crearCronogramaDB.php');
	iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
	//Va a ser utilizada cuando existan sesiones
	//require_once('token.php');
	require_once('utils.php');

	$utils = new Utils();
	$requestMethod = $utils->getUri();

	$method = $_SERVER['REQUEST_METHOD']; //Obtengo el METODO: PUT/GET/DELETE/POST.

	//Defino todas las funciones que voy a utilizar
	function calcularCronograma(){

		$cantClases = (int) $_GET['cantClases'];
		$fechaInicio = $_GET['fechaInicio'];
		$direccion = json_decode($_GET['direccion'], true);
		$direccion_alt = json_decode($_GET['direccion_alt'], true);
		$disponibilidad = json_decode($_GET['disponibilidad'], true);
		$excepciones = json_decode($_GET['excepciones'], true);

		$resDisponibilidad = [];
		$hayDireccionAlternativa = false;
		foreach ($disponibilidad as $dia) {
			$resDisponibilidad[$dia['name_day']] = null;
			$resOptions[$dia['name_day']] = [];

			if($dia['option'][0]['scheduleSend'] != null) {
				$resDisponibilidad[$dia['name_day']] = [];
				$options = [];
				foreach ($dia['option'] as $option) {
					foreach ($option['scheduleSend'] as $schedule) {
						array_push($options, $schedule);
					}
					if ($option['dir_alt'] == true) {
						$hayDireccionAlternativa = true;
					}
					array_push($resOptions[$dia['name_day']], $option);
				}
				$resDisponibilidad[$dia['name_day']] = $options;
			} else {
				$resDisponibilidad[$dia['name_day']] = null;
			}		
		}

		if (containsOnlyNull($resDisponibilidad)) {
			echo json_encode($GLOBALS['utils']->getResponse(3, "Debe completar al menos un dia"));
		} else {
			$resExcepciones = [];
			$resExcepcionesOptions = [];
			foreach ($excepciones as $excepcion) {
				$resExcepciones[$excepcion['date_string']] = [];
				$resExcepcionesOptions[$excepcion['date_string']] = [];
				$options = [];
				foreach ($excepcion['horarios'] as $horario) {
					foreach ($horario['horariosTotales'] as $schedule) {
						array_push($options, $schedule);
					}
					if ($horario['dir_alt'] == true) {
						$hayDireccionAlternativa = true;
					}
					array_push($resExcepcionesOptions[$excepcion['date_string']], $horario);
				}
				$objetoExcepcion = (object) [
					'options' => $options,
					'no_puede' => $excepcion['no_puede']
				];

				$resExcepciones[$excepcion['date_string']] = $objetoExcepcion;
			}

			$cronograma = new Cronograma();
			$cronogramaResultante = $cronograma->calcularCronograma($cantClases, $resDisponibilidad, $direccion, $fechaInicio, $resExcepciones, $direccion_alt, $hayDireccionAlternativa, $resOptions, $resExcepcionesOptions);

			if (is_array($cronogramaResultante)) {
				if (!empty($cronogramaResultante)) {
					echo json_encode($GLOBALS['utils']->getResponse(0, $cronogramaResultante));
				} else {
					echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al calcular el cronograma, por favor vuelva a intentar."));
				}
			} else {
				if ($cronogramaResultante == 2) {
					echo json_encode($GLOBALS['utils']->getResponse(2, "La dirección ingresada no corresponde a una zona de trabajo. Por favor, ingrese un punto de encuentro"));
				}
			}
		}
		
	}

	function guardarCronograma() {
		$post = json_decode(file_get_contents('php://input'));

		$selectedOptions = $post[0]->selected_options;
		$studentName = $post[1]->student_name;
		$student_phone = $post[2]->student_phone;
		$address = $post[3]->address;
		$address_alt = $post[4]->address_alternative;
		$disponibilidad = $post[5]->disponibilidad;
		$excepciones = $post[6]->excepciones;

		$cronograma = new Cronograma();
		$resultGuardarCronograma = $cronograma->guardarCronograma($selectedOptions, $studentName, $student_phone, $address, $address_alt, $disponibilidad, $excepciones);
		if ($resultGuardarCronograma) {
			echo json_encode($GLOBALS['utils']->getResponse(0, 'Registros guardados exitosamente'));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al guardar algún registro, vuelva a intentar más tarde."));
		}
	}

	function obtenerCronogramasPendientesDeConfirmar() {
		$cronograma = new Cronograma();
		$resultCronograma = $cronograma->obtenerCronogramasPendientesDeConfirmar();
		if ($resultCronograma == 1) {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al obtener los cronogramas, vuelva a intentar más tarde."));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(0, $resultCronograma));	
		}
	}

	function confirmarCronograma() {
		$post = json_decode(file_get_contents('php://input'));
		$idCronograma = $post->params->idCronograma;
		$idAlumno = $post->params->idAlumno;
		$cronograma = new Cronograma();
		$resultCronogramaUpdate = $cronograma->confirmarCronograma($idCronograma, $idAlumno);
		if ($resultCronogramaUpdate) {
			echo json_encode($GLOBALS['utils']->getResponse(0, 'El cronograma ha sido confirmado exitosamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}
	}

	function containsOnlyNull($input) {
		return empty(array_filter($input, function ($a) { return $a !== null;}));
	}

	switch ($method) {
		case 'GET': {
		  	//Obtengo la URL Final para saber cual accion ejecutar.
		  	switch ($requestMethod){
				case '/calcularCronograma':
					calcularCronograma();
					break;
				case '/calcularCronograma/cronogramasPendientes':
					obtenerCronogramasPendientesDeConfirmar();
					break;
			  	default:
					echo "podriamos agregar otra consulta mas";
					break;
		  	}	    
			break;	
		}

		case 'POST': {
			//Obtengo la URL Final para saber cual accion ejecutar.
			switch ($requestMethod){
				case '/calcularCronograma/guardar':
					guardarCronograma();
					break;
				case '/calcularCronograma/confirmarCronograma':
					confirmarCronograma();
					break;
				default:
					echo "podriamos agregar otra consulta mas";
					break;
			}
			break;    	
		  }  
	  }
?>