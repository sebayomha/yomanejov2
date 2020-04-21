<?php
	require_once('crearCronogramaDB.php');
	require_once('utils.php');
	require_once('token.php');
	require_once('authGuard.php');

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

	function obtenerClasesActivasCronograma() {
		$post = json_decode(file_get_contents('php://input'));

		$post = $post->params;
		$idCronograma = (int) $post->idCronograma;
		$fechaInicio = $post->fechaInicio;
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
			$clasesActivas = $cronograma->obtenerClasesActivasCronograma($idCronograma, $resDisponibilidad, $fechaInicio, $excepciones, $resOptions, $resExcepcionesOptions);

			echo json_encode($GLOBALS['utils']->getResponse(0, $clasesActivas));
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

		if (!$post[13]->idClasesModificadas) {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Para editar debe modificar alguna de las clases."));
		} else {
			$cronograma = new Cronograma();
			$resultActualizarCronogramaPendiente = $cronograma->actualizarCronogramaPendiente($idCronograma, $selectedOptions, $idAlumno, $studentName, $student_phone, $idDireccionPrincipal, $address, $idDireccionAlternativa, $address_alt, $idDisponibilidad, $disponibilidad, $idExcepciones, $excepciones);
			if ($resultActualizarCronogramaPendiente != false) {
				echo json_encode($GLOBALS['utils']->getResponse(0, $resultActualizarCronogramaPendiente));
			} else {
				echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al actualizar algún registro, vuelva a intentar más tarde."));
			}
		}	
	}

	function actualizarCronogramaActivo() {
		$post = json_decode(file_get_contents('php://input'));
		
		$idCronograma = $post[0]->idCronograma;
		$selectedOptions = $post[1]->selected_options;
		$idAlumno = $post[2]->idAlumno;
		$idDireccionPrincipal = $post[5]->idDireccionPrincipal;
		$address = $post[6]->address;
		$idDireccionAlternativa = $post[7]->idDireccionAlternativa;
		$address_alt = $post[8]->address_alternative;
		$idDisponibilidad = $post[9]->idDisponibilidad;
		$disponibilidad = $post[10]->disponibilidad;
		$idExcepciones = $post[11]->idExcepciones;
		$excepciones = $post[12]->excepciones;

		if (!$post[13]->idClasesModificadas) {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Para editar debe modificar alguna de las clases."));
		} else {
			$idClasesModificadas = $post[13]->idClasesModificadas;

			$cronograma = new Cronograma();
			$resultActualizarCronogramaActivo = $cronograma->actualizarCronogramaActivo($idCronograma, $idClasesModificadas, $selectedOptions, $idAlumno, $idDireccionPrincipal, $address, $idDireccionAlternativa, $address_alt, $idDisponibilidad, $disponibilidad, $idExcepciones, $excepciones);
			if ($resultActualizarCronogramaActivo != false) {
				echo json_encode($GLOBALS['utils']->getResponse(0, $resultActualizarCronogramaActivo));
			} else {
				echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al actualizar algún registro, vuelva a intentar más tarde."));
			}
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

	function obtenerClasesDisponiblesParaAlumno() {
		$post = json_decode(file_get_contents('php://input'));

		$post = $post->params;
		$idAlumno = (int) $post;

		$cronograma = new Cronograma();
		$resultObject = $cronograma->generarSearch($idAlumno);
		$fechasEnUso = $cronograma->obtenerFechasEnUsoParaCronograma($idAlumno);
		$resultBusqueda = calcularCronogramaParametros($resultObject['searchResult']['lessons'], $resultObject['searchResult']['date'], $resultObject['searchResult']['address'], $resultObject['searchResult']['address_alternative'], $resultObject['searchResult']['dates_times'], $resultObject['excepciones']);
		$filtroFechas = array_diff_key(array_column($resultBusqueda, null, 'fecha'), array_flip($fechasEnUso));

		if (!empty($filtroFechas)) {
			echo json_encode($GLOBALS['utils']->getResponse(0, $filtroFechas));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Ocurrió un error al calcular el cronograma, por favor vuelva a intentar."));
		}
	}
		

	function calcularCronogramaParametros($cantClases, $fechaInicio, $direccion, $direccion_alt, $disponibilidad, $excepciones) {
		$resDisponibilidad = [];
		$hayDireccionAlternativa = false;
		foreach ($disponibilidad as $dia) {
			$resDisponibilidad[$dia['name_day']] = null;
			$resOptions[$dia['name_day']] = [];

			if($dia['option'] != null) {
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

			return $cronogramaResultante;
		}		
	}

	function cancelarClase() {
		$post = json_decode(file_get_contents('php://input'));

		$post = $post->params;
		$idClase = (int) $post->idClase;
		$motivoCancelacion = $post->motivoCancelacion;
		$cronograma = new Cronograma();
		$cancelarClaseResult = $cronograma->cancelarClase($idClase, $motivoCancelacion);

		if ($cancelarClaseResult == 0) {
			echo json_encode($GLOBALS['utils']->getResponse(0, "La clase se ha cancelado exitosamente"));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Lo lamentamos, ha ocurrido un error."));
		}
	}

	function reactivarClase() {
		$post = json_decode(file_get_contents('php://input'));

		$post = $post->params;
		$idClase = (int) $post->idClase;
		$cronograma = new Cronograma();
		$reactivarClaseResult = $cronograma->reactivarClase($idClase);

		if ($reactivarClaseResult == 0) {
			echo json_encode($GLOBALS['utils']->getResponse(0, "La clase se ha reactivado exitosamente"));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Lo lamentamos, ha ocurrido un error."));
		}
	}

	function agregarClaseACronograma() {
		$post = json_decode(file_get_contents('php://input'));

		$idCronograma = (int) $post[0]->idCronograma;
		$idAlumno = (int) $post[2]->idAlumno;
		$selectedOption = $post[1]->selectedOption;
		$fechaClase = $post[3]->fechaClase;

		$cronograma = new Cronograma();
		$agregarClaseACronograma = $cronograma->agregarClaseACronograma($idCronograma, $idAlumno, $selectedOption, $fechaClase);

		if ($agregarClaseACronograma == 0) {
			echo json_encode($GLOBALS['utils']->getResponse(0, "La clase se ha agregado exitosamente"));
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, "Lo lamentamos, ha ocurrido un error."));
		}
	}

	function containsOnlyNull($input) {
		return empty(array_filter($input, function ($a) { return $a !== null;}));
	}

	$authGuard = new AuthGuard();
	$allowedAccessResult = $authGuard->allowedAccess();
	if ($allowedAccessResult != false && $allowedAccessResult != "expired") {
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
					case '/calcularCronograma/obtenerClasesActivasCronograma':
						obtenerClasesActivasCronograma();
					break;
					case '/calcularCronograma/actualizarCronogramaActivo':
						actualizarCronogramaActivo();
					break;
					case '/calcularCronograma/obtenerClasesDisponiblesParaAlumno':
						obtenerClasesDisponiblesParaAlumno();
					break;
					case '/calcularCronograma/cancelarClase':
						cancelarClase();
					break;
					case '/calcularCronograma/reactivarClase':
						reactivarClase();
					break;
					case '/calcularCronograma/agregarClaseACronograma':
						agregarClaseACronograma();
					break;
					default:
						echo "podriamos agregar otra consulta mas";
						break;
				}
				break;    	
			}  
		}
	} else {
		if ($method != "OPTIONS") {
			if ($allowedAccessResult == "expired") {
				header("HTTP/1.1 401 Token Expired");
				exit;
			} else {
				header("HTTP/1.1 401 Unauthorized");
				exit;
			}
		}
	}
?>