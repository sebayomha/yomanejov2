<?php
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');

    class Alumno {

        public $db;
        public $conn;
        
        function __construct() { 
            $this->db = new ConnectionDB();
            $this->conn = $this->db->getConnection();
        }

        function obtenerAlumnos() {
            $state = $this->conn->prepare('SELECT 
            d1.idDireccion AS id_DirPrincipal,
            d1.calle AS calle_DirPrincipal, 
            d1.calle_diag AS calle_diag_DirPrincipal, 
            d1.calle_a AS calle_a_DirPrincipal,
            d1.calle_a_diag AS calle_a_diag_DirPrincipal,
            d1.calle_b AS calle_b_DirPrincipal,
            d1.calle_b_diag AS calle_b_diag_DirPrincipal,
            d1.numero AS numero_DirPrincipal,
            d1.ciudad AS ciudad_DirPrincipal,
            d1.departamento AS departamento_DirPrincipal,
            d1.floor_ AS floor_DirPrincipal,
            d1.observaciones AS observaciones_DirPrincipal,
            d2.idDireccion AS id_DirAlternativa,
            d2.calle AS calle_DirAlternativa,
            d2.calle_diag AS calle_diag_DirAlternativa, 
            d2.calle_a AS calle_a_DirAlternativa,
            d2.calle_a_diag AS calle_a_diag_DirAlternativa,
            d2.calle_b AS calle_b_DirAlternativa,
            d2.calle_b_diag AS calle_b_diag_DirAlternativa,
            d2.numero AS numero_DirAlternativa,
            d2.ciudad AS ciudad_DirAlternativa,
            d2.departamento AS departamento_DirAlternativa,
            d2.floor_ AS floor_DirAlternativa,
            d2.observaciones AS observaciones_DirAlternativa,
            d3.idDireccion AS id_DirFisica,
            d3.calle AS calle_DirFisica,
            d3.calle_diag AS calle_diag_DirFisica, 
            d3.calle_a AS calle_a_DirFisica,
            d3.calle_a_diag AS calle_a_diag_DirFisica,
            d3.calle_b AS calle_b_DirFisica,
            d3.calle_b_diag AS calle_b_diag_DirFisica,
            d3.numero AS numero_DirFisica,
            d3.ciudad AS ciudad_DirFisica,
            d3.departamento AS departamento_DirFisica,
            d3.floor_ AS floor_DirFisica,
            d3.observaciones AS observaciones_DirFisica,
            alumno.nombre, alumno.telefono, alumno.idAlumno, alumno.fechaAlta, alumno.activo,
            alumno.documento,
            alumno.idDisponibilidad,
            alumno.confirmado,
            alumno.fechaBaja,
            alumno.motivoBaja,
            cronograma.idCronograma,
            GROUP_CONCAT(
                IF(clase.claseCancelada="" OR clase.claseCancelada="false", 
                null, 
                CONCAT(clase.idClase, ": ",clase.motivoCancelacion)
                ) SEPARATOR ","
            ) as clasesCanceladasStatus,
            idAlumnoCronograma, cantClasesTomadas, cantClasesTotales,
            disponibilidad.Monday, disponibilidad.Tuesday, disponibilidad.Wednesday, disponibilidad.Thursday, disponibilidad.Friday, disponibilidad.Saturday, disponibilidad.Sunday
            FROM alumno 
            LEFT JOIN direccion AS d3 ON d3.idDireccion = alumno.idDireccionFisica
            INNER JOIN direccion AS d1 ON d1.idDireccion = alumno.idDireccion
            LEFT JOIN direccion AS d2 ON d2.idDireccion = alumno.idDireccionAlt
            INNER JOIN disponibilidad ON disponibilidad.idDisponibilidad = alumno.idDisponibilidad
            INNER JOIN cronograma ON cronograma.idAlumno = alumno.idAlumno
            INNER JOIN clase ON clase.idCronograma = cronograma.idCronograma
            LEFT JOIN alumnocronogramaclasestomadas ON alumnocronogramaclasestomadas.idAlumno = alumno.idAlumno AND alumnocronogramaclasestomadas.idCronograma = cronograma.idCronograma
            GROUP BY alumno.idAlumno
            ORDER BY alumno.idAlumno DESC');
            $state->execute();
            $result = $state->get_result();
            $alumnos = (array) [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $row['dirAlternativaFormateada'] = null;
                    $row['cantClasesParaRestantes'] = $row['cantClasesTotales'] - $row['cantClasesTomadas'];
                    if ($row['id_DirAlternativa'] != null) {
                        $row['dirAlternativaFormateada'] = $this->obtenerDireccionParaMostrar($row['calle_DirAlternativa'], filter_var($row['calle_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirAlternativa'], filter_var($row['calle_a_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirAlternativa'], filter_var($row['calle_b_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirAlternativa'], $row['ciudad_DirAlternativa'], $row['floor_DirAlternativa'], $row['departamento_DirAlternativa']);
                    }

                    $row['dirPrincipalFormateada'] = $this->obtenerDireccionParaMostrar($row['calle_DirPrincipal'], filter_var($row['calle_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirPrincipal'], filter_var($row['calle_a_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirPrincipal'], filter_var($row['calle_b_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirPrincipal'], $row['ciudad_DirPrincipal'], $row['floor_DirPrincipal'], $row['departamento_DirPrincipal']);
                    $row['dirFisicaFormateada'] = $this->obtenerDireccionParaMostrar($row['calle_DirFisica'], filter_var($row['calle_diag_DirFisica'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirFisica'], filter_var($row['calle_a_diag_DirFisica'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirFisica'], filter_var($row['calle_b_diag_DirFisica'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirFisica'], $row['ciudad_DirFisica'], $row['floor_DirFisica'], $row['departamento_DirFisica']);
                    $disponibilidades = (object) [
                        'Monday' => $this->verificarSiEsTodoElDia($row['Monday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $row['dirPrincipalFormateada'], $row['dirAlternativaFormateada']),
                        'Tuesday' => $this->verificarSiEsTodoElDia($row['Tuesday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $row['dirPrincipalFormateada'], $row['dirAlternativaFormateada']),
                        'Wednesday' => $this->verificarSiEsTodoElDia($row['Wednesday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $row['dirPrincipalFormateada'], $row['dirAlternativaFormateada']),
                        'Thursday' => $this->verificarSiEsTodoElDia($row['Thursday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $row['dirPrincipalFormateada'], $row['dirAlternativaFormateada']),
                        'Friday' => $this->verificarSiEsTodoElDia($row['Friday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $row['dirPrincipalFormateada'], $row['dirAlternativaFormateada']),
                        'Saturday' => $this->verificarSiEsTodoElDia($row['Saturday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $row['dirPrincipalFormateada'], $row['dirAlternativaFormateada']),
                        'Sunday' => $this->verificarSiEsTodoElDia($row['Sunday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $row['dirPrincipalFormateada'], $row['dirAlternativaFormateada'])
                    ];


                    $row['disponibilidades'] = $disponibilidades;

                    array_push($alumnos, $row);
                }
            } else {
                return [];
            }
            return $alumnos;
        }

        function obtenerDireccionParaMostrar($calle, $calle_diag, $calle_a, $calle_a_diag, $calle_b, $calle_b_diag, $numero, $ciudad, $floor, $departamento) {
            $stringDireccion = "";

            if($calle_diag == true) {
                $stringDireccion .= "Diagonal ".$calle;
            } else {
                $stringDireccion .= "Calle ".$calle;
            }

            if ($numero != '') {
                $stringDireccion .= " N°".$numero;
            }

            if ($calle_a != '' && $calle_b != '') {
                $stringDireccion .= ' Entre ';
                if ($calle_a_diag) {
                    $stringDireccion .= 'Diagonal '.$calle_a. ' y ';
                    if ($calle_b_diag) {
                        $stringDireccion .= 'Diagonal '.$calle_b;
                    } else {
                        $stringDireccion .= $calle_b;
                    }
                } else {
                    $stringDireccion .= $calle_a. ' y ';
                    if ($calle_b_diag) {
                        $stringDireccion .= 'Diagonal '.$calle_b;
                    } else {
                        $stringDireccion .= $calle_b;
                    }
                }
            } else {
                if ($calle_a != '' || $calle_b != '') {
                    $stringDireccion .= ' Esquina ';
                    if ($calle_a != '') {
                        if ($calle_a_diag) {
                            $stringDireccion .= 'Diagonal '.$calle_a;
                        } else {
                            $stringDireccion .= $calle_a;
                        }
                    } else {
                        if ($calle_b_diag) {
                            $stringDireccion .= 'Diagonal '.$calle_b;
                        } else {
                            $stringDireccion .= $calle_b;
                        }
                    }
                }
            }

            if ($floor != '') {
                $stringDireccion .= ' Dpto: '.$floor.$departamento.', '.$ciudad;
            } else {
                $stringDireccion .= ', '.$ciudad;
            }

            

            return $stringDireccion;
        }

        function verificarSiEsTodoElDia($diaString, $direccionPrincipal, $direccionAlternativa, $direccionPrincipalFormateada, $direccionAlternativaFormateada) {
            $diaInformacion = (object) [
                'todoElDia' => false,
                'direccionUtilizada' => null,
                'usandoDirAlternativa' => false,
                'direccionUtilizadaFormateada' => null,
                'tramosHorarios' => []
            ];

            $diaEntero = ['08:00', '09:00', '10:00', '11:15', '12:15', '13:15', '14:30', '15:30', '16:30', '17:45', '18:45', '19:45'];
        
            if (substr_count($diaString, "|") == 1) { //es solo un tramo y es todo el dia (disponibleTodoElDia)
                $diaStringSoloHorarios = strtok($diaString, '|');
                $diaStringSinCommas = str_replace(',', '', $diaStringSoloHorarios);
                $diaStringSinEspacios = str_replace(' ', '', $diaStringSinCommas);

                $diaEnteroString = implode("",$diaEntero);

                if ($diaStringSinEspacios == $diaEnteroString) { //es todo el dia confirmado
                    $diaInformacion->todoElDia = true;
                    //valido que direccion esta usando para todo el dia
                    if ($direccionAlternativa == null) { //nunca cargo una direccion alternativa asi que debe ser la principal
                        $diaInformacion->direccionUtilizada = $direccionPrincipal;
                        $diaInformacion->direccionUtilizadaFormateada = $direccionPrincipalFormateada;
                        $diaInformacion->usandoDirAlternativa = false;
                    } else { //puede ser una direccion alternativa o una principal
                        if (strpos($diaString, 'true') !== false) { //es la direccion alternativa
                            $diaInformacion->direccionUtilizada = $direccionAlternativa;
                            $diaInformacion->direccionUtilizadaFormateada = $direccionAlternativaFormateada;
                            $diaInformacion->usandoDirAlternativa = true;
                        } else { //es la direccion principal
                            $diaInformacion->direccionUtilizada = $direccionPrincipal;
                            $diaInformacion->direccionUtilizadaFormateada = $direccionPrincipalFormateada;
                            $diaInformacion->usandoDirAlternativa = false;
                        }
                    }
                } else { //Es un solo tramo
                    $cantidadDeTramos = substr_count($diaString, "|"); //cantidadDeTramos
                    $tramos = [];
                    $diaStringCopy = $diaString;
                    for ($i=0; $i < $cantidadDeTramos; $i++) {
    
                        $tramoHorario = (object) [
                            'horarios' => null,
                            'direccionUtilizada' => null,
                            'usandoDirAlternativa' => false,
                            'direccionUtilizadaFormateada' => null
                        ];
    
                        $tramo = rtrim(strtok($diaStringCopy,  '|'), ", ");
                        $tramo = trim(strtok($diaStringCopy,  '|'), ", ");
                        $tramo = str_replace(", ", ",", $tramo);
                        $diaStringCopy = trim($diaStringCopy, ", ");
                        $diaStringCopy = str_replace(", ", ",", $diaStringCopy);

                        $tramoSize = strlen($tramo);
                        $indexToF = $tramoSize + 2;
                        
                        $lastIndexOfToF;
                        if(substr($diaStringCopy, $indexToF, 1) == 't') { //es una direccion alternativa para ese tramo
                            $tramoHorario->horarios = explode(",",$tramo);
                            $tramoHorario->direccionUtilizada = $direccionAlternativa;
                            $tramoHorario->direccionUtilizadaFormateada = $direccionAlternativaFormateada;
                            $tramoHorario->usandoDirAlternativa = true;
                            $lastIndexOfToF = $indexToF + 4;
                        } else { //es la direccion principal para este tramo
                            $tramoHorario->horarios = explode(",",$tramo); 
                            $tramoHorario->direccionUtilizada = $direccionPrincipal;
                            $tramoHorario->direccionUtilizadaFormateada = $direccionPrincipalFormateada;
                            $tramoHorario->usandoDirAlternativa = false;
                            $lastIndexOfToF = $indexToF + 5;
                        }
                        
                        array_push($diaInformacion->tramosHorarios,$tramoHorario);
    
                        $diaStringCopy = substr($diaStringCopy, $lastIndexOfToF);
                    }
                }
            } else { //posee mas de 1 tramo, y no es (disponibleTodoElDia)
                $cantidadDeTramos = substr_count($diaString, "|"); //cantidadDeTramos
                $tramos = [];
                $diaStringCopy = $diaString;
                for ($i=0; $i < $cantidadDeTramos; $i++) {

                    $tramoHorario = (object) [
                        'horarios' => null,
                        'direccionUtilizada' => null,
                        'usandoDirAlternativa' => false,
                        'direccionUtilizadaFormateada' => null
                    ];

                    $tramo = rtrim(strtok($diaStringCopy,  '|'), ", ");
                    $tramo = trim(strtok($diaStringCopy,  '|'), ", ");
                    
                    $diaStringCopy = trim($diaStringCopy, ", ");

                    $tramoSize = strlen($tramo);
                    $indexToF = $tramoSize + 3;
                    

                    $lastIndexOfToF;
                    if(substr($diaStringCopy, $indexToF, 1) == 't') { //es una direccion alternativa para ese tramo
                        $tramoHorario->horarios = explode(",",$tramo);
                        $tramoHorario->direccionUtilizada = $direccionAlternativa;
                        $tramoHorario->direccionUtilizadaFormateada = $direccionAlternativaFormateada;
                        $tramoHorario->usandoDirAlternativa = true;
                        $lastIndexOfToF = $indexToF + 4;
                    } else { //es la direccion principal para este tramo
                        $tramoHorario->horarios = explode(",",$tramo); 
                        $tramoHorario->direccionUtilizada = $direccionPrincipal;
                        $tramoHorario->direccionUtilizadaFormateada = $direccionPrincipalFormateada;
                        $tramoHorario->usandoDirAlternativa = false;
                        $lastIndexOfToF = $indexToF + 5;
                    }
                    
                    array_push($diaInformacion->tramosHorarios, $tramoHorario);

                    $diaStringCopy = substr($diaStringCopy, $lastIndexOfToF);
                }
            }

            return $diaInformacion;

        }

        function updateAlumnoInformacionPersonal($idAlumno, $idDirFisica, $nuevoNombre, $nuevoTelefono, $nuevoDocumento, $direccionFisica) {
            $idDireccionUpdate = null;
            $direccionFisicaNueva = false;
            $doInsert = false;

            if ($direccionFisica->nuevaDireccion == true) { //es una direccion nueva, ahora hay que ver si cambio
                $doInsert = true;
            } else { //es una direccion ya seleccionada del listado.
                $idDireccionUpdate = $direccionFisica->idDireccionSeleccionada;
            }

            $address_diag_string = var_export($direccionFisica->direccion->diag, true);
            $address_a_diag_string = var_export($direccionFisica->direccion->diag_a, true);
            $address_b_diag_string = var_export($direccionFisica->direccion->diag_b, true);

            //Actualizo la direccion fisica
            if ($doInsert) {
                $state = $this->conn->prepare('INSERT INTO direccion (calle, calle_diag, calle_a, calle_a_diag, calle_b, calle_b_diag, numero, ciudad, departamento, floor_, observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                $state->bind_param('sssssssssss', $direccionFisica->direccion->street, $address_diag_string, $direccionFisica->direccion->street_a, $address_a_diag_string, $direccionFisica->direccion->street_b, $address_b_diag_string, $direccionFisica->direccion->altitud, $direccionFisica->direccion->city, $direccionFisica->direccion->department, $direccionFisica->direccion->floor, $direccionFisica->direccion->observations);
                $state->execute();
                $idDireccionUpdate = $this->conn->insert_id;           
            } else {
/*                 $state = $this->conn->prepare('UPDATE direccion SET calle = ?, calle_diag = ?, calle_a = ?, calle_a_diag = ?, calle_b = ?, calle_b_diag = ?, numero = ?, ciudad = ?, departamento = ?, floor_ = ?, observaciones = ? WHERE direccion.idDireccion = ?');
                $state->bind_param('ssssssssssss', $direccionFisica->direccion->street, $address_diag_string, $direccionFisica->direccion->street_a, $address_a_diag_string, $direccionFisica->direccion->street_b, $address_b_diag_string, $direccionFisica->direccion->altitud, $direccionFisica->direccion->city, $direccionFisica->direccion->department, $direccionFisica->direccion->floor, $direccionFisica->direccion->observations, $idDireccionUpdate);
                $state->execute();  */   
            }
/*             
                else {
                    if ($idDirFisica != null) { //elimino la direccion fisica previa porque selecciono una del listado
                        $state = $this->conn->prepare('DELETE FROM direccion WHERE direccion.idDireccion = ?');
                        $state->bind_param('i', $idDirFisica);
                        $state->execute();
                    } */
            
            $state = $this->conn->prepare('UPDATE alumno SET nombre = ?, telefono = ?, documento = ?, idDireccionFisica = ? WHERE alumno.idAlumno = ?');
            $state->bind_param('sssii', $nuevoNombre, $nuevoTelefono, $nuevoDocumento, $idDireccionUpdate, $idAlumno);
            $state->execute();

            return 0;
        }

        function getInformacionPersonal($idAlumno) {
            $state = $this->conn->prepare('SELECT 
            d1.idDireccion AS id_DirPrincipal,
            d1.calle AS calle_DirPrincipal, 
            d1.calle_diag AS calle_diag_DirPrincipal, 
            d1.calle_a AS calle_a_DirPrincipal,
            d1.calle_a_diag AS calle_a_diag_DirPrincipal,
            d1.calle_b AS calle_b_DirPrincipal,
            d1.calle_b_diag AS calle_b_diag_DirPrincipal,
            d1.numero AS numero_DirPrincipal,
            d1.ciudad AS ciudad_DirPrincipal,
            d1.departamento AS departamento_DirPrincipal,
            d1.floor_ AS floor_DirPrincipal,
            d1.observaciones AS observaciones_DirPrincipal,
            d2.idDireccion AS id_DirAlternativa,
            d2.calle AS calle_DirAlternativa,
            d2.calle_diag AS calle_diag_DirAlternativa, 
            d2.calle_a AS calle_a_DirAlternativa,
            d2.calle_a_diag AS calle_a_diag_DirAlternativa,
            d2.calle_b AS calle_b_DirAlternativa,
            d2.calle_b_diag AS calle_b_diag_DirAlternativa,
            d2.numero AS numero_DirAlternativa,
            d2.ciudad AS ciudad_DirAlternativa,
            d2.departamento AS departamento_DirAlternativa,
            d2.floor_ AS floor_DirAlternativa,
            d2.observaciones AS observaciones_DirAlternativa,
            d3.idDireccion AS id_DirFisica,
            d3.calle AS calle_DirFisica,
            d3.calle_diag AS calle_diag_DirFisica, 
            d3.calle_a AS calle_a_DirFisica,
            d3.calle_a_diag AS calle_a_diag_DirFisica,
            d3.calle_b AS calle_b_DirFisica,
            d3.calle_b_diag AS calle_b_diag_DirFisica,
            d3.numero AS numero_DirFisica,
            d3.ciudad AS ciudad_DirFisica,
            d3.departamento AS departamento_DirFisica,
            d3.floor_ AS floor_DirFisica,
            d3.observaciones AS observaciones_DirFisica,
            alumno.documento
            FROM alumno 
            LEFT JOIN direccion AS d3 ON d3.idDireccion = alumno.idDireccionFisica
            INNER JOIN direccion AS d1 ON d1.idDireccion = alumno.idDireccion
            LEFT JOIN direccion AS d2 ON d2.idDireccion = alumno.idDireccionAlt           
            WHERE alumno.idAlumno = ?');
            $state->bind_param('i', $idAlumno);
            $state->execute();
            $result = $state->get_result();

            $alumnos = (array) [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $row['dirAlternativaFormateada'] = null;
                    if ($row['id_DirAlternativa'] != null) {
                        $row['dirAlternativaFormateada'] = $this->obtenerDireccionParaMostrar($row['calle_DirAlternativa'], filter_var($row['calle_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirAlternativa'], filter_var($row['calle_a_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirAlternativa'], filter_var($row['calle_b_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirAlternativa'], $row['ciudad_DirAlternativa'], $row['floor_DirAlternativa'], $row['departamento_DirAlternativa']);
                    }

                    $row['dirPrincipalFormateada'] = $this->obtenerDireccionParaMostrar($row['calle_DirPrincipal'], filter_var($row['calle_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirPrincipal'], filter_var($row['calle_a_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirPrincipal'], filter_var($row['calle_b_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirPrincipal'], $row['ciudad_DirPrincipal'], $row['floor_DirPrincipal'], $row['departamento_DirPrincipal']);
                    $row['dirFisicaFormateada'] = $this->obtenerDireccionParaMostrar($row['calle_DirFisica'], filter_var($row['calle_diag_DirFisica'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirFisica'], filter_var($row['calle_a_diag_DirFisica'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirFisica'], filter_var($row['calle_b_diag_DirFisica'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirFisica'], $row['ciudad_DirFisica'], $row['floor_DirFisica'], $row['departamento_DirFisica']);

                    array_push($alumnos, $row);
                }
            } else {
                return [];
            }
            return $alumnos;
        }

        function eliminarAlumno($idAlumno, $idCronograma, $motivoBaja) {
            //Eliminar cronograma (LOGICAMENTE)
            $status = "CANCELADO";
            $nowHour = date('Y-m-d h:i:s a', time());
            $state = $this->conn->prepare('UPDATE cronograma SET status = ?, timestampCancelado = ? WHERE cronograma.idCronograma = ?');
            $state->bind_param('ssi', $status, $nowHour ,$idCronograma);
            $state->execute();

            //Eliminar clases (LOGICAMENTE)
            $state = $this->conn->prepare('UPDATE clase SET status = ? WHERE clase.idCronograma = ?');
            $state->bind_param('si', $status, $idCronograma);
            $state->execute();
            
            //Eliminar alumno (LOGICAMENTE)
            $activo = "false";
            $now = date("Y-m-d");

            $state = $this->conn->prepare('UPDATE alumno SET activo = ?, motivoBaja = ?, fechaBaja = ? WHERE alumno.idAlumno = ?');
            $state->bind_param('sssi', $activo, $motivoBaja, $now, $idAlumno);
            $state->execute();

            return 0;
        }

    }

?>