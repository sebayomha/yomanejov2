<?php
	require_once('alumnosDB.php');
	require_once('utils.php');
	require_once('token.php');
	require_once('authGuard.php');

	$utils = new Utils();
	$requestMethod = $utils->getUri();

	$method = $_SERVER['REQUEST_METHOD']; //Obtengo el METODO: PUT/GET/DELETE/POST.
	
	function obtenerAlumnos() {
		$alumno = new Alumno();
		$resultAlumno = $alumno->obtenerAlumnos();
		echo json_encode($GLOBALS['utils']->getResponse(0, $resultAlumno));
	}

	function getInformacionPersonal() {
		$alumno = new Alumno();
		$resultAlumno = $alumno->getInformacionPersonal($_GET['idAlumno']);
		echo json_encode($GLOBALS['utils']->getResponse(0, $resultAlumno));	
	}

	function updateAlumnoInformacionPersonal() {
		$post = json_decode(file_get_contents('php://input'));
		$idAlumno = $post->idAlumno;
		$nuevoDocumento = $post->nuevoDocumento;
		$nuevoNombre = $post->nuevoNombre;
		$nuevoTelefono = $post->nuevoTelefono;
		$idDirFisica = $post->idDirFisica;
		$direccionFisica = $post->direccionFisicaInformation;

		$alumno = new Alumno();
		$resultEditingAlumno = $alumno->updateAlumnoInformacionPersonal($idAlumno, $idDirFisica, $nuevoNombre, $nuevoTelefono, $nuevoDocumento, $direccionFisica);
		if ($resultEditingAlumno == 0) {
			echo json_encode($GLOBALS['utils']->getResponse(0, 'Alumno actualizado correctamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}
	}

	function eliminarAlumno() {
		$post = json_decode(file_get_contents('php://input'));
		$idAlumno = $post->idAlumno;
		$idCronograma = $post->idCronograma;
		$motivoBaja = $post->motivoDeBaja;

		$alumno = new Alumno();
		$resultDelete = $alumno->eliminarAlumno($idAlumno, $idCronograma, $motivoBaja);

		if ($resultDelete == 0) {
			echo json_encode($GLOBALS['utils']->getResponse(0, 'Alumno dado de baja correctamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}
	}

	$authGuard = new AuthGuard();
	$allowedAccessResult = $authGuard->allowedAccess();
	if ($allowedAccessResult != false && $allowedAccessResult != "expired") {
		switch ($method) {
			case 'GET': {
					//Obtengo la URL Final para saber cual accion ejecutar.
					switch ($requestMethod){
					case '/alumnos':
						obtenerAlumnos();
						break;
					case '/alumnos/getInformacionPersonal':
						getInformacionPersonal();
					break;
					}	    
				break;	
			}
	
			case 'POST': {
				//Obtengo la URL Final para saber cual accion ejecutar.
				switch ($requestMethod){
					case '/alumnos/update':
						updateAlumnoInformacionPersonal();
					break;
					case '/alumnos/eliminar':
						eliminarAlumno();
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