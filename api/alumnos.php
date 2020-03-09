<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
	require_once('alumnosDB.php');
	iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
	//Va a ser utilizada cuando existan sesiones
	//require_once('token.php');
	require_once('utils.php');

	$utils = new Utils();
	$requestMethod = $utils->getUri();

	$method = $_SERVER['REQUEST_METHOD']; //Obtengo el METODO: PUT/GET/DELETE/POST.
	
	function obtenerAlumnos() {
		$alumno = new Alumno();
		$resultAlumno = $alumno->obtenerAlumnos();
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
			echo json_encode($GLOBALS['utils']->getResponse(0, 'Datos actualizados correctamente'));	
		} else {
			echo json_encode($GLOBALS['utils']->getResponse(1, 'Lo lamentamos, ha ocurrido un error'));
		}
	}

	switch ($method) {
		case 'GET': {
		  	//Obtengo la URL Final para saber cual accion ejecutar.
		  	switch ($requestMethod){
				case '/alumnos':
					obtenerAlumnos();
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
				case '/alumnos/update':
					updateAlumnoInformacionPersonal();
				break;
			}
			break;    	
		  }  
	  }
?>