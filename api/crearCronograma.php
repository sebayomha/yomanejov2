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
		$post = json_decode(file_get_contents('php://input'));

		$post = $post->params;
		$cantClases = (int) $post->cantClases;
		$fechaInicio = $post->fechaInicio;
		$direccion = json_decode($post->direccion, true);
		$direccion_alt = json_decode($post->direccion_alt, true);
		$disponibilidad = json_decode($post->disponibilidad, true);
		$excepciones = json_decode($post->excepciones, true);

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

		$selectedOptions = $post[1]->selected_options;
		$studentName = $post[3]->student_name;
		$student_phone = $post[4]->student_phone;
		$address = $post[6]->address;
		$address_alt = $post[8]->address_alternative;
		$disponibilidad = $post[10]->disponibilidad;
		$excepciones = $post[12]->excepciones;

		$cronograma = new Cronograma();
		$resultGuardarCronograma = $cronograma->guardarCronograma($selectedOptions, $studentName, $student_phone, $address, $address_alt, $disponibilidad, $excepciones);
		if ($resultGuardarCronograma != false) {
			echo json_encode($GLOBALS['utils']->getResponse(0, $resultGuardarCronograma));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al guardar algún registro, vuelva a intentar más tarde."));
		}
	}

	function actualizarCronogramaPendiente() {
		$post = json_decode(file_get_contents('php://input'));
		
		$idCronograma = $post[0]->idCronograma;
		$selectedOptions = $post[1]->selected_options;
		$idAlumno = $post[2]->idAlumno;
		$studentName = $post[3]->student_name;
		$student_phone = $post[4]->student_phone;
		$idDireccionPrincipal = $post[5]->idDireccionPrincipal;
		$address = $post[6]->address;
		$idDireccionAlternativa = $post[7]->idDireccionAlternativa;
		$address_alt = $post[8]->address_alternative;
		$idDisponibilidad = $post[9]->idDisponibilidad;
		$disponibilidad = $post[10]->disponibilidad;
		$idExcepciones = $post[11]->idExcepciones;
		$excepciones = $post[12]->excepciones;

		$cronograma = new Cronograma();
		$resultActualizarCronogramaPendiente = $cronograma->actualizarCronogramaPendiente($idCronograma, $selectedOptions, $idAlumno, $studentName, $student_phone, $idDireccionPrincipal, $address, $idDireccionAlternativa, $address_alt, $idDisponibilidad, $disponibilidad, $idExcepciones, $excepciones);
		if ($resultActualizarCronogramaPendiente != false) {
			echo json_encode($GLOBALS['utils']->getResponse(0, $resultActualizarCronogramaPendiente));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al actualizar algún registro, vuelva a intentar más tarde."));
		}	
	}

	function obtenerCronogramas() {
		$cronograma = new Cronograma();
		$resultCronograma = $cronograma->obtenerCronogramas();
		if (!is_object($resultCronograma)) {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al obtener los cronogramas, vuelva a intentar más tarde."));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(0, $resultCronograma));	
		}
	}

	function confirmarCronograma() {
		$post = json_decode(file_get_contents('php://input'));
		$idCronograma = $post->params->idCronograma;
		$idAlumno = $post->params->idAlumno;
		$clases = $post->params->clases;
		$documento = $post->params->documento;
		$direccionFisica = $post->params->direccionFisicaInformation;

		$cronograma = new Cronograma();
		$resultCronogramaUpdate = $cronograma->confirmarCronograma($idCronograma, $idAlumno, $direccionFisica, $clases, $documento);
		if (!is_array($resultCronogramaUpdate)) {
			if ($resultCronogramaUpdate) {
				echo json_encode($GLOBALS['utils']->getResponse(0, 'El cronograma '.$idCronograma.' ha sido confirmado exitosamente'));	
			} else {
				echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
			}
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(2, $resultCronogramaUpdate));
		}
	}

	function cancelarCronograma() {
		$post = json_decode(file_get_contents('php://input'));
		$idCronograma = $post->params->idCronograma;
		$idAlumno = $post->params->idAlumno;
		$cronograma = new Cronograma();
		$resultCronogramaCancelar = $cronograma->cancelarCronograma($idCronograma, $idAlumno);
		if ($resultCronogramaCancelar) {
			echo json_encode($GLOBALS['utils']->getResponse(0, 'El cronograma ha sido eliminado exitosamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}
	}

	function cancelarCronogramaActivo() {
		$post = json_decode(file_get_contents('php://input'));
		$idCronograma = $post->params->idCronograma;
		$idAlumno = $post->params->idAlumno;
		$motivoBaja = $post->params->motivoBaja;
		$cronograma = new Cronograma();
		$resultCronogramaCancelar = $cronograma->cancelarCronogramaActivo($idCronograma, $idAlumno, $motivoBaja);
		if ($resultCronogramaCancelar) {
			echo json_encode($GLOBALS['utils']->getResponse(0, 'El cronograma ha sido cancelado exitosamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}
	}

	function obtenerClasesPorFecha() {
		$cronograma = new Cronograma();
		echo json_encode($GLOBALS['utils']->getResponse(0, $cronograma->obtenerClasesPorFecha($_GET['fecha'])));
	}

	function containsOnlyNull($input) {
		return empty(array_filter($input, function ($a) { return $a !== null;}));
	}

	switch ($method) {
		case 'GET': {
		  	//Obtengo la URL Final para saber cual accion ejecutar.
		  	switch ($requestMethod){
				case '/calcularCronograma/cronogramasPendientes':
					obtenerCronogramas();
					break;
				case '/calcularCronograma/obtenerClasesPorFecha':
					obtenerClasesPorFecha();
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
				case '/calcularCronograma':
					calcularCronograma();
					break;
				case '/calcularCronograma/guardar':
					guardarCronograma();
					break;
				case '/calcularCronograma/confirmarCronograma':
					confirmarCronograma();
					break;
				case '/calcularCronograma/cancelarCronograma':
					cancelarCronograma();
					break;
				case '/calcularCronograma/actualizarCronogramaPendiente':
					actualizarCronogramaPendiente();
					break;
				case '/calcularCronograma/cancelarCronogramaActivo':
					cancelarCronogramaActivo();
				break;
				default:
					echo "podriamos agregar otra consulta mas";
					break;
			}
			break;    	
		  }  
	  }
?>