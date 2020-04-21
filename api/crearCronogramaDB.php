<?php
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');

    class Cronograma {

        public $db;
        public $conn;
        
        function __construct() { 
            $this->db = new ConnectionDB();
            $this->conn = $this->db->getConnection();
        }

        //Funcion principal que se encargara de armar el cronograma
        function calcularCronograma($cantClases, $disponibilidad, $direccion, $fechaInicio, $excepciones, $direccion_alt, $hayDireccionAlternativa, $resOptions, $resExcepcionesOptions){
            $horariosTentativos = array(); //arreglo que se va a retornar con el cronograma

            $fechaBusqueda = DateTime::createFromFormat("Y-m-d", $fechaInicio);
            $nombreDiaBusqueda = strftime("%A",$fechaBusqueda->getTimestamp());

            //**********//
            //se arma toda la informacion de las zonas
            //**********//
            $zonas = $this->obtenerZonas(); //obtengo las zonas y las cargo solo 1 vez
            $arrayGrafo = $this->crearGrafoZonas($zonas); //creo el grafo con todas las zonas y sus adyacentes
            $zonaAlumno = $this->obtenerZonaAlumno($direccion); //obtengo la zona del alumno a partir de su direccion
            $zonaAlumno_alt = true;
            $idAutoMaster_alt;
            if ($hayDireccionAlternativa) {
                $zonaAlumno_alt = $this->obtenerZonaAlumno($direccion_alt);
                $idAutoMaster_alt = $this->obtenerIdAutoMaster($zonaAlumno_alt);
            }

            if ($zonaAlumno === null || $zonaAlumno_alt === null) {
                return 2;
            }
            
            $idAutoMaster = $this->obtenerIdAutoMaster($zonaAlumno); //obtengo el id del auto que tiene asignada la zona master del alumno
            $fechasExcepciones = array_keys($excepciones);

            //me guardo las disponibilidades de los autos
            $disponibilidadesAutos = $this->obtenerDisponibilidadesAutos();
            $tolerancias = $this->obtenerMaximosDiasTolerancia();

            $totalDiasTentativosRetornar; //Total de dias tentativos a retornar

            switch ($cantClases) {
                case 1:  $totalDiasTentativosRetornar = 7; break;
                case 2:  $totalDiasTentativosRetornar = 8; break;
                case 3:  $totalDiasTentativosRetornar = 8; break;
                case 4:  $totalDiasTentativosRetornar = 10; break;
                case 5:  $totalDiasTentativosRetornar = 12; break;
                case 6:  $totalDiasTentativosRetornar = 13; break;
                case 7:  $totalDiasTentativosRetornar = 13; break;
                case 8:  $totalDiasTentativosRetornar = 15; break;
                case 9:  $totalDiasTentativosRetornar = 20; break;
                case 10: $totalDiasTentativosRetornar = 20; break;
                case 12: $totalDiasTentativosRetornar = 25; break;
                default: $totalDiasTentativosRetornar = 30; break;
            };

            $i = 1;
            while ($i <= $totalDiasTentativosRetornar) { //Se recorrera hasta tanto obtener la cantidad de dias posibles a retornar
                $puede = true; 
                if (in_array($fechaBusqueda->format('Y-m-d'), $fechasExcepciones)) { //evaluo si en las excepciones, el alumno puede esta fecha.
                    if ($excepciones[$fechaBusqueda->format('Y-m-d')]->no_puede == true) {
                        $puede = false;
                    }
                }
                
                if ($disponibilidad[$nombreDiaBusqueda] != null && $puede) { //entonces es un dia que el usuario esta disponible
                    $clasesDelDiaPorAuto = $this->obtenerCronogramaDelDia($fechaBusqueda); //armo un diccionario auto => clases
                    $clasesDelDiaPorAuto = $this->eliminarAutosInactivos($clasesDelDiaPorAuto, $fechaBusqueda); //quito los autos que esten inactivos esa fecha
                    $autos = []; //array que se usa para armar la estructura final que se retornara
                    $diccionarioFechaHorariosLibres = [];
                    foreach ($clasesDelDiaPorAuto as $idAuto => $clases) { //recorro cada clase
                        $cronogramasActuales = [];
                        
                        //horarios libres va a contener los dias que el auto no este ocupado y el usuario este disponible
                        $horariosLibres;
                        $result = $fechaBusqueda->format('Y-m-d');

                        $estoySobreUnaExcepcion = false;
                        if (in_array($result, $fechasExcepciones) && count($excepciones[$result]->options) > 0) {
                            $estoySobreUnaExcepcion = true;
                            $horariosLibres = $this->obtenerHorariosLibresAutoYAlumnoExcepciones($clases, $excepciones[$result]->options,$nombreDiaBusqueda, $result);
                        } else {
                            $horariosLibres = $this->obtenerHorariosLibresAutoYAlumno($clases, $disponibilidad, $nombreDiaBusqueda, $result,$idAuto);
                        }

                        
                        $horariosOcupados = array_column($clases, 'horaInicio'); //son los horarios que estan efectivamente ocupados por clases

                        $horariosLibresDataGeneral = [];

                        /* INFO ADICIONAL POR CADA HORARIO */
                        $tieneEldiaLibre = false;
                        if (count($horariosOcupados) === 1 && $horariosOcupados[0] === null) { //no hay horarios ocupados, por ende todos estan libres en el dia.
                            $tieneEldiaLibre = true;
                        }

                        if ($clases[0]['idClase'] != null) { //armo el cronograma actual del auto. Si este auto posee clases armo el array, sino retorno un array vacio
                            foreach ($clases as $clase) {
                                $nombreAlumno = $this->obtenerNombreAlumno($clase['alumno']);
                                $direccionClase = $this->obtenerDireccionClase($clase['idDireccion']);
                                
                                $cronogramaActualObject = (object) [
                                    'alumno' => $nombreAlumno,
                                    'direccion' => $direccionClase,
                                    'horario' => $clase['horaInicio']
                                ];
    
                                array_push($cronogramasActuales, $cronogramaActualObject);
                            }
                        } else {
                            $cronogramasActuales = [];
                        }
                        /* FIN INFO ADICIONAL POR CADA HORARIO */

                        //**********//
                        //comienzo a armar el array que se va a retornar
                        //**********//           
       
                        foreach ($horariosLibres as $horarioAuto) { //en base a los horarios libres instancio los objetos que se van a terminar retornando     
                            if ($disponibilidadesAutos[$idAuto] === "A" || $this->esTurnoDisponible($disponibilidadesAutos[$idAuto], $horarioAuto, $nombreDiaBusqueda)) { //si el horario esta en el turno que el auto puede (A = todo el dia,T= solo por la tarde, M= solo por la maniana)
                                $esDeLaZona = false;
                                $horarioData = (object) [
                                    'horaInicio' => '',
                                    'ratingHorario' => '',
                                    'ratingZona' => '',
                                    'ratingGeneral' => '',
                                    'idAuto' => '',
                                    'cronogramaActual' => '',
                                    'tieneEldiaLibre' => '',
                                    'esDeLaZona' => '',
                                    'usandoDirAlt' => '',
                                    'idZona' => ''
                                ];
                                $horarioData->idAuto = $idAuto;
                                $horarioData->cronogramaActual = $cronogramasActuales;
                                $horarioData->tieneEldiaLibre = $tieneEldiaLibre;

                                $horarioData->horaInicio = $horarioAuto;
                                $horarioData->ratingHorario = $this->obtenerRatingHorario($horariosOcupados, $horarioAuto, $tolerancias, $fechaInicio, $fechaBusqueda);
                                $zonasVecinas = $this->zonasDeClasesVecinas($clases, $horarioAuto); //busco si el horario posee clases vecinas para ver si es necesario calcular la cercania o no.
                                                                                            //Si no posee clases vecinas no vale la pena calcular la distancia
                                
                                
                                $arrayABuscar;
                                if ($estoySobreUnaExcepcion == true) {
                                    $arrayABuscar = $resExcepcionesOptions[$fechaBusqueda->format('Y-m-d')];
                                } else {
                                    $arrayABuscar = $resOptions[$nombreDiaBusqueda];
                                }

                                $horarioTieneDireccionAlternativa = $this->verificarSiElHorarioTieneDireccionAlternativa($arrayABuscar, $horarioAuto, $estoySobreUnaExcepcion);                                

                                $zonaBusqueda = $zonaAlumno; //por defecto va a ser la zona principal la que voy a buscar
                                if ($horarioTieneDireccionAlternativa == true) {
                                    if ($idAutoMaster_alt == $idAuto) {
                                        $esDeLaZona = true;
                                    }

                                    $horarioData->usandoDirAlt = true;
                                    $zonaBusqueda = $zonaAlumno_alt; //Este horario posee direccion alternativa asi que realizo el calculo del rating bajo esta zona.
                                } else {
                                    if ($idAutoMaster == $idAuto) {
                                        $esDeLaZona = true;
                                    }
                                    $horarioData->usandoDirAlt = false;
                                }

                                $horarioData->idZona = $zonaBusqueda; //Es la zona de ese horario.
                                
                                if (!empty($zonasVecinas)) {
                                    $ratingZonaMasCerca = 0;

                                    foreach ($zonasVecinas as $zona) {
                                        $posicionGrafoZonaClase = array_search($zona, array_column($arrayGrafo, 'idZona'));

                                        $ratingZonaActual = $this->obtenerRatingZona($posicionGrafoZonaClase, $zonaBusqueda, $arrayGrafo);
                                        if($ratingZonaActual > $ratingZonaMasCerca) {
                                            $ratingZonaMasCerca = $ratingZonaActual;
                                        }
                                    }
                                } else {
                                    $ratingZonaMasCerca = null;
                                }

                                $horarioData->esDeLaZona = $esDeLaZona;
                                $horarioData->ratingZona = $ratingZonaMasCerca;
        
                                if ($ratingZonaMasCerca == null) {
                                    $horarioData->ratingGeneral = $horarioData->ratingHorario;
                                } else {
                                    if ($horarioData->ratingZona >= 6) { //si el rating de la zona es muy bueno, entonces mantengo el calculo
                                        $horarioData->ratingGeneral = abs($horarioData->ratingHorario + $horarioData->ratingZona) / 2;
                                    } else { //como el rating de la zona no es bueno, le quito algunos puntos al puntaje general para que sea mas realistico
                                        $horarioData->ratingGeneral = (abs($horarioData->ratingHorario + $horarioData->ratingZona) / 2) - 2;
                                    }
                                }

                                /* ANALIZO SI DEBO AGREGAR EL HORARIO O NO PARA SER RETORNADO */
                                if ($horarioTieneDireccionAlternativa == false) {
                                    if ($horarioData->tieneEldiaLibre) {
                                        if ($idAuto == $idAutoMaster) { //si es el auto master entonces siempre lo agrego
                                            array_push($horariosLibresDataGeneral, $horarioData);
                                        }
                                    } else {
                                        if ($idAuto == $idAutoMaster) { //si es el auto master entonces siempre lo agrego
                                            array_push($horariosLibresDataGeneral, $horarioData);
                                        } else {
                                            if ($horarioData->ratingGeneral > 6) { //no es el auto master pero tiene clases cercanas
                                                array_push($horariosLibresDataGeneral, $horarioData);
                                            }
                                        }
                                    }                                    
                                } else {
                                    if ($horarioData->tieneEldiaLibre) {
                                        if ($idAuto == $idAutoMaster_alt) {
                                            array_push($horariosLibresDataGeneral, $horarioData);
                                        }
                                    } else {
                                        if ($idAuto == $idAutoMaster_alt) { //si es el auto master entonces siempre lo agrego
                                            array_push($horariosLibresDataGeneral, $horarioData);
                                        } else {
                                            if ($ratingZonaMasCerca != null && $ratingZonaMasCerca > 6 && !$horarioData->tieneEldiaLibre) { //no es el auto master pero tiene clases cercanas
                                                array_push($horariosLibresDataGeneral, $horarioData);
                                            }
                                        }
                                    }
                                }

                                /* FIN DE ANALIZO SI DEBO AGREGAR EL HORARIO O NO PARA SER RETORNADO */
                            }
                        }

                        
                        $fechaBusquedaString = $fechaBusqueda->format('Y-m-d');
                        /************/
                        /* ARMO LA ESTRUCTURA QUE SE VA A RETORNAR */
                        /************/
                        foreach ($horariosLibresDataGeneral as $horarioData) {  //NUEVO PARA EL ORDENAMIENTO el auto aun no fue agregado
                            array_push($diccionarioFechaHorariosLibres, $horarioData);
                        }                         
                        usort($diccionarioFechaHorariosLibres, array($this, 'sortHorariosPorHora'));

                        $fechaObject = (object) [
                            'fecha' => $fechaBusquedaString,
                            'dia' => $this->obtenerNombreDia($fechaBusquedaString),
                            'showMoreHours' => 4,
                            'horarios' => $diccionarioFechaHorariosLibres                      
                        ];
                        /************/
                        /* FIN DE ARMAR LA ESTRUCTURA QUE SE VA A RETORNAR */
                        /************/
                        
                        $horariosTentativos[$fechaBusquedaString] = $fechaObject;
                        //DEPRECTATED PERO SE VA A MANTENER
                        //$horariosTentativos[$fechaBusquedaString][$idAuto] = $autoObject; //agrego en el diccionario el nuevo auto con sus horarios disponibles que coinciden con los del alumno
                    }

                } else { //aumento el total de dia para poder retornar la cantidad correspondiente
                    $totalDiasTentativosRetornar++;
                }
                
                //Avanzo un dia
                $fechaBusqueda = DateTime::createFromFormat("Y-m-d", $fechaInicio);
                $fechaBusqueda->modify('+'.$i.' day');
                $nombreDiaBusqueda = strftime("%A",$fechaBusqueda->getTimestamp());
                $i++;
            }

            return $horariosTentativos;
        }

        //Funcion principal que se encargara de armar el cronograma
        function obtenerClasesActivasCronograma($idCronograma, $disponibilidad, $fechaInicio, $excepciones, $resOptions, $resExcepcionesOptions){
            $clasesFinalesRetornar = array(); //arreglo que se va a retornar con el cronograma

            /* NO SE UTILIZA */
            $fechaBusqueda = DateTime::createFromFormat("Y-m-d", $fechaInicio);
            $fechaBusquedaString = $fechaBusqueda->format('Y-m-d');
            $nombreDiaBusqueda = strftime("%A",$fechaBusqueda->getTimestamp());
            /* NO SE UTILIZA */

            //Comentario: Los continue = false los dejo por si en un futuro se necesitan

            $fechasExcepciones = array_keys($excepciones);

            $clasesActualesCronograma = $this->clasesActualesCronograma($idCronograma, $fechaBusqueda);
            
            if (sizeof($clasesActualesCronograma) > 0) {
                foreach ($clasesActualesCronograma as $clase) {
                    $claseData = (object) [
                        'idClase' => $clase['idClase'],
                        'fecha' => $clase['fecha'],
                        'horaInicio' => $clase['horaInicio'],
                        'idAlumno' => $clase['alumno'],
                        'idAuto' => $clase['auto'],
                        'idCronograma' => $clase['idCronograma'],
                        'idDireccion' => $clase['idDireccion'],
                        'usandoDirAlt' => $this->usandoDirAlt($clase['idDireccion'], $clase['alumno']),
                        'continuaDisponible' => true
                    ];

                    $fechaClaseDate = DateTime::createFromFormat("Y-m-d", $claseData->fecha);
                    $nombreDiaClase = strftime("%A",$fechaClaseDate->getTimestamp());

                    $puede = true; 
                    if (in_array($fechaBusqueda->format('Y-m-d'), $fechasExcepciones)) { //evaluo si en las excepciones, el alumno puede esta fecha.
                        if ($excepciones[$fechaBusqueda->format('Y-m-d')]->no_puede == true) {
                            $puede = false;
                        }
                    }
                   
                    if ($claseData->fecha >= $fechaBusquedaString) {
                        if ($claseData->fecha == $fechaBusquedaString) {
                            if (date('H:i', strtotime($claseData->horaInicio)) > date('H:i')) {
                                if ($puede) { //este dia dentro de las excepciones puede
                                    if ($disponibilidad[$nombreDiaClase] != null) { //este dia es un dia que esta disponible
                                        //valido sobre las excepciones
                                        if (in_array($claseData->fecha, $fechasExcepciones) && count($excepciones[$claseData->fecha]->options) > 0) {
                                            if (in_array($claseData->horaInicio, $excepciones[$claseData->fecha]->options)) { //la clase esta dentro de los horarios de la excepcion
                                                array_push($clasesFinalesRetornar, $claseData);
                                            } else { //la clase no esta dentro de los horarios posibles en la excepcion
/*                                                 $claseData->continuaDisponible = false;
                                                array_push($clasesFinalesRetornar, $claseData); */
                                            }
                                        } else { //no estoy en una excepcion, entonces valido con la disponibilidad
                                            if(in_array($clase['horaInicio'], $disponibilidad[$nombreDiaClase])) { //el alumno continua disponible ese dia
                                                array_push($clasesFinalesRetornar, $claseData);
                                            } else { //este dia no puede (basado en la disponibilidad)
/*                                                 $claseData->continuaDisponible = false;
                                                array_push($clasesFinalesRetornar, $claseData); */
                                            }
                                        }
                                    } else { //no esta disponible este dia
/*                                         $claseData->continuaDisponible = false;
                                        array_push($clasesFinalesRetornar, $claseData); */
                                    }
                                } else { //este dia no puede entonces directamente agrego la clase con el flag de que no esta disponible
/*                                     $claseData->continuaDisponible = false;
                                    array_push($clasesFinalesRetornar, $claseData); */
                                }
                            } else { //la clase ya paso
/*                                 $claseData->continuaDisponible = false;
                                array_push($clasesFinalesRetornar, $claseData); */
                            }
                        } else {
                            
                            if ($puede) { //este dia dentro de las excepciones puede
                                if ($disponibilidad[$nombreDiaClase] != null) { //este dia es un dia que esta disponible              
                                    //valido sobre las excepciones
                                    if (in_array($claseData->fecha, $fechasExcepciones) && count($excepciones[$claseData->fecha]->options) > 0) {
                                        if (in_array($claseData->horaInicio, $excepciones[$claseData->fecha]->options)) { //la clase esta dentro de los horarios de la excepcion
                                            array_push($clasesFinalesRetornar, $claseData);
                                        } else { //la clase no esta dentro de los horarios posibles en la excepcion
/*                                             $claseData->continuaDisponible = false;
                                            array_push($clasesFinalesRetornar, $claseData); */
                                        }
                                    } else { //no estoy en una excepcion, entonces valido con la disponibilidad
                                         if(in_array($clase['horaInicio'], $disponibilidad[$nombreDiaClase])) { //el alumno continua disponible ese dia
                                            array_push($clasesFinalesRetornar, $claseData);
                                        } else { //este dia no puede (basado en la disponibilidad)
/*                                             $claseData->continuaDisponible = false;
                                            array_push($clasesFinalesRetornar, $claseData); */
                                        }
                                    }
                                } else { //no esta disponible este dia
/*                                     $claseData->continuaDisponible = false;
                                    array_push($clasesFinalesRetornar, $claseData); */
                                }
                            } else { //este dia no puede entonces directamente agrego la clase con el flag de que no esta disponible
/*                                 $claseData->continuaDisponible = false;
                                array_push($clasesFinalesRetornar, $claseData); */
                            }
                        }
                    } else { //la clase ya paso
/*                         $claseData->continuaDisponible = false;
                        array_push($clasesFinalesRetornar, $claseData); */
                    }
                }   
            } else {
                return [];
            }

            return $clasesFinalesRetornar;
        }

        //Funcion principal que se encargara de guardar el cronograma correspondiente PREVIO a la confirmacion
        function guardarCronograma($selectedOptions, $studentName, $student_phone, $address, $address_alt, $disponibilidad, $excepciones) {
            /*
            SE HACE UN INSERT DE:
            -Direccion principal
            -Direccion alternativa
            -Disponibilidad del alumno
            -El alumno
            -Excepciones que el alumno posea
            -Un nuevo cronograma
            -Las clases asociadas a dicho cronograma
            */
            
            /* SE INSERTA LA DIRECCION PRINCIPAL */            
            $state = $this->conn->prepare('INSERT INTO direccion (calle, calle_diag, calle_a, calle_a_diag, calle_b, calle_b_diag, numero, ciudad, departamento, floor_, observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $address_diag_string = var_export($address[0]->diag, true);
            $address_a_diag_string = var_export($address[1]->diag, true);
            $address_b_diag_string = var_export($address[2]->diag, true);

            $state->bind_param('sssssssssss', $address[0]->street, $address_diag_string, $address[1]->street_a, $address_a_diag_string, $address[2]->street_b, $address_b_diag_string, $address[3]->altitud, $address[4]->city, $address[6]->department, $address[5]->floor, $address[7]->observations);
            
            $idDireccionPrincipal;
            $idDireccionAlternativa = null;
            $idDisponibilidad;
            $idAlumno;
            $idCronograma;
            $idExcepcion;

            if ($state->execute()) { //el insert de la direccion fue exitoso
                $idDireccionPrincipal = $this->conn->insert_id; //Me quedo con el id de la direccion para luego asignarselo al alumno

                /* SE INSERTA LA DIRECCION ALTERNATIVA SI ES QUE POSEE */
                if ($this->hayDireccionAlternativa($selectedOptions)) {
                    /* SE INSERTA LA DIRECCION ALTERNATIVA */            
                    $state = $this->conn->prepare('INSERT INTO direccion (calle, calle_diag, calle_a, calle_a_diag, calle_b, calle_b_diag, numero, ciudad, departamento, floor_, observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                    $address_diag_string = var_export($address_alt[0]->diag, true);
                    $address_a_diag_string = var_export($address_alt[1]->diag, true);
                    $address_b_diag_string = var_export($address_alt[2]->diag, true);

                    $state->bind_param('sssssssssss', $address_alt[0]->street, $address_diag_string, $address_alt[1]->street_a, $address_a_diag_string, $address_alt[2]->street_b, $address_b_diag_string, $address_alt[3]->altitud, $address_alt[4]->city, $address_alt[6]->department, $address_alt[5]->floor, $address_alt[7]->observations);
                    
                    if ($state->execute()) { //el insert de la direccion alternativa fue exitoso
                        $idDireccionAlternativa = $this->conn->insert_id;
                    } else {
                        return false;
                    }
                }

                /* SE INSERTA LA DISPONIBILIDAD DEL ALUMNO */
                /* SE INSERTARA LA DISPONIBILIDAD DEL ALUMNO CON EL SIGUIENTE FORMATO:
                [DISPONIBILIDADES: STRING] | [DIRECCION_ALTERNATIVA: BOOLEAN]
                SI EL TRAMO HORARIO ES EN LA DIRECCION ALTERNATIVA SE PONDRA EN TRUE, SI ES EN LA PRINCIPAL EN FALSE. 
                 */
                $disponibilidad_string = [];
                foreach ($disponibilidad as $dia) {
                    if ($dia->all_day) {
                        $scheduleSend_string = implode (", ", $dia->option[0]->scheduleSend);
                        $scheduleSend_string .= '|'.var_export($dia->option[0]->dir_alt, true);
                        array_push($disponibilidad_string, $scheduleSend_string);
                    } else {
                        if ($dia->option[0]->scheduleSend != null) {
                            $arrayTotal = [];
                            foreach ($dia->option as $option) {
                                $arrayTotal = array_merge($arrayTotal, $option->scheduleSend);
                                $scheduleSend_string = '|'.var_export($option->dir_alt, true);
                                array_push($arrayTotal, $scheduleSend_string);
                            }       
                            $scheduleSend_string = implode (", ", $arrayTotal);
                            array_push($disponibilidad_string, $scheduleSend_string);
                        } else {
                            array_push($disponibilidad_string, null);
                        }
                    }
                }                
                $state = $this->conn->prepare('INSERT INTO disponibilidad (Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday) VALUES (?,?,?,?,?,?,?)');
                $state->bind_param('sssssss', $disponibilidad_string[0], $disponibilidad_string[1], $disponibilidad_string[2], $disponibilidad_string[3], $disponibilidad_string[4], $disponibilidad_string[5], $disponibilidad_string[6]);
                
                if ($state->execute()) { //disponibilidad insertada con exito
                    $idDisponibilidad = $this->conn->insert_id;
                } else {
                    return false;
                }
                
                /* SE INSERTA AL ALUMNO */
                $today = date('Y-m-d');
                $activoYConfirmado = 'false';
                $state = $this->conn->prepare('INSERT INTO alumno (idDireccion, idDireccionAlt, fechaAlta, activo, nombre, telefono, confirmado, idDisponibilidad) VALUES (?,?,?,?,?,?,?,?)');
                $state->bind_param('iisssssi', $idDireccionPrincipal, $idDireccionAlternativa, $today, $activoYConfirmado, $studentName, $student_phone, $activoYConfirmado, $idDisponibilidad);
                if ($state->execute()) { //el insert del alumno fue exitoso
                    $idAlumno = $this->conn->insert_id;
                } else {
                    return false;
                }

                /* SE INSERTAN LAS EXCEPCIONES SI POSEE */
                if (sizeof($excepciones) > 0) {
                    foreach ($excepciones as $excepcion) {
                        $no_puede = 'false';
                        if ($excepcion->no_puede) {
                            $no_puede = 'true';
                        }
                        $state = $this->conn->prepare('INSERT INTO excepcion (fecha, no_puede, idAlumno) VALUES (?,?,?)');
                        $state->bind_param('ssi', $excepcion->date_string, $no_puede, $idAlumno);
                        if ($state->execute()) {  //me guardo la excepcion
                            $idExcepcion = $this->conn->insert_id;
                        }

                        if (sizeof($excepcion->horarios) > 0) { //si tiene horarios agregados, inserto los mismos
                            foreach ($excepcion->horarios as $horarioRowTime) {
                                $dir_alt_excepcion = var_export($horarioRowTime->dir_alt, true);
                                $horariosTotales_string = implode (", ", $horarioRowTime->horariosTotales);
                                $state = $this->conn->prepare('INSERT INTO excepcionHorarios (dir_alt, horarios, idExcepcion) VALUES (?,?,?)');
                                $state->bind_param('ssi', $dir_alt_excepcion, $horariosTotales_string, $idExcepcion);
                                $state->execute();
                            }
                        }
                    }
                }
                
                /* SE CREA UN NUEVO CRONOGRAMA */
                $state = $this->conn->prepare('INSERT INTO cronograma (status, idAlumno, timestampGuardado) VALUES (?,?,?)');
                $status = "NO CONFIRMADO";
                $datetime = date('m/d/Y h:i:s a', time());
                $state->bind_param('sis', $status, $idAlumno, $datetime);
                if ($state->execute()) { //el insert del alumno fue exitoso
                    $idCronograma = $this->conn->insert_id;
                } else {
                    return false;
                }

                /* SE CARGAN LAS CLASES SIN CONFIRMAR BAJO EL NUEVO CRONOGRAMA */
                $i = 1;
                foreach ($selectedOptions as $option) {
                    $state = $this->conn->prepare('INSERT INTO clase (alumno, auto, fecha, horaInicio, idZona, idDireccion, idCronograma, status, nroClase) VALUES (?,?,?,?,?,?,?,?,?)');
                    $direccionClase = $idDireccionPrincipal;
                    if ($option->da) {
                        $direccionClase = $idDireccionAlternativa;
                    }
                    $state->bind_param('iissiiisi', $idAlumno, $option->id_auto, $option->fecha, $option->horario, $option->idZona, $direccionClase, $idCronograma, $status, $i);
                    $state->execute();
                    $i++;
                }
            } else {
                return false;
            }

            return $idCronograma;
        }

        //Funcion principal que se encargara de actualizar el cronograma correspondiente PREVIO a la confirmacion
        function actualizarCronogramaPendiente($idCronograma, $selectedOptions, $idAlumno, $studentName, $student_phone, $idDireccionPrincipal, $address, $idDireccionAlternativa, $address_alt, $idDisponibilidad, $disponibilidad, $idExcepciones, $excepciones) {
            /*
            SE HACE UN UPDATE DE:
            -Direccion principal
            -Direccion alternativa
            -Disponibilidad del alumno
            -El alumno
            -Excepciones que el alumno posea
            -El cronograma
            -Las clases asociadas a dicho cronograma
            */
            
            /* SE HACE UN UPDATE DE LA DIRECCION PRINCIPAL */            
            $state = $this->conn->prepare('UPDATE direccion SET calle = ?, calle_diag = ?, calle_a = ?, calle_a_diag = ?, calle_b = ?, calle_b_diag = ?, numero = ?, ciudad = ?, departamento = ?, floor_ = ?, observaciones = ? WHERE direccion.idDireccion = ?');
            $address_diag_string = var_export($address[0]->diag, true);
            $address_a_diag_string = var_export($address[1]->diag, true);
            $address_b_diag_string = var_export($address[2]->diag, true);

            $state->bind_param('sssssssssssi', $address[0]->street, $address_diag_string, $address[1]->street_a, $address_a_diag_string, $address[2]->street_b, $address_b_diag_string, $address[3]->altitud, $address[4]->city, $address[6]->department, $address[5]->floor, $address[7]->observations, $idDireccionPrincipal);
            $state->execute();
            
            /* SE INSERTA LA DIRECCION ALTERNATIVA SI ES QUE POSEE */
            if ($this->hayDireccionAlternativa($selectedOptions)) {

                /* SE EVALUA SI YA TENIA DIRECCION ALTERNATIVA PARA HACER UN UPDATE O UN INSERT */
                if ($idDireccionAlternativa == null) { //no tenia direccion alternativa
                    /* SE INSERTA LA DIRECCION ALTERNATIVA */            
                    $state = $this->conn->prepare('INSERT INTO direccion (calle, calle_diag, calle_a, calle_a_diag, calle_b, calle_b_diag, numero, ciudad, departamento, floor_, observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                    $address_diag_string = var_export($address_alt[0]->diag, true);
                    $address_a_diag_string = var_export($address_alt[1]->diag, true);
                    $address_b_diag_string = var_export($address_alt[2]->diag, true);

                    $state->bind_param('sssssssssss', $address_alt[0]->street, $address_diag_string, $address_alt[1]->street_a, $address_a_diag_string, $address_alt[2]->street_b, $address_b_diag_string, $address_alt[3]->altitud, $address_alt[4]->city, $address_alt[6]->department, $address_alt[5]->floor, $address_alt[7]->observations);
                    
                    if ($state->execute()) { //el insert de la direccion alternativa fue exitoso
                        $idDireccionAlternativa = $this->conn->insert_id;
                    } else {
                        return false;
                    }
                } else {
                    /* SE HACE UN UPDATE DE LA DIRECCION ALTERNATIVA */            
                    $state = $this->conn->prepare('UPDATE direccion SET calle = ?, calle_diag = ?, calle_a = ?, calle_a_diag = ?, calle_b = ?, calle_b_diag = ?, numero = ?, ciudad = ?, departamento = ?, floor_ = ?, observaciones = ? WHERE direccion.idDireccion = ?');
                    $address_diag_string = var_export($address_alt[0]->diag, true);
                    $address_a_diag_string = var_export($address_alt[1]->diag, true);
                    $address_b_diag_string = var_export($address_alt[2]->diag, true);

                    $state->bind_param('sssssssssssi', $address_alt[0]->street, $address_diag_string, $address_alt[1]->street_a, $address_a_diag_string, $address_alt[2]->street_b, $address_b_diag_string, $address_alt[3]->altitud, $address_alt[4]->city, $address_alt[6]->department, $address_alt[5]->floor, $address_alt[7]->observations, $idDireccionAlternativa);
                    $state->execute();
                }
            }

            /* SE HACE UN UPDATE DE LA DISPONIBILIDAD DEL ALUMNO */
            /* SE INSERTARA LA DISPONIBILIDAD DEL ALUMNO CON EL SIGUIENTE FORMATO:
            [DISPONIBILIDADES: STRING] | [DIRECCION_ALTERNATIVA: BOOLEAN]
            SI EL TRAMO HORARIO ES EN LA DIRECCION ALTERNATIVA SE PONDRA EN TRUE, SI ES EN LA PRINCIPAL EN FALSE. 
                */
            $disponibilidad_string = [];
            foreach ($disponibilidad as $dia) {
                if ($dia->all_day) {
                    $scheduleSend_string = implode (", ", $dia->option[0]->scheduleSend);
                    $scheduleSend_string .= '|'.var_export($dia->option[0]->dir_alt, true);
                    array_push($disponibilidad_string, $scheduleSend_string);
                } else {
                    if ($dia->option[0]->scheduleSend != null) {
                        $arrayTotal = [];
                        foreach ($dia->option as $option) {
                            $arrayTotal = array_merge($arrayTotal, $option->scheduleSend);
                            $scheduleSend_string = '|'.var_export($option->dir_alt, true);
                            array_push($arrayTotal, $scheduleSend_string);
                        }       
                        $scheduleSend_string = implode (", ", $arrayTotal);
                        array_push($disponibilidad_string, $scheduleSend_string);
                    } else {
                        array_push($disponibilidad_string, null);
                    }
                }
            }                
            $state = $this->conn->prepare('UPDATE disponibilidad SET Monday = ?, Tuesday = ?, Wednesday = ?, Thursday = ?, Friday = ?, Saturday = ?, Sunday = ? WHERE disponibilidad.idDisponibilidad = ?');
            $state->bind_param('sssssssi', $disponibilidad_string[0], $disponibilidad_string[1], $disponibilidad_string[2], $disponibilidad_string[3], $disponibilidad_string[4], $disponibilidad_string[5], $disponibilidad_string[6], $idDisponibilidad);
            $state->execute();
            
            /* SE HACE UN UPDATE DEL ALUMNO */
            $state = $this->conn->prepare('UPDATE alumno SET idDireccionAlt = ?, nombre = ?, telefono = ? WHERE alumno.idAlumno = ?');
            $state->bind_param('issi', $idDireccionAlternativa, $studentName, $student_phone, $idAlumno);
            $state->execute();
            
            /* SE INSERTAN LAS EXCEPCIONES SI POSEE */
            if (sizeof($excepciones) > 0) {
                /* ELIMINO LAS EXCEPCIONES QUE YA POSEA */
                if ($idExcepciones != null) {
                    foreach ($idExcepciones as $idExcepcion) {
                        /* SE ELIMINAN TODAS LAS EXCEPCIONES DEL ALUMNO*/
                        $state = $this->conn->prepare('DELETE FROM excepcionHorarios WHERE excepcionHorarios.idExcepcion = ?');
                        $state->bind_param('i', $idExcepcion);
                        $state->execute(); 

                        $state = $this->conn->prepare('DELETE FROM excepcion WHERE excepcion.idExcepcion = ?');
                        $state->bind_param('i', $idExcepcion);
                        $state->execute(); 
                    }
                }
                
                /* SE VUELVEN A AGREGAR O SE AGREGAN POR PRIMERA VEZ */
                foreach ($excepciones as $excepcion) {
                    $no_puede = 'false';
                    if ($excepcion->no_puede) {
                        $no_puede = 'true';
                    }
                    $state = $this->conn->prepare('INSERT INTO excepcion (fecha, no_puede, idAlumno) VALUES (?,?,?)');
                    $state->bind_param('ssi', $excepcion->date_string, $no_puede, $idAlumno);
                    if ($state->execute()) {  //me guardo la excepcion
                        $idExcepcion = $this->conn->insert_id;
                    }

                    if (sizeof($excepcion->horarios) > 0) { //si tiene horarios agregados, inserto los mismos
                        foreach ($excepcion->horarios as $horarioRowTime) {
                            $dir_alt_excepcion = var_export($horarioRowTime->dir_alt, true);
                            $horariosTotales_string = implode (", ", $horarioRowTime->horariosTotales);
                            $state = $this->conn->prepare('INSERT INTO excepcionHorarios (dir_alt, horarios, idExcepcion) VALUES (?,?,?)');
                            $state->bind_param('ssi', $dir_alt_excepcion, $horariosTotales_string, $idExcepcion);
                            $state->execute();
                        }
                    }
                }
            } else { //no tiene nuevas excepciones, por lo que debo eliminar las que ya tenia, si es que tenia
                if ($idExcepciones != null) {
                    foreach ($idExcepciones as $idExcepcion) {
                        /* SE ELIMINAN TODAS LAS EXCEPCIONES DEL ALUMNO*/
                        $state = $this->conn->prepare('DELETE FROM excepcionHorarios WHERE excepcionHorarios.idExcepcion = ?');
                        $state->bind_param('i', $idExcepcion);
                        $state->execute(); 

                        $state = $this->conn->prepare('DELETE FROM excepcion WHERE excepcion.idExcepcion = ?');
                        $state->bind_param('i', $idExcepcion);
                        $state->execute(); 
                    }
                }
            }

            /* SE HACE UN UPDATE DEL CRONOGRAMA PARA RESETEAR EL TIMESTAMP */
            $datetime = date('m/d/Y h:i:s a', time());
            $state = $this->conn->prepare('UPDATE cronograma SET timestampGuardado = ? WHERE cronograma.idCronograma = ?');
            $state->bind_param('si', $datetime, $idCronograma);
            $state->execute();

            /* SE ELIMINAN TODAS LAS CLASES DEL CRONOGRAMA Y SE INSERTAN LAS NUEVAS*/
            $state = $this->conn->prepare('DELETE FROM clase WHERE clase.idCronograma = ?');
            $state->bind_param('i', $idCronograma);
            $state->execute(); 

            /* SE INSERTAN LAS NUEVAS */
            $i = 1;
            foreach ($selectedOptions as $option) {
                $status = "NO CONFIRMADO";
                $state = $this->conn->prepare('INSERT INTO clase (alumno, auto, fecha, horaInicio, idZona, idDireccion, idCronograma, status, nroClase) VALUES (?,?,?,?,?,?,?,?,?)');
                $direccionClase = $idDireccionPrincipal;
                if ($option->da) {
                    $direccionClase = $idDireccionAlternativa;
                }
                $state->bind_param('iissiiisi', $idAlumno, $option->id_auto, $option->fecha, $option->horario, $option->idZona, $direccionClase, $idCronograma, $status, $i);
                $state->execute();
                $i++;
            }
            return $idCronograma;
        }

        //Funcion principal que se encargara de actualizar un cronograma ACTIVO
        function actualizarCronogramaActivo($idCronograma, $clasesModificadas, $selectedOptions, $idAlumno, $idDireccionPrincipal, $address, $idDireccionAlternativa, $address_alt, $idDisponibilidad, $disponibilidad, $idExcepciones, $excepciones) {
            /*
            SE HACE UN UPDATE DE:
            -Direccion principal
            -Direccion alternativa
            -Disponibilidad del alumno
            -El alumno
            -Excepciones que el alumno posea
            -El cronograma
            -Las clases asociadas a dicho cronograma
            -El contador de clase
            SE INSERTA EN LA NUEVA TABLA LAS CLASES QUE CAMBIARON ASOCIADAS A LAS NUEVAS
            */
        
  
            
            /* SE HACE UN UPDATE DE LA DIRECCION PRINCIPAL */            
            $state = $this->conn->prepare('UPDATE direccion SET calle = ?, calle_diag = ?, calle_a = ?, calle_a_diag = ?, calle_b = ?, calle_b_diag = ?, numero = ?, ciudad = ?, departamento = ?, floor_ = ?, observaciones = ? WHERE direccion.idDireccion = ?');
            $address_diag_string = var_export($address[0]->diag, true);
            $address_a_diag_string = var_export($address[1]->diag, true);
            $address_b_diag_string = var_export($address[2]->diag, true);

            $state->bind_param('sssssssssssi', $address[0]->street, $address_diag_string, $address[1]->street_a, $address_a_diag_string, $address[2]->street_b, $address_b_diag_string, $address[3]->altitud, $address[4]->city, $address[6]->department, $address[5]->floor, $address[7]->observations, $idDireccionPrincipal);
            $state->execute();
            
            /* SE INSERTA LA DIRECCION ALTERNATIVA SI ES QUE POSEE */
            if ($this->hayDireccionAlternativa($selectedOptions)) {

                /* SE EVALUA SI YA TENIA DIRECCION ALTERNATIVA PARA HACER UN UPDATE O UN INSERT */
                if ($idDireccionAlternativa == null) { //no tenia direccion alternativa
                    /* SE INSERTA LA DIRECCION ALTERNATIVA */            
                    $state = $this->conn->prepare('INSERT INTO direccion (calle, calle_diag, calle_a, calle_a_diag, calle_b, calle_b_diag, numero, ciudad, departamento, floor_, observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                    $address_diag_string = var_export($address_alt[0]->diag, true);
                    $address_a_diag_string = var_export($address_alt[1]->diag, true);
                    $address_b_diag_string = var_export($address_alt[2]->diag, true);

                    $state->bind_param('sssssssssss', $address_alt[0]->street, $address_diag_string, $address_alt[1]->street_a, $address_a_diag_string, $address_alt[2]->street_b, $address_b_diag_string, $address_alt[3]->altitud, $address_alt[4]->city, $address_alt[6]->department, $address_alt[5]->floor, $address_alt[7]->observations);
                    
                    if ($state->execute()) { //el insert de la direccion alternativa fue exitoso
                        $idDireccionAlternativa = $this->conn->insert_id;
                    } else {
                        return false;
                    }
                } else {
                    /* SE HACE UN UPDATE DE LA DIRECCION ALTERNATIVA */            
                    $state = $this->conn->prepare('UPDATE direccion SET calle = ?, calle_diag = ?, calle_a = ?, calle_a_diag = ?, calle_b = ?, calle_b_diag = ?, numero = ?, ciudad = ?, departamento = ?, floor_ = ?, observaciones = ? WHERE direccion.idDireccion = ?');
                    $address_diag_string = var_export($address_alt[0]->diag, true);
                    $address_a_diag_string = var_export($address_alt[1]->diag, true);
                    $address_b_diag_string = var_export($address_alt[2]->diag, true);

                    $state->bind_param('sssssssssssi', $address_alt[0]->street, $address_diag_string, $address_alt[1]->street_a, $address_a_diag_string, $address_alt[2]->street_b, $address_b_diag_string, $address_alt[3]->altitud, $address_alt[4]->city, $address_alt[6]->department, $address_alt[5]->floor, $address_alt[7]->observations, $idDireccionAlternativa);
                    $state->execute();
                }
            }

            /* SE HACE UN UPDATE DE LA DISPONIBILIDAD DEL ALUMNO */
            /* SE INSERTARA LA DISPONIBILIDAD DEL ALUMNO CON EL SIGUIENTE FORMATO:
            [DISPONIBILIDADES: STRING] | [DIRECCION_ALTERNATIVA: BOOLEAN]
            SI EL TRAMO HORARIO ES EN LA DIRECCION ALTERNATIVA SE PONDRA EN TRUE, SI ES EN LA PRINCIPAL EN FALSE. 
                */
            $disponibilidad_string = [];
            foreach ($disponibilidad as $dia) {
                if ($dia->all_day) {
                    $scheduleSend_string = implode (", ", $dia->option[0]->scheduleSend);
                    $scheduleSend_string .= '|'.var_export($dia->option[0]->dir_alt, true);
                    array_push($disponibilidad_string, $scheduleSend_string);
                } else {
                    if ($dia->option[0]->scheduleSend != null) {
                        $arrayTotal = [];
                        foreach ($dia->option as $option) {
                            $arrayTotal = array_merge($arrayTotal, $option->scheduleSend);
                            $scheduleSend_string = '|'.var_export($option->dir_alt, true);
                            array_push($arrayTotal, $scheduleSend_string);
                        }       
                        $scheduleSend_string = implode (", ", $arrayTotal);
                        array_push($disponibilidad_string, $scheduleSend_string);
                    } else {
                        array_push($disponibilidad_string, null);
                    }
                }
            }                
            $state = $this->conn->prepare('UPDATE disponibilidad SET Monday = ?, Tuesday = ?, Wednesday = ?, Thursday = ?, Friday = ?, Saturday = ?, Sunday = ? WHERE disponibilidad.idDisponibilidad = ?');
            $state->bind_param('sssssssi', $disponibilidad_string[0], $disponibilidad_string[1], $disponibilidad_string[2], $disponibilidad_string[3], $disponibilidad_string[4], $disponibilidad_string[5], $disponibilidad_string[6], $idDisponibilidad);
            $state->execute();
            
            /* SE HACE UN UPDATE DEL ALUMNO SOLO PARA LA DIR ALTERNATIVA*/
            $state = $this->conn->prepare('UPDATE alumno SET idDireccionAlt = ? WHERE alumno.idAlumno = ?');
            $state->bind_param('ii', $idDireccionAlternativa, $idAlumno);
            $state->execute();
            
            /* SE INSERTAN LAS EXCEPCIONES SI POSEE */
            if (sizeof($excepciones) > 0) {
                /* ELIMINO LAS EXCEPCIONES QUE YA POSEA */
                if ($idExcepciones != null) {
                    foreach ($idExcepciones as $idExcepcion) {
                        /* SE ELIMINAN TODAS LAS EXCEPCIONES DEL ALUMNO*/
                        $state = $this->conn->prepare('DELETE FROM excepcionHorarios WHERE excepcionHorarios.idExcepcion = ?');
                        $state->bind_param('i', $idExcepcion);
                        $state->execute(); 

                        $state = $this->conn->prepare('DELETE FROM excepcion WHERE excepcion.idExcepcion = ?');
                        $state->bind_param('i', $idExcepcion);
                        $state->execute(); 
                    }
                }
                
                /* SE VUELVEN A AGREGAR O SE AGREGAN POR PRIMERA VEZ */
                foreach ($excepciones as $excepcion) {
                    $no_puede = 'false';
                    if ($excepcion->no_puede) {
                        $no_puede = 'true';
                    }
                    $state = $this->conn->prepare('INSERT INTO excepcion (fecha, no_puede, idAlumno) VALUES (?,?,?)');
                    $state->bind_param('ssi', $excepcion->date_string, $no_puede, $idAlumno);
                    if ($state->execute()) {  //me guardo la excepcion
                        $idExcepcion = $this->conn->insert_id;
                    }

                    if (sizeof($excepcion->horarios) > 0) { //si tiene horarios agregados, inserto los mismos
                        foreach ($excepcion->horarios as $horarioRowTime) {
                            $dir_alt_excepcion = var_export($horarioRowTime->dir_alt, true);
                            $horariosTotales_string = implode (", ", $horarioRowTime->horariosTotales);
                            $state = $this->conn->prepare('INSERT INTO excepcionHorarios (dir_alt, horarios, idExcepcion) VALUES (?,?,?)');
                            $state->bind_param('ssi', $dir_alt_excepcion, $horariosTotales_string, $idExcepcion);
                            $state->execute();
                        }
                    }
                }
            } else { //no tiene nuevas excepciones, por lo que debo eliminar las que ya tenia, si es que tenia
                if ($idExcepciones != null) {
                    foreach ($idExcepciones as $idExcepcion) {
                        /* SE ELIMINAN TODAS LAS EXCEPCIONES DEL ALUMNO*/
                        $state = $this->conn->prepare('DELETE FROM excepcionHorarios WHERE excepcionHorarios.idExcepcion = ?');
                        $state->bind_param('i', $idExcepcion);
                        $state->execute(); 

                        $state = $this->conn->prepare('DELETE FROM excepcion WHERE excepcion.idExcepcion = ?');
                        $state->bind_param('i', $idExcepcion);
                        $state->execute(); 
                    }
                }
            }

            /* SE HACE UN UPDATE DEL CRONOGRAMA PARA RESETEAR EL TIMESTAMP */
            $datetime = date('m/d/Y h:i:s a', time());
            $state = $this->conn->prepare('UPDATE cronograma SET timestampModificado = ? WHERE cronograma.idCronograma = ?');
            $state->bind_param('si', $datetime, $idCronograma);
            $state->execute();
            
            /* SE UPDATEAN TODAS LAS CLASES CON EL STATUS DE MODIFICADO */
            $status = "MODIFICADO";
            foreach ($clasesModificadas as $clase) {
                $state = $this->conn->prepare('UPDATE clase SET status = ? WHERE clase.idClase = ?');
                $state->bind_param('si', $status, $clase);
                $state->execute(); 
            }

            $arrayIdClasesNuevos = (array) [];

            /* SE INSERTAN LAS NUEVAS */
            foreach ($selectedOptions as $option) {
                $status = "CONFIRMADO";
                $state = $this->conn->prepare('INSERT INTO clase (alumno, auto, fecha, horaInicio, idZona, idDireccion, idCronograma, status) VALUES (?,?,?,?,?,?,?,?)');
                $direccionClase = $idDireccionPrincipal;
                if ($option->da) {
                    $direccionClase = $idDireccionAlternativa;
                }
                $state->bind_param('iissiiis', $idAlumno, $option->id_auto, $option->fecha, $option->horario, $option->idZona, $direccionClase, $idCronograma, $status);
                $state->execute();
                if ($this->conn->error) {
                    echo json_encode($this->conn->error);

                }
                array_push($arrayIdClasesNuevos, $this->conn->insert_id);
            }

            /* RENUEVO EL NUMERO DE CLASE */
            $status = "CONFIRMADO";
            $state = $this->conn->prepare('SELECT clase.idClase FROM clase WHERE clase.status = ? AND clase.idCronograma = ? ORDER BY clase.fecha');
            $state->bind_param('si', $status, $idCronograma);
            $state->execute(); 
            $result = $state->get_result();

            if ($result->num_rows > 0) {
                $i = 1;
                while($row = $result->fetch_assoc()) {
                    $state = $this->conn->prepare('UPDATE clase SET nroClase = ? WHERE clase.idClase = ?');
                    $state->bind_param('ii', $i, $row['idClase']);
                    $state->execute(); 
                }
            }

            /* REMUEVO LA CLASE DE LA NUEVA TABLA SI ES QUE YA ERA UNA CLASE NUEVA, YA QUE AHORA VA A SER MODIFICADA */
            /* Y LAS INSERTO EN UN HISTORIAL DE MODIFICACIONES */
            foreach ($clasesModificadas as $clase) {
                $state = $this->conn->prepare('DELETE FROM claseModificadaRegistro WHERE claseModificadaRegistro.idClaseNueva = ?');
                $state->bind_param('i', $clase);
                $state->execute();
            }

            /* SE AGREGAN EN LA NUEVA TABLA QUE CLASE CAMBIO POR CUAL */
            $i = 0;
            foreach ($arrayIdClasesNuevos as $idNuevaClase) {
                $state = $this->conn->prepare('INSERT INTO claseModificadaRegistro (idClaseAnterior, idClaseNueva) VALUES (?,?)');
                $state->bind_param('ii', $clasesModificadas[$i], $idNuevaClase);
                $state->execute();
                $i++;
            }

            return $idCronograma;
        }

        function hayDireccionAlternativa($selectedOptions) {
            foreach($selectedOptions as $option) {
                if ($option->da) {
                    return true;
                }
            }
            return false;
        }

        function obtenerCronogramas() { 
            $statusNo = "MODIFICADO";
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
            cronograma.timestampGuardado,
            cronograma.status AS statusCronograma,
            cronograma.timestampCancelado,
            cronograma.timestampFinalizado,
            cronograma.timestampActivo,
            cronograma.motivoBaja as motivoBajaCronograma,
            alumnocronogramaclasestomadas.cantClasesTomadas,
            alumnocronogramaclasestomadas.cantClasesTotales,
            excepcion.idExcepcion, excepcion.fecha AS fechaExcepcion, excepcion.no_puede,
            excepcionhorarios.dir_alt, excepcionhorarios.horarios,
            disponibilidad.idDisponibilidad, disponibilidad.Monday, disponibilidad.Tuesday, disponibilidad.Wednesday, disponibilidad.Thursday, disponibilidad.Friday, disponibilidad.Saturday, disponibilidad.Sunday,
            alumno.idAlumno, alumno.nombre, alumno.telefono,
            cmr.idClaseNueva, cmr.idClaseAnterior,
            clase.idClase, clase.idCronograma, clase.alumno, clase.auto, clase.sumada, clase.claseCancelada, clase.motivoCancelacion,
            clase.fecha, clase.horaInicio, clase.idZona, clase.idDireccion, clase.status AS satusClase, cronograma.status AS cronogramaStatus 
            FROM clase 
            INNER JOIN cronograma ON cronograma.idCronograma = clase.idCronograma 
            INNER JOIN alumno ON clase.alumno = alumno.idAlumno 
            INNER JOIN direccion AS d1 ON d1.idDireccion = alumno.idDireccion 
            LEFT JOIN direccion AS d2 ON d2.idDireccion = alumno.idDireccionAlt
            INNER JOIN disponibilidad ON disponibilidad.idDisponibilidad = alumno.idDisponibilidad
            LEFT JOIN excepcion ON excepcion.idAlumno = alumno.idAlumno
            LEFT JOIN excepcionhorarios ON excepcion.idExcepcion = excepcionhorarios.idExcepcion
            LEFT JOIN clasemodificadaregistro cmr ON cmr.idClaseNueva = clase.idClase
            LEFT JOIN alumnocronogramaclasestomadas ON alumnocronogramaclasestomadas.idCronograma = clase.idCronograma
            WHERE clase.status <> ?
            ORDER BY cronograma.idCronograma DESC');

            $state->bind_param('s', $statusNo);
            
            $cronogramas = array();
            $cronogramaContainer = (object) [
                'cronogramasConfirmados' => [],
                'cronogramasPendientes' => [],
                'cronogramasFinalizados' => [],
                'cronogramasCancelados' => []
            ];

            $cronogramasConfirmados = array();
            $cronogramasPendientes = array();
            $cronogramasFinalizados = array();
            $cronogramasCancelados = array();

            if ($state->execute()) { //si la consulta fue exitosa
                $result = $state->get_result();
                while($row = $result->fetch_assoc()) {
                    $dirAlternativa = null;
                    if ($row['id_DirAlternativa'] != null) {
                        $dirAlternativa = $this->obtenerDireccionParaMostrar($row['calle_DirAlternativa'], filter_var($row['calle_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirAlternativa'], filter_var($row['calle_a_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirAlternativa'], filter_var($row['calle_b_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirAlternativa'], $row['ciudad_DirAlternativa'], $row['floor_DirAlternativa'], $row['departamento_DirAlternativa']);
                    }

                    if ($row['idDireccion'] == $row['id_DirAlternativa']) {
                        $row['usandoDirAlternativa'] = true;
                        $row['direccionClaseFormateada'] = $this->obtenerDireccionParaMostrar($row['calle_DirAlternativa'], filter_var($row['calle_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirAlternativa'], filter_var($row['calle_a_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirAlternativa'], filter_var($row['calle_b_diag_DirAlternativa'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirAlternativa'], $row['ciudad_DirAlternativa'], $row['floor_DirAlternativa'], $row['departamento_DirAlternativa']);
                    } else {
                        $row['usandoDirAlternativa'] = false;
                        $row['direccionClaseFormateada'] = $this->obtenerDireccionParaMostrar($row['calle_DirPrincipal'], filter_var($row['calle_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirPrincipal'], filter_var($row['calle_a_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirPrincipal'], filter_var($row['calle_b_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirPrincipal'], $row['ciudad_DirPrincipal'], $row['floor_DirPrincipal'], $row['departamento_DirPrincipal']);
                    }

                    $direccionPrincipalFormateada = $this->obtenerDireccionParaMostrar($row['calle_DirPrincipal'], filter_var($row['calle_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['calle_a_DirPrincipal'], filter_var($row['calle_a_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['calle_b_DirPrincipal'], filter_var($row['calle_b_diag_DirPrincipal'], FILTER_VALIDATE_BOOLEAN), $row['numero_DirPrincipal'], $row['ciudad_DirPrincipal'], $row['floor_DirPrincipal'], $row['departamento_DirPrincipal']);
                    $disponibilidades = (object) [
                        'Monday' => $this->verificarSiEsTodoElDia($row['Monday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $direccionPrincipalFormateada, $dirAlternativa),
                        'Tuesday' => $this->verificarSiEsTodoElDia($row['Tuesday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $direccionPrincipalFormateada, $dirAlternativa),
                        'Wednesday' => $this->verificarSiEsTodoElDia($row['Wednesday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $direccionPrincipalFormateada, $dirAlternativa),
                        'Thursday' => $this->verificarSiEsTodoElDia($row['Thursday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $direccionPrincipalFormateada, $dirAlternativa),
                        'Friday' => $this->verificarSiEsTodoElDia($row['Friday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $direccionPrincipalFormateada, $dirAlternativa),
                        'Saturday' => $this->verificarSiEsTodoElDia($row['Saturday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $direccionPrincipalFormateada, $dirAlternativa),
                        'Sunday' => $this->verificarSiEsTodoElDia($row['Sunday'],$row['id_DirPrincipal'],$row['id_DirAlternativa'], $direccionPrincipalFormateada, $dirAlternativa)
                    ];

                    $excepciones = (array) [];
                    $excepcion = (object) [
                        'idExcepcion' => null,
                        'fecha' => null,
                        'no_puede' => null,
                        'horarios' => []
                    ];

                    $horario = (object) [
                        'tramoHorario' => '',
                        'usandoDirAlt' => false
                    ];

                    if ($row['idExcepcion'] != null) { //tiene excepciones
                        $excepcion = (object) [
                            'idExcepcion' => $row['idExcepcion'],
                            'fecha' => $row['fechaExcepcion'],
                            'no_puede' => filter_var($row['no_puede'], FILTER_VALIDATE_BOOLEAN),
                            'horarios' => []
                        ];

                        if ($row['horarios'] != null) { //tiene horarios
                            $horario->tramoHorario = explode(",", str_replace(", ", ",", $row['horarios']));
                            $horario->usandoDirAlt = filter_var($row['dir_alt'], FILTER_VALIDATE_BOOLEAN);
                            array_push($excepcion->horarios, $horario);
                        }

                        array_push($excepciones, $excepcion);
                    }

                    if ($row['idClaseNueva'] != null && $row['idClaseNueva'] != '') {
                        $row['tieneClaseModificada'] = true;
                        $row['claseModificada'] = $this->getInfoClaseModificada($row['idClaseAnterior']);
                    } else {
                        $row['tieneClaseModificada'] = false;
                    }
                    
                    $cronogramaObject = (object) [
                        'idCronograma' => $row['idCronograma'],
                        'statusCronograma' => $row['statusCronograma'],
                        'alumno' => $row['alumno'],
                        'idDisponibilidad' => $row['idDisponibilidad'],
                        'nombreAlumno' => $row['nombre'],
                        'cantClasesTomadas' => $row['cantClasesTomadas'],
                        'cantClasesTotales' => $row['cantClasesTotales'],
                        'cantClasesRestantes' => $row['cantClasesTotales'] - $row['cantClasesTomadas'],
                        'telefonoAlumno' => $row['telefono'],
                        'direccionPrincipalFormateada' => $direccionPrincipalFormateada,
                        'idDireccionPrincipal' => $row['id_DirPrincipal'],
                        'fechaHoraGuardado' => $row['timestampGuardado'],
                        'timestampFinalizado' => $row['timestampFinalizado'],
                        'timestampCancelado' => $row['timestampCancelado'],
                        'timestampActivo' => $row['timestampActivo'],
                        'motivoBajaCronograma' => $row['motivoBajaCronograma'],
                        'direccionAlternativaFormateada' => $dirAlternativa,
                        'idDireccionAlternativa' => $row['id_DirAlternativa'],
                        'disponibilidades' => $disponibilidades,
                        'excepciones' => $excepciones,
                        'clases' => array($row)
                    ];

                    $found = false;
                    foreach ($cronogramas as $cronograma) {
                        if ($cronograma->idCronograma == $row['idCronograma']) {
                            $found = true;

                            $foundExcepcion = false;

                            foreach ($cronograma->excepciones as $excepcionGuardada) {
                                if ($excepcion->idExcepcion == $excepcionGuardada->idExcepcion) {
                                    $foundExcepcion = true;
                                    
                                    $horarioFound = false;
                                    foreach ($excepcionGuardada->horarios as $horario) {
                                        if ($horario->tramoHorario == explode(",", str_replace(", ", ",", $row['horarios']))) {
                                            $horarioFound = true;
                                            break;
                                        }
                                    }
                                    
                                    if (!$horarioFound) {
                                        $horario = (object) [
                                            'tramoHorario' => '',
                                            'usandoDirAlt' => false
                                        ];

                                        $horario->tramoHorario = explode(",",str_replace(", ", ",", $row['horarios']));
                                        $horario->usandoDirAlt = filter_var($row['dir_alt'], FILTER_VALIDATE_BOOLEAN);
                                        array_push($excepcionGuardada->horarios, $horario);
                                        usort($excepcionGuardada->horarios, array($this, 'excepciones_tramos_compare'));
                                    }
                                }
                            }

                            if (!$foundExcepcion) {
                                array_push($cronograma->excepciones, $excepcion);
                            }

                            $claseFound = false;
                            foreach ($cronograma->clases as $clase) {
                                if ($clase['idClase'] == $row['idClase']) {
                                    $claseFound = true;
                                break;
                                }
                            }

                            if (!$claseFound) {
                                array_push($cronograma->clases, $row);
                            }

                            usort($cronograma->excepciones, array($this, 'excepciones_compare'));
                            usort($cronograma->clases, array($this, 'date_compare'));
                            break;
                        }
                    }
                    if (!$found) {
                        array_push($cronogramas, $cronogramaObject);
                    }
                }
            } else {
                return 1;
            }
            
            foreach ($cronogramas as $cronograma) {
                if ($cronograma->statusCronograma == "NO CONFIRMADO") {
                    array_push($cronogramasPendientes, $cronograma);
                }

                if ($cronograma->statusCronograma == "CONFIRMADO") {
                    array_push($cronogramasConfirmados, $cronograma);
                }

                if ($cronograma->statusCronograma == "FINALIZADO") {
                    array_push($cronogramasFinalizados, $cronograma);
                }

                if($cronograma->statusCronograma == "CANCELADO") {
                    array_push($cronogramasCancelados, $cronograma);
                }
            }

            $cronogramaContainer->cronogramasConfirmados = $cronogramasConfirmados;
            $cronogramaContainer->cronogramasPendientes = $cronogramasPendientes;
            $cronogramaContainer->cronogramasFinalizados = $cronogramasFinalizados;
            $cronogramaContainer->cronogramasCancelados = $cronogramasCancelados;
            return $cronogramaContainer;
        }

        function clasesActualesCronograma($idCronograma, $fecha) {
           //$fechaString = $fecha->format('Y-m-d');
           /* SE VERIFICA SI LAS CLASES AUN ESTAN DISPONIBLES */
           $status = "CONFIRMADO";
           $state = $this->conn->prepare('SELECT * FROM clase WHERE clase.status = ? AND clase.idCronograma = ?');
           $state->bind_param('si', $status, $idCronograma);
           $state->execute(); 

           $result = $state->get_result();

           $clasesActivas = (array) [];
           if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
               array_push($clasesActivas, $row);
            }
           } else {
               return [];
           }
           return $clasesActivas;
        }

        function usandoDirAlt($idDireccion, $idAlumno) {
            $state = $this->conn->prepare('SELECT * FROM alumno WHERE alumno.idAlumno = ?');
            $state->bind_param('i', $idAlumno);
            $state->execute(); 
            $result = $state->get_result();

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                   if ($row['idDireccion'] == $idDireccion) {
                       return false;
                   } else {
                       return true;
                   }
                }
            }
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

        function confirmarCronograma($idCronograma, $idAlumno, $direccionFisica, $clases, $documento) { 
            $clasesExistentes = [];
            foreach ($clases as $key=>$clase) {
                $status = "CONFIRMADO";
                /* SE VERIFICA SI LAS CLASES AUN ESTAN DISPONIBLES */
                $state = $this->conn->prepare('SELECT clase.idClase FROM clase WHERE clase.fecha = ? AND clase.horaInicio = ? AND clase.auto = ? AND clase.status = ?');
                $state->bind_param('ssis', $clase->fecha, $clase->horaInicio, $clase->auto, $status);
                $state->execute();

                $result = $state->get_result();

                if ($result->num_rows > 0) {
                    array_push($clasesExistentes, $key + 1);
                }   
            }

            if (empty($clasesExistentes)) {
                $status = "CONFIRMADO";
                $state = $this->conn->prepare('UPDATE clase SET status = ? WHERE clase.idCronograma = ?');
                $state->bind_param('si', $status, $idCronograma);
                $state->execute();
                $now = date('Y-m-d h:i:s a', time());
                $state = $this->conn->prepare('UPDATE cronograma SET status = ?, timestampActivo = ? WHERE cronograma.idCronograma = ?');
                $state->bind_param('ssi', $status, $now, $idCronograma);
                $state->execute();
                $confirmado = 'true'; //INDICA QUE EL ALUMNO YA ES UN ALUMNO FIJO
                $today = date('Y-m-d');

                //INSERT CANT CLASES TOMADAS
                $state = $this->conn->prepare('INSERT INTO alumnocronogramaclasestomadas (idAlumno, idCronograma, cantClasesTomadas, cantClasesTotales) VALUES (?,?,?,?)');
                $clasesTomadas = 0;
                $totalClases = sizeof($clases);
                $state->bind_param('iiii', $idAlumno, $idCronograma, $clasesTomadas, $totalClases);
                $state->execute();

                $idDireccionFisica;
                if (!$direccionFisica->nuevaDireccion) {
                    $idDireccionFisica = $direccionFisica->idDireccionSeleccionada;
                } else {
                    $state = $this->conn->prepare('INSERT INTO direccion (calle, calle_diag, calle_a, calle_a_diag, calle_b, calle_b_diag, numero, ciudad, departamento, floor_, observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                    $address_diag_string = var_export($direccionFisica->direccion->diag, true);
                    $address_a_diag_string = var_export($direccionFisica->direccion->diag_a, true);
                    $address_b_diag_string = var_export($direccionFisica->direccion->diag_b, true);
        
                    $state->bind_param('sssssssssss', $direccionFisica->direccion->street, $address_diag_string, $direccionFisica->direccion->street_a, $address_a_diag_string, $direccionFisica->direccion->street_b, $address_b_diag_string, $direccionFisica->direccion->altitud, $direccionFisica->direccion->city, $direccionFisica->direccion->department, $direccionFisica->direccion->floor, $direccionFisica->direccion->observations);
                    $state->execute();
                    $idDireccionFisica = $this->conn->insert_id;
                }


                $state = $this->conn->prepare('UPDATE alumno SET confirmado = ?, activo = ?, fechaConfirmacion = ?, documento = ?, idDireccionFisica = ? WHERE alumno.idAlumno = ?');
                $state->bind_param('ssssii', $confirmado, $confirmado, $today, $documento, $idDireccionFisica, $idAlumno);
    
                if ($state->execute()) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return $clasesExistentes;
            }
        }

        function obtenerClasesPorFecha($fecha) {
            $fechaString = $fecha;
            $state = $this->conn->prepare(
            'SELECT * FROM auto 
            LEFT JOIN clase ON auto.idAuto = clase.auto AND clase.fecha = ? AND clase.status = ? 
            LEFT JOIN alumno ON clase.alumno = alumno.idAlumno 
            LEFT JOIN direccion ON clase.idDireccion = direccion.idDireccion
            LEFT JOIN alumnocronogramaclasestomadas act ON act.idCronograma = clase.idCronograma
            LEFT JOIN clasemodificadaregistro cmr ON cmr.idClaseNueva = clase.idClase
            ORDER BY clase.horaInicio');
            $status = 'CONFIRMADO';
            $state->bind_param('ss', $fechaString, $status);
            $state->execute();
            $result = $state->get_result();

            $cronograma = (array) [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $row['direccionFormateada'] = $this->obtenerDireccionParaMostrar($row['calle'], filter_var($row['calle_diag'], FILTER_VALIDATE_BOOLEAN), $row['calle_a'], filter_var($row['calle_a_diag'], FILTER_VALIDATE_BOOLEAN), $row['calle_b'], filter_var($row['calle_b_diag'], FILTER_VALIDATE_BOOLEAN), $row['numero'], $row['ciudad'], $row['floor_'], $row['departamento']);
                    if ($row['idClaseNueva'] != null) {
                        $row['tieneClaseModificada'] = true;
                        $row['claseModificada'] = $this->getInfoClaseModificada($row['idClaseAnterior']);
                    }
                    $cronograma[$row['idAuto']][] = $row;
                }
            } else {
                return [];
            }
            return $cronograma;          
        }

        function getInfoClaseModificada($idClaseModificada) {
            $state = $this->conn->prepare('SELECT * FROM clase WHERE clase.idClase = ?');
            $state->bind_param('i', $idClaseModificada);
            $state->execute();
            $result = $state->get_result();
            $claseModificada = null;
            while($row = $result->fetch_assoc()) {
                $claseModificada = $row;
            }
            return $claseModificada;
        }

        function cancelarCronograma($idCronograma, $idAlumno) {
            $state = $this->conn->prepare('DELETE FROM clase WHERE clase.idCronograma = ?');
            $state->bind_param('i', $idCronograma);
            if ($state->execute()) { 
                $state = $this->conn->prepare('DELETE FROM cronograma WHERE cronograma.idCronograma = ?');
                $state->bind_param('i', $idCronograma);
                if ($state->execute()) {
                    $state = $this->conn->prepare('SELECT alumno.confirmado, alumno.idDisponibilidad, alumno.idDireccion, alumno.idDireccionAlt FROM alumno WHERE alumno.idAlumno = ?');
                    $state->bind_param('i', $idAlumno);
                    if ($state->execute()) {
                        $result = $state->get_result();

                        $confirmado;
                        $idDisponibilidad;
                        $idDireccion;
                        $idDireccionAlternativa;
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $confirmado = filter_var($row['confirmado'], FILTER_VALIDATE_BOOLEAN);
                                $idDisponibilidad = $row['idDisponibilidad'];
                                $idDireccion = $row['idDireccion'];
                                $idDireccionAlternativa = $row['idDireccionAlt'];
                            }
                        }               


                        if (!$confirmado) { //SI EL ALUMNO ES ABSOLUTAMENTE NUEVO
                            //ELIMINO SU DISPONIBILIDAD
                            $state = $this->conn->prepare('DELETE FROM disponibilidad WHERE disponibilidad.idDisponibilidad = ?');
                            $state->bind_param('i', $idDisponibilidad);
                            if ($state->execute()) {
                                //ELIMINO SU DIRECCION
                                $state = $this->conn->prepare('DELETE FROM direccion WHERE direccion.idDireccion = ?');
                                $state->bind_param('i', $idDireccion);
                                if ($state->execute()) {
                                    if ($idDireccionAlternativa != null) {
                                        //ELIMINO SU DIRECCION ALTERNATIVA
                                        $state = $this->conn->prepare('DELETE FROM direccion WHERE direccion.idDireccion = ?');
                                        $state->bind_param('i', $idDireccionAlternativa);
                                        $state->execute();
                                    }
                                    
                                    //ELIMINO AL ALUMNO
                                    $state = $this->conn->prepare('DELETE FROM alumno WHERE alumno.idAlumno = ?');
                                    $state->bind_param('i', $idAlumno);
                                    if ($state->execute()) {
                                        return true;
                                    } else {
                                        return false;
                                    }
                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        function cancelarCronogramaActivo($idCronograma, $idAlumno, $motivoBaja) {
            $cancelado = "CANCELADO";
            $now = date('Y-m-d h:i:s a', time());
            $state = $this->conn->prepare('UPDATE clase SET clase.status = ? WHERE clase.idCronograma = ?');
            $state->bind_param('si', $cancelado, $idCronograma);
            if ($state->execute()) { 
                $state = $this->conn->prepare('UPDATE cronograma SET cronograma.status = ?, cronograma.timestampCancelado = ?, motivoBaja = ? WHERE cronograma.idCronograma = ?');
                $state->bind_param('sssi', $cancelado, $now, $motivoBaja, $idCronograma);
                if ($state->execute()) {
                    $inactivo = "false";
                    $state = $this->conn->prepare('UPDATE alumno SET activo = ?, motivoBaja = ?, fechaBaja = ? WHERE alumno.idAlumno = ?');
                    $state->bind_param('sssi', $inactivo, $motivoBaja, $now, $idAlumno);
                    if ($state->execute()) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        function agregarClaseACronograma($idCronograma, $idAlumno, $selectedOption, $fechaClase) {
            //Agrego la nueva clase
            $claseExtra = "true";
            $status = "CONFIRMADO";
            $nroClase = 20; //hardcodeado
            $idDireccion;
            $idDireccion_alt;
            $sumada = "false";

            //Obtengo las dos direcciones del alumno
            $state = $this->conn->prepare('SELECT alumno.idDireccion, alumno.idDireccionAlt FROM alumno WHERE alumno.idAlumno = ?');
            $state->bind_param('i', $idAlumno);
            $state->execute(); 
            $result = $state->get_result();
            while($row = $result->fetch_assoc()) {
                $idDireccion = $row['idDireccion'];
                $idDireccion_alt = $row['idDireccionAlt'];
            }

            $state = $this->conn->prepare('INSERT INTO clase (alumno, auto, fecha, horaInicio, idZona, idDireccion, idCronograma, status, nroClase, claseExtra, sumada) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $direccionClase = $idDireccion;
            if ($selectedOption->usandoDirAlt) {
                $direccionClase = $idDireccion_alt;
            }
            $state->bind_param('iissiiisiss', $idAlumno, $selectedOption->idAuto, $fechaClase, $selectedOption->horaInicio, $selectedOption->idZona, $direccionClase, $idCronograma, $status, $nroClase, $claseExtra, $sumada);
            $state->execute();

            //Reordeno el nro de clase
            $state = $this->conn->prepare('SELECT clase.idClase FROM clase WHERE clase.status = ? AND clase.idCronograma = ? ORDER BY clase.fecha');
            $state->bind_param('si', $status, $idCronograma);
            $state->execute(); 
            $result = $state->get_result();

            $nuevoTotalClases = $result->num_rows;
            if ($nuevoTotalClases > 0) {
                $i = 1;
                while($row = $result->fetch_assoc()) {
                    $state = $this->conn->prepare('UPDATE clase SET nroClase = ? WHERE clase.idClase = ?');
                    $state->bind_param('ii', $i, $row['idClase']);
                    $state->execute(); 
                }
            }

            //update timestamp cronograma modificado
            $datetime = date('m/d/Y h:i:s a', time());
            $state = $this->conn->prepare('UPDATE cronograma SET timestampModificado = ? WHERE cronograma.idCronograma = ?');
            $state->bind_param('si', $datetime, $idCronograma);
            $state->execute(); 

            //aumento el nuevo total de cantidad de clases totales del cronograma
            $state = $this->conn->prepare('UPDATE alumnocronogramaclasestomadas SET cantClasesTotales = ? WHERE alumnocronogramaclasestomadas.idCronograma = ?');
            $state->bind_param('ii', $nuevoTotalClases, $idCronograma);
            $state->execute(); 
        }

        function generarSearch($idAlumno) {
            $searchResult = (array) [
                'lessons' => 1,
                'date' => date('Y-m-d'),
                'address' => [],
                'address_alternative' => [],
                'dates_times' => []
            ];

            $excepcionResult = (array) [ ];

            $idDisponibilidad;
            $disponibilidad_info;
            $excepciones;
            $idDireccion;
            $direccionInfo;
            $idDireccion_alt;
            $direccion_alt_info;

            //Obtengo los ids del alumno.
            $state = $this->conn->prepare('SELECT * FROM alumno WHERE alumno.idAlumno = ?');
            $state->bind_param('i', $idAlumno);
            $state->execute();
            $result = $state->get_result();
            while($row = $result->fetch_assoc()) {
                $idDisponibilidad = $row['idDisponibilidad'];
                $idDireccion = $row['idDireccion'];
                $idDireccion_alt = $row['idDireccionAlt'];
            }

            //Obtengo la direccion principal
            $state = $this->conn->prepare('SELECT * FROM direccion WHERE direccion.idDireccion = ?');
            $state->bind_param('i', $idDireccion);
            $state->execute();
            $result = $state->get_result();
            while($row = $result->fetch_assoc()) {
                $direccionInfo = $row;
            }

            //Obtengo la direccion alternativa
            if ($idDireccion_alt != null) {
                $state = $this->conn->prepare('SELECT * FROM direccion WHERE direccion.idDireccion = ?');
                $state->bind_param('i', $idDireccion_alt);
                $state->execute();
                $result = $state->get_result();
                while($row = $result->fetch_assoc()) {
                    $direccion_alt_info = $row;
                }
            }

            //Obtengo la disponibilidad
            $state = $this->conn->prepare('SELECT * FROM disponibilidad WHERE disponibilidad.idDisponibilidad = ?');
            $state->bind_param('i', $idDisponibilidad);
            $state->execute();
            $result = $state->get_result();
            while($row = $result->fetch_assoc()) {
                $disponibilidad_info = $row;
            }

            $disponibilidadesInfoPorDia = (object) [
                'Monday' => $this->verificarSiEsTodoElDia($disponibilidad_info['Monday'],$idDireccion,$idDireccion_alt, null, null, null, null),
                'Tuesday' => $this->verificarSiEsTodoElDia($disponibilidad_info['Tuesday'],$idDireccion,$idDireccion_alt, null, null, null, null),
                'Wednesday' => $this->verificarSiEsTodoElDia($disponibilidad_info['Wednesday'],$idDireccion,$idDireccion_alt, null, null, null, null),
                'Thursday' => $this->verificarSiEsTodoElDia($disponibilidad_info['Thursday'],$idDireccion,$idDireccion_alt, null, null, null, null),
                'Friday' => $this->verificarSiEsTodoElDia($disponibilidad_info['Friday'],$idDireccion,$idDireccion_alt, null, null, null, null),
                'Saturday' => $this->verificarSiEsTodoElDia($disponibilidad_info['Saturday'],$idDireccion,$idDireccion_alt, null, null, null, null),
                'Sunday' => $this->verificarSiEsTodoElDia($disponibilidad_info['Sunday'],$idDireccion,$idDireccion_alt, null, null, null, null)
            ];

            //Completo las disponibilidades como esta en el objecto search.
            $diaEntero = ['08:00', '09:00', '10:00', '11:15', '12:15', '13:15', '14:30', '15:30', '16:30', '17:45', '18:45', '19:45'];
            
            $dates_times = [];
            foreach ($disponibilidadesInfoPorDia as $dayKey => $valueKey) {
                $search_dates_dayObject = (array) [
                    'name_day' => '',
                    'all_day' => false,
                    'option' => [],
                ];

                $option = (array) [
                    'scheduleSend' => [],
                    'dir_alt' => false
                ];

                $search_dates_dayObject['name_day'] = $dayKey;

                if ($valueKey->todoElDia) {
                    $search_dates_dayObject['all_day'] = true;
                    $option['scheduleSend'] = $diaEntero;
                    $option['dir_alt'] = $valueKey->usandoDirAlternativa;
                    array_push($search_dates_dayObject['option'], $option);
                } else {
                    foreach ($valueKey->tramosHorarios as $tramoHorario) {
                        $option = (array) [
                            'scheduleSend' => [],
                            'dir_alt' => false
                        ];
                        $option['scheduleSend'] = $tramoHorario->horarios;
                        $option['dir_alt'] = $tramoHorario->usandoDirAlternativa;
                        array_push($search_dates_dayObject['option'], $option);
                    }
                }

                array_push($dates_times, $search_dates_dayObject);
            }

            $searchResult['dates_times'] = $dates_times;

            //Obtengo la direccion principal como esta en el objecto search.
            $address_array = [];
            $address_object = (array) [
                'street' => '',
                'diag' => false
            ];

            $address_object['street'] = $direccionInfo['calle'];
            $address_object['diag'] = filter_var($direccionInfo['calle_diag'], FILTER_VALIDATE_BOOLEAN);
            array_push($address_array, $address_object);

            $address_object = (array) [
                'street_a' => '',
                'diag' => false
            ];

            $address_object['street_a'] = $direccionInfo['calle_a'];
            $address_object['diag'] = filter_var($direccionInfo['calle_a_diag'], FILTER_VALIDATE_BOOLEAN);
            array_push($address_array, $address_object);

            $address_object = (array) [
                'street_b' => '',
                'diag' => false
            ];

            $address_object['street_b'] = $direccionInfo['calle_b'];
            $address_object['diag'] = filter_var($direccionInfo['calle_b_diag'], FILTER_VALIDATE_BOOLEAN);
            array_push($address_array, $address_object);

            $address_object = (array) [
                'altitud' => '',
                'diag' => false
            ];

            $address_object['altitud'] = $direccionInfo['numero'];
            array_push($address_array, $address_object);

            $address_object = (array) [
                'city' => '',
                'diag' => false
            ];

            $address_object['city'] = $direccionInfo['ciudad'];
            array_push($address_array, $address_object);

            $address_object = (array) [
                'city' => '',
                'floor' => '',
                'diag' => false
            ];

            $address_object['floor'] = $direccionInfo['floor_'];
            array_push($address_array, $address_object);

            $address_object = (array) [
                'city' => '',
                'floor' => '',
                'departamento' => '',
                'diag' => false
            ];

            $address_object['departamento'] = $direccionInfo['departamento'];
            array_push($address_array, $address_object);

            $address_object = (array) [
                'city' => '',
                'floor' => '',
                'observations' => '',
                'diag' => false
            ];

            $address_object['observations'] = $direccionInfo['observaciones'];
            array_push($address_array, $address_object);

            $searchResult['address'] = $address_array;

            //Obtengo la direccion alternativa como esta en el objecto search.
            $address_alternative_array = [];
            if ($idDireccion_alt != null) {
                $address_object = (array) [
                    'street' => '',
                    'diag' => false
                ];
    
                $address_object['street'] = $direccion_alt_info['calle'];
                $address_object['diag'] = filter_var($direccion_alt_info['calle_diag'], FILTER_VALIDATE_BOOLEAN);
                array_push($address_alternative_array, $address_object);
    
                $address_object = (array) [
                    'street_a' => '',
                    'diag' => false
                ];
    
                $address_object['street_a'] = $direccion_alt_info['calle_a'];
                $address_object['diag'] = filter_var($direccion_alt_info['calle_a_diag'], FILTER_VALIDATE_BOOLEAN);
                array_push($address_alternative_array, $address_object);
    
                $address_object = (array) [
                    'street_b' => '',
                    'diag' => false
                ];
    
                $address_object['street_b'] = $direccion_alt_info['calle_b'];
                $address_object['diag'] = filter_var($direccion_alt_info['calle_b_diag'], FILTER_VALIDATE_BOOLEAN);
                array_push($address_alternative_array, $address_object);
    
                $address_object = (array) [
                    'altitud' => '',
                    'diag' => false
                ];
    
                $address_object['altitud'] = $direccion_alt_info['numero'];
                array_push($address_alternative_array, $address_object);
    
                $address_object = (array) [
                    'city' => '',
                    'diag' => false
                ];
    
                $address_object['city'] = $direccion_alt_info['ciudad'];
                array_push($address_alternative_array, $address_object);
    
                $address_object = (array) [
                    'city' => '',
                    'floor' => '',
                    'diag' => false
                ];
    
                $address_object['floor'] = $direccion_alt_info['floor_'];
                array_push($address_alternative_array, $address_object);
    
                $address_object = (array) [
                    'city' => '',
                    'floor' => '',
                    'departamento' => '',
                    'diag' => false
                ];
    
                $address_object['departamento'] = $direccion_alt_info['departamento'];
                array_push($address_alternative_array, $address_object);
    
                $address_object = (array) [
                    'city' => '',
                    'floor' => '',
                    'observations' => '',
                    'diag' => false
                ];
    
                $address_object['observations'] = $direccion_alt_info['observaciones'];
                array_push($address_alternative_array, $address_object);
    
            }
            
            $searchResult['address_alternative'] = $address_alternative_array;

            //Obtengo las excepciones (si es que posee).
            $state = $this->conn->prepare('SELECT * FROM excepcion WHERE excepcion.idAlumno = ?');
            $state->bind_param('i', $idAlumno);
            $state->execute();
            $result = $state->get_result();
            if (mysqli_num_rows($result) > 0) { //tiene excepciones
                while($row = $result->fetch_assoc()) {
                    $excepcionObjectResult = (array) [
                        'date_string' => '',
                        'no_puede' => false,
                        'horarios' => []
                    ];

                    $excepcionObjectResult['date_string'] = $row['fecha'];

                    if ($row['no_puede'] == 'true') { //no puede
                        $excepcionObjectResult['no_puede'] = true;
                        array_push($excepcionResult, $excepcionObjectResult);
                    } else { //puede, asi que tengo que buscar los tramos
                        $state = $this->conn->prepare('SELECT * FROM excepcionhorarios WHERE excepcionhorarios.idExcepcion = ?');
                        $state->bind_param('i', $row['idExcepcion']);
                        $state->execute();
                        $result = $state->get_result();

                        while($row = $result->fetch_assoc()) { //recorro cada uno de los tramos
                            $horariosExcepcionesObject = (array) [
                                'horariosTotales' => [],
                                'dir_alt' => false
                            ];
                            array_push($horariosExcepcionesObject['horariosTotales'], $row['horarios']);
                            $horariosExcepcionesObject['dir_alt'] = filter_var($row['dir_alt'], FILTER_VALIDATE_BOOLEAN);
                            array_push($excepcionObjectResult['horarios'], $horariosExcepcionesObject);
                        }

                        array_push($excepcionResult, $excepcionObjectResult);
                    }
                }
            }

            $objectReturned = (array) [
                'searchResult' => $searchResult,
                'excepciones' => $excepcionResult
            ];

            return $objectReturned;
        }

        function obtenerFechasEnUsoParaCronograma($idAlumno) {
            $status = "CONFIRMADO";
            $sumada = "false";
            $state = $this->conn->prepare('SELECT cronograma.idCronograma FROM cronograma WHERE cronograma.idAlumno = ? AND cronograma.status = ?');
            $state->bind_param('is', $idAlumno, $status);
            $state->execute();
            $result = $state->get_result();
            $idCronograma = null;
            while($row = $result->fetch_assoc()) {
                $idCronograma = $row['idCronograma'];
            }

            $state = $this->conn->prepare('SELECT clase.fecha FROM clase WHERE clase.alumno = ? AND clase.status = ? AND clase.sumada = ? AND clase.idCronograma = ? ORDER BY clase.fecha');
            $state->bind_param('issi', $idAlumno, $status, $sumada, $idCronograma);
            $state->execute();
            $result = $state->get_result();

            $fechasEnUso = [];
            while($row = $result->fetch_assoc()) {
                array_push($fechasEnUso, $row['fecha']);
            }

            return $fechasEnUso;
        }

        function cancelarClase($idClase, $motivoCancelacion) {
            $cancelado = "true";
            $now = date('Y-m-d h:i:s a', time());
            $state = $this->conn->prepare('UPDATE clase SET clase.claseCancelada = ?, clase.fechaClaseCancelada = ?, motivoCancelacion = ? WHERE clase.idClase = ?');
            $state->bind_param('sssi', $cancelado, $now, $motivoCancelacion, $idClase);
            if($state->execute()) {
                return 0;
            }
            return 1;
        }

        function reactivarClase($idClase) {
            $cancelado = "false";
            $vaciarCampos = "";
            $now = date('Y-m-d h:i:s a', time());
            $state = $this->conn->prepare('UPDATE clase SET clase.claseCancelada = ?, clase.fechaClaseCancelada = ?, motivoCancelacion = ?, fechaClaseReactivada = ? WHERE clase.idClase = ?');
            $state->bind_param('ssssi', $cancelado, $vaciarCampos, $vaciarCampos, $now, $idClase);
            if($state->execute()) {
                return 0;
            }
            return 1;
        }

        //deprecated
        function sortAutosPorID($a, $b) {
            return strcmp($a->idAuto, $b->idAuto);
        }

        function date_compare($a, $b) {
            $t1 = strtotime($a['fecha']);
            $t2 = strtotime($b['fecha']);
            return $t1 - $t2;
        }

        function excepciones_compare($a, $b) {
            $t1 = $a->idExcepcion;
            $t2 = $b->idExcepcion;
            return $t1 > $t2;
        }

        function excepciones_tramos_compare($a, $b) {
            $splitA = $a->tramoHorario;
            $splitB = $b->tramoHorario;
            $t1 = strtotime($splitA[0]);
            $t2 = strtotime($splitB[sizeof($splitB) - 1]);
            return $t1 > $t2;
        }

        function sortHorariosPorHora($a, $b) {
            return $b->ratingGeneral > $a->ratingGeneral ? 1 : -1;
        }

        function verificarSiElHorarioTieneDireccionAlternativa($options, $horarioAuto, $estoySobreUnaExcepcion) {
            if ($estoySobreUnaExcepcion == false) {
                foreach($options as $option) {
                    if (in_array($horarioAuto, $option['scheduleSend'])) {
                        return $option['dir_alt'];
                    }
                }
            } else {
                foreach($options as $option) {
                    if (in_array($horarioAuto, $option['horariosTotales'])) {
                        return $option['dir_alt'];
                    }
                }
            }

        }
        
        function obtenerIdAutoMaster($zonaAlumno) {
            $state = $this->conn->prepare('SELECT auto.idAuto FROM auto WHERE auto.zonaMaster IN (SELECT zona.zonaMaster FROM zona WHERE zona.idZona = ?)');
            $state->bind_param('s', $zonaAlumno);
            $state->execute();
            $result = $state->get_result();

            $idAutoMaster;
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $idAutoMaster = $row['idAuto'];
                }
                return $idAutoMaster;
            } else {
                return null;
            }
        }

        function obtenerHorariosLibresAutoYAlumno($clases, $disponibilidad, $nombreDiaBusqueda, $fechaBusqueda, $idAuto) {
            $horariosOcupados = [];
            $claseData = [
                'idClase' => '',
                'horaInicio' => '',
                'idZona' => ''
            ];

            if (date('Y-m-d') == $fechaBusqueda) {
                foreach ($clases as $clase) { //recorro cada clase que tenga este auto
                    if(in_array($clase['horaInicio'], $disponibilidad[$nombreDiaBusqueda])) { //el auto esta ocupado en uno de los horarios disponibles del alumno
                        $claseData['idClase'] = $clase['idClase'];
                        $claseData['horaInicio'] = $clase['horaInicio'];
                        $claseData['idZona'] = $clase['idZona'];
                        array_push($horariosOcupados, $claseData);
                    }
                }

                $resultado = array_values(array_diff($disponibilidad[$nombreDiaBusqueda], array_column($horariosOcupados, 'horaInicio'))); //obtengo los horarios libres que tanto el usuario como el auto estan libres
                foreach ($resultado as $key => $horario) {
                    if (strtotime($horario) < time()) {
                        unset($resultado[$key]);
                    }
                }
                return array_values($resultado);

            } else {
                foreach ($clases as $clase) { //recorro cada clase que tenga este auto
                    if(in_array($clase['horaInicio'], $disponibilidad[$nombreDiaBusqueda])) { //el auto esta ocupado en uno de los horarios disponibles del alumno
                        $claseData['idClase'] = $clase['idClase'];
                        $claseData['horaInicio'] = $clase['horaInicio'];
                        $claseData['idZona'] = $clase['idZona'];
                        array_push($horariosOcupados, $claseData);
                    }
                }

                return array_values(array_diff($disponibilidad[$nombreDiaBusqueda], array_column($horariosOcupados, 'horaInicio'))); //obtengo los horarios libres que tanto el usuario como el auto estan libres
            }
        }

        function obtenerHorariosLibresAutoYAlumnoExcepciones($clases, $disponibilidad, $nombreDiaBusqueda, $fechaBusqueda) {
            $horariosOcupados = [];
            $claseData = [
                'idClase' => '',
                'horaInicio' => '',
                'idZona' => ''
            ];
            if (date('Y-m-d') == $fechaBusqueda) {
                foreach ($clases as $clase) { //recorro cada clase que tenga este auto
                    if(in_array($clase['horaInicio'], $disponibilidad)) { //el auto esta ocupado en uno de los horarios disponibles del alumno
                        $claseData['idClase'] = $clase['idClase'];
                        $claseData['horaInicio'] = $clase['horaInicio'];
                        $claseData['idZona'] = $clase['idZona'];
                        array_push($horariosOcupados, $claseData);
                    }
                }

                $resultado = array_values(array_diff($disponibilidad, array_column($horariosOcupados, 'horaInicio'))); //obtengo los horarios libres que tanto el usuario como el auto estan libres
                foreach ($resultado as $key => $horario) {
                    if (strtotime($horario) < time()) {
                        unset($resultado[$key]);
                    }
                }
                return array_values($resultado);

            } else {
                foreach ($clases as $clase) { //recorro cada clase que tenga este auto
                    if(in_array($clase['horaInicio'], $disponibilidad)) { //el auto esta ocupado en uno de los horarios disponibles del alumno
                        $claseData['idClase'] = $clase['idClase'];
                        $claseData['horaInicio'] = $clase['horaInicio'];
                        $claseData['idZona'] = $clase['idZona'];
                        array_push($horariosOcupados, $claseData);
                    }
                }
    
                return array_values(array_diff($disponibilidad, array_column($horariosOcupados, 'horaInicio'))); //obtengo los horarios libres que tanto el usuario como el auto estan libres
            }
        }

        function obtenerCronogramaDelDia($fecha) {
            $fechaString = $fecha->format('Y-m-d');
            //$state = $this->$conn->prepare('SELECT * FROM clase WHERE clase.fecha = ? ORDER BY clase.horaInicio');
            //$state = $this->$conn->prepare('SELECT * FROM auto LEFT JOIN clase ON auto.idAuto = clase.auto WHERE clase.fecha = ? OR clase.fecha IS NULL ORDER BY clase.horaInicio');
            $state = $this->conn->prepare('SELECT * FROM auto LEFT JOIN clase ON auto.idAuto = clase.auto AND clase.fecha = ? AND clase.status = ? ORDER BY clase.horaInicio');
            $status = 'CONFIRMADO';
            $state->bind_param('ss', $fechaString, $status);
            $state->execute();
            $result = $state->get_result();

            $cronograma = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $cronograma[$row['idAuto']][] = $row;
                }
            } else {
                return [];
            }
            return $cronograma;
        }

        function eliminarAutosInactivos($cronogramaDelDiaPorAuto, $fechaBusqueda) {
            foreach ($cronogramaDelDiaPorAuto as $idAuto => $cronogramaAuto) {
                $state = $this->conn->prepare('SELECT * FROM autoinactivo WHERE autoinactivo.idAuto = ?');
                $state->bind_param('s', $idAuto);
                $state->execute();
                $result = $state->get_result();

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $fechaInicioInactividad = date('Y-m-d', strtotime($row['fechaInicioinactividad']));
                        $fechaFinInactividad = date('Y-m-d', strtotime($row['fechaFininactividad']));
                        $fechaBusquedaDate = $fechaBusqueda->format('Y-m-d');
                        if (($fechaBusquedaDate >= $fechaInicioInactividad) && ($fechaBusquedaDate <= $fechaFinInactividad)) {
                            unset($cronogramaDelDiaPorAuto[$idAuto]);
                        }
                    }
                }
            }
            
            return $cronogramaDelDiaPorAuto;
        }

        function obtenerDisponibilidadesAutos() {
            $state = $this->conn->prepare('SELECT auto.idAuto, auto.disponibilidad FROM auto');
            $state->execute();
            $result = $state->get_result();

            $disponibilidades = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $disponibilidades[$row['idAuto']] = $row['disponibilidad'];
                }
                return $disponibilidades;
            } else {
                return [];
            }
        }

        function obtenerZonas() {
            $state = $this->conn->prepare('SELECT zona.idZona, zona.nombreZona, zonasvecinas.idZonaVecina FROM zona INNER JOIN zonasvecinas ON zona.idZona = zonasvecinas.idZona');
            $state->execute();
            $result = $state->get_result();

            $zonas = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $zonas[$row['idZona']][] = $row['idZonaVecina'];
                }
            }         
            return $zonas;
        }

        function crearGrafoZonas($zonas) {
            $grafo = [];
            foreach ($zonas as $zona => $zonasAdyacentes) {
                $vertice = (object) [
                    'idZona' => null,
                    'adyacentes' => []
                ];
                $vertice->idZona = $zona;
                $vertice->adyacentes = $zonasAdyacentes;
                array_push($grafo, $vertice);
            }

            foreach ($grafo as $vertice) {
                $adyacentes = $vertice->adyacentes;
                $adyacentesObjetos = [];
                foreach ($adyacentes as $adyacente) {
                    $verticeAdyacente = (object) [
                        'posicion' => null,
                        'costo' => null
                    ];
                    $index = array_search($adyacente, array_column($grafo, 'idZona'));
                    $verticeAdyacente->posicion = $index;
                    array_push($adyacentesObjetos, $verticeAdyacente);
                }
                $vertice->adyacentes = $adyacentesObjetos;
            }

            return $grafo;
        }

        //i es la posicion de la zona inicio en el grafo
        //idZonaDestino es el id de la zona destino
        function obtenerRatingZona($i, $idZonaDestino, $grafo) {
            $marca = [];
            $encontre = false;
            $cantidadZonas = 0;
            $ady = [];
            $cola = [];
            array_push($cola, $grafo[$i]); //incluye el vertice (dato y adyacente)
            array_push($cola, null);
            $marca[$i] = true;
            while (!empty($cola)) {
                if (!$encontre) {
                    $vertice = array_shift($cola); //desencolo el primer elemento y lo elimino
                    if($vertice != null) {
                        if($vertice->idZona == $idZonaDestino) { //el vertice es el destino
                            $encontre = true;
                        } else {
                            $ady = $vertice->adyacentes; //me guardo sus adyacentes
                            foreach ($ady as $elementAdy) {
                                $posicion = $elementAdy->posicion; //si la posicion no esta marcada
                                if(!in_array($posicion, array_keys($marca))) {
                                    $w = $grafo[$posicion];
                                    $marca[$posicion] = true;
                                    array_push($cola, $w);
                                }
                            }
                        }                
                    } else {
                        $cantidadZonas++;
                        if(count($cola) > 0) { //si la cola no esta vacia
                            array_push($cola, null); //pusheo un null nuevo para separar de cola
                        }
                    }
                } else {
                    break;
                }
            }
            return 10 - $cantidadZonas;
        }

        function obtenerInformacionZonas() {
            $state = $this->conn->prepare('SELECT * FROM zona');
            $state->execute();
            $result = $state->get_result();

            $zonas = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $points = (object) [
                        'puntoSuperiorIzquierdo' => $row['puntoSuperiorIzquierdo'],
                        'puntoSuperiorDerecho' => $row['puntoSuperiorDerecho'],
                        'puntoInferiorIzquierdo' => $row['puntoInferiorIzquierdo'],
                        'puntoInferiorDerecho' => $row['puntoInferiorDerecho']
                    ];
                    $zonas[$row['idZona']] = $points;
                }
            }
            return $zonas;
        }

        function obtenerZonaAlumno($direccion) {
            $dirBusqueda = $this->obtenerDireccionParaBusqueda($direccion);

            $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$dirBusqueda.'&key=AIzaSyCvMYjfZVe25lflg8fb6PlfKc3zipGmGCM';
            //API PARA CUANDO SON INTERSECCIONES Y NO ES CON NUMERO. SOLO ES PRECISO CON LA PLATA
            //https://geocoder.ls.hereapi.com/6.2/geocode.json?city=La%20Plata&street=45%20%40%203&apiKey=keJFZOq_jmWtTdun9_bUg_JfQKPPj8pWFsw0nIDtjEY
            
            //API KEY HERE
            $context = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
            //$response = file_get_contents('https://geocoder.ls.hereapi.com/6.2/geocode.json?apiKey=keJFZOq_jmWtTdun9_bUg_JfQKPPj8pWFsw0nIDtjEY&searchtext='.$dirBusqueda,false,$context);
            $response = file_get_contents($url,false,$context);
            
            $response = json_decode($response);
            
            //$latitude = $response->results[2]->View[0]->Result[0]->Location->NavigationPosition[0]->Latitude;
            $latitude = $response->results[0]->geometry->location->lat;
            $longitude = $response->results[0]->geometry->location->lng;
           

            $zonas = $this->obtenerInformacionZonas(); //consulto la BD para traerme por cada zona sus puntos.
            $zona = null;
            foreach ($zonas as $idZona => $informacionZona) {
                $latLong = $this->obtenerLatitudesYLongitudes($informacionZona);

                $vertices_x = $latLong[1]; //Longitud    // x-coordinates of the vertices of the polygon
                $vertices_y = $latLong[0]; //Latitud // y-coordinates of the vertices of the polygon
                $points_polygon = count($vertices_x);  // number vertices - zero-based array
                $longitude_x = $longitude;  // x-coordinate of the point to test
                $latitude_y = $latitude;    // y-coordinate of the point to test
                
                if ($this->is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)){
                    $zona = $idZona;
                    break;
                }
            }

            return $zona;
        }

        function obtenerNombreAlumno($idAlumno) {
            $state = $this->conn->prepare('SELECT * FROM alumno WHERE alumno.idAlumno = ?');
            $state->bind_param('i', $idAlumno);
            $state->execute();
            $result = $state->get_result();

            $nombreAlumno = '';
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $nombreAlumno .= $row['nombre'];
                }
                return $nombreAlumno;
            } else {
                return null;
            }
        }

        function obtenerDireccionClase($idDireccion) {
            $state = $this->conn->prepare('SELECT * FROM direccion WHERE direccion.idDireccion = ?');
            $state->bind_param('i', $idDireccion);
            $state->execute();
            $result = $state->get_result();

            $direccionStringFormateada;
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $direccionStringFormateada = $this->obtenerDireccionParaMostrar($row['calle'], $row['calle_diag'], $row['calle_a'], $row['calle_a_diag'], $row['calle_b'], $row['calle_b_diag'], $row['numero'], $row['ciudad'], $row['floor_'], $row['departamento']);
                }
                return $direccionStringFormateada;
            } else {
                return null;
            }
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

        function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y) {
            $i = $j = $c = 0;
            for ($i = 0, $j = $points_polygon-1 ; $i < $points_polygon; $j = $i++) {
                if ( (($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
                ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) ) 
                    $c = !$c;
            }
            return $c;
        }

        function obtenerLatitudesYLongitudes($informacionZona) {
            $latitudLongitudPrimerPunto = explode(',', $informacionZona->puntoSuperiorIzquierdo);
            $latitudLongitudSegundoPunto = explode(',', $informacionZona->puntoSuperiorDerecho);
            $latitudLongitudTercerPunto = explode(',', $informacionZona->puntoInferiorIzquierdo);
            $latitudLongitudCuartoPunto = explode(',', $informacionZona->puntoInferiorDerecho);

            $latitudes = [$latitudLongitudPrimerPunto[0], $latitudLongitudSegundoPunto[0], $latitudLongitudTercerPunto[0], $latitudLongitudCuartoPunto[0]];
            $longitudes = [$latitudLongitudPrimerPunto[1], $latitudLongitudSegundoPunto[1], $latitudLongitudTercerPunto[1], $latitudLongitudCuartoPunto[1]];
            
            $latLong = [$latitudes, $longitudes];

            return $latLong;
        }

        function obtenerDireccionParaBusqueda($direccion) {
            $avenidasLaPlata = (array) ['66', '31', '60', '52', '44', '38', '32', '31', '25', '19', '13', '7', '1', '122'];
            $stringDireccion = "";

            $ciudad = $direccion[4]['city'];
            if($direccion[4]['city'] == "La Plata") {
                $ciudad = "La+Plata";
            }

            $finalPart = '+'.$ciudad.'+Buenos+Aires+Argentina';

            if ($direccion[0]['diag']) {
                $stringDireccion .= "Diagonal+".$direccion[0]['street']."+";
            }

            if (!$direccion[0]['diag']) {
                $minusculas = strtolower($direccion[0]['diag']);
                if(strpos($minusculas, 'boulevard')) {
                    $stringDireccion .= "Boulevard+".$direccion[0]['street'].'+';
                } else {
                    if (in_array($direccion[0]['street'], $avenidasLaPlata)) {
                        $stringDireccion .= "Avenida+".$direccion[0]['street']."+";
                    } else{
                        $stringDireccion .= "Calle+".$direccion[0]['street']."+";
                    }
                }
            }

            if ($direccion[3]['altitud'] != '') {
                $stringDireccion .= $direccion[3]['altitud'].$finalPart;
            } else {
                if ($direccion[1]['street_a'] != '') {
                    if ($direccion[1]['diag']) {
                        $stringDireccion .= "Diagonal+".$direccion[1]['street_a'].$finalPart;
                    } else {
                        $minusculas = strtolower($direccion[1]['street_a']);
                        if(strpos($minusculas, 'boulevard')) {
                            $stringDireccion .= "Boulevard+".$direccion[1]['street_a'].$finalPart;
                        } else {
                            if (in_array($direccion[1]['street_a'], $avenidasLaPlata)) {
                                $stringDireccion .= "Avenida+".$direccion[1]['street_a'].$finalPart;
                            } else {
                                $stringDireccion .= "Calle+".$direccion[1]['street_a'].$finalPart;
                            }
                        }
                    }
                } else {
                    if ($direccion[2]['diag']) {
                        $stringDireccion .= "Diagonal+".$direccion[2]['street_b'].$finalPart;
                    } else {
                        $minusculas = strtolower($direccion[2]['street_b']);
                        if(strpos($minusculas, 'boulevard')) {
                            $stringDireccion .= "Boulevard+".$direccion[2]['street_b'].$finalPart;
                        } else {
                            if (in_array($direccion[2]['street_b'], $avenidasLaPlata)) {
                                $stringDireccion .= "Avenida+".$direccion[2]['street_b'].$finalPart;
                            } else {
                                $stringDireccion .= "Calle+".$direccion[2]['street_b'].$finalPart;
                            }
                        }
                    }
                }
            }

            return $stringDireccion;
        }

        function zonasDeClasesVecinas($clases, $horario) {
            $zonasVecinas = [];
            foreach ($clases as $clase) {
                if($this->obtenerDiferenciaHoraria($clase['horaInicio'], $horario) == 1 || $this->obtenerDiferenciaHoraria($clase['horaInicio'], $horario) == 1.15 || $this->obtenerDiferenciaHoraria($clase['horaInicio'], $horario) == 1.30) {
                    array_push($zonasVecinas, $clase['idZona']);
                }
            }

            return $zonasVecinas;
        }

        function esTurnoDisponible($disponibilidad, $horarioAuto, $nombreDiaBusqueda) {
            if ($disponibilidad === "A") {
                return true;
            }

            if ($disponibilidad === "M") {
                $horarioBusqueda = strtotime($horarioAuto);
                $horarioInicial = strtotime("08:00");
                $horarioFinal = strtotime("11:15");
                if (($horarioBusqueda >= $horarioInicial) && ($horarioBusqueda <= $horarioFinal)) {
                    return true;
                }
                return false;
            }

            if ($disponibilidad === "T") {
                if ($nombreDiaBusqueda == 'Tuesday' || $nombreDiaBusqueda == 'Thursday' || $nombreDiaBusqueda == 'Sunday') {
                    return true;
                } else {
                    $horarioBusqueda = strtotime($horarioAuto);
                    $horarioInicial = strtotime("12:15");
                    $horarioFinal = strtotime("19:45");
                    if (($horarioBusqueda >= $horarioInicial) && ($horarioBusqueda <= $horarioFinal)) {
                        return true;
                    }
                }
                return false; 
            }
        }
        
        function obtenerNombreDia($fecha) {
            $day = date('l', strtotime($fecha));
            switch ($day) {
                case 'Monday': return 'Lunes'; break;
                case 'Tuesday': return 'Martes'; break;
                case 'Wednesday': return 'Miércoles'; break;
                case 'Thursday': return 'Jueves'; break;
                case 'Friday': return 'Viernes'; break;
                case 'Saturday': return 'Sábado'; break;
                case 'Sunday': return 'Domingo'; break;
            }
        }

        function obtenerDiferenciaHoraria($horarioLibre, $primerHorarioOcupado) {
            return abs(round((strtotime($horarioLibre) - strtotime($primerHorarioOcupado))/3600, 1));
        }

        function obtenerMaximosDiasTolerancia() {
            $idParametro = 1;
            $state = $this->conn->prepare('SELECT * FROM parametros WHERE idParametro = ?');
            $state->bind_param('s', $idParametro);
            $state->execute();
            $result = $state->get_result();

            $maximoDiasTolerancia;
            $minimoDiasTolerancia;
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $maximoDiasTolerancia = $row['maximoDiasTolerancia'];
                    $minimoDiasTolerancia = $row['diasToleranciaBajo'];
                }
            }
            $tolerancias = [];
            array_push($tolerancias, $maximoDiasTolerancia);
            array_push($tolerancias, $minimoDiasTolerancia);
            return $tolerancias;
        }

        //deprecated
        function obtenerPrimerHorarioOcupado($horariosOcupados, $positivo) {
            if ($positivo) {
                return $horariosOcupados[0];
            } else {
                return end($horariosOcupados);
            }
        }

        //deprecated
        function obtenerSegundoHorarioOcupado($horariosOcupados, $positivo) {
            if ($positivo) {
                return $horariosOcupados[1];
            } else {
                return $horariosOcupados[count($horariosOcupados) - 2];
            }
        }

        //deprecated
        function obtenerRating819y918($horariosOcupados, $horarioLibre) {
            $positivo = true;
            if ($horarioLibre == '18:00' || $horarioLibre == '19:00') {
                $positivo = false;
            }

            if (count($horariosOcupados) === 0) { //no hay horarios ocupados, por ende todos estan libres en el dia.
                return 2;
            }

            $primerHorarioOcupado = obtenerPrimerHorarioOcupado($horariosOcupados, $positivo);
            $diferenciaHoraria = obtenerDiferenciaHoraria($horarioLibre, $primerHorarioOcupado);

            if ($diferenciaHoraria >= 4) { //si la diferencia es de 4 entonces son las 12/15hs
                return 2;
            } else { 
                if ($diferenciaHoraria == 3) { //el primer horario ocupado es a las 11/16hs
                    return 4;
                } else {
                    if ($diferenciaHoraria == 2) { //el primer horario ocupado es a las 10/17hs
                        if (count($horariosOcupados) >= 2) { //evaluo si el proximo horario ocupado es a las 11/16 para hacer un calculo mas preciso
                            $segundoHorarioOcupado = obtenerSegundoHorarioOcupado($horariosOcupados, $positivo);
                            if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 3) { //el proximo horario ocupado es a las 11/16hs //por ende a las 9 esta libre, pero a las 10 y 11 esta ocupado
                                return 7;
                            } else { //el proximo horario ocupado no es a las 11.
                                return 6;
                            }
                        } else { //solo a las 10 esta ocupado, no posee mas ocupados.
                            return 6;
                        }
                    } else { //el primer horario ocupado es a las 9/18hs
                        if (count($horariosOcupados) >= 2) {
                            $segundoHorarioOcupado = obtenerSegundoHorarioOcupado($horariosOcupados, $positivo);
                            if ($horarioLibre === '08:00' || $horarioLibre === '19:00') {
                                if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 2) { //el proximo horario ocupado es a las 10/17hs
                                    return 10;
                                } else { //a las 9/18 esta ocupado, pero el proximo no es a las 10/17hs
                                    return 8;
                                }
                            } else {
                                if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 1 && obtenerDiferenciaHoraria($primerHorarioOcupado, $segundoHorarioOcupado) == 2) { //el proximo horario ocupado es a las 11/16hs
                                    return 10;
                                } else {
                                    if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 2 && obtenerDiferenciaHoraria($primerHorarioOcupado, $segundoHorarioOcupado) == 1) {
                                        return 10;
                                    } else {
                                        return 8;   
                                    }
                                }
                            }
                        } else { //el unico horario ocupado es a las 9
                            return 8;
                        }
                    }
                }
            }
        }

        //deprecated
        function obtenerRating9y18($horariosOcupados, $horarioLibre) {
            $positivo = true;
            if ($horarioLibre == '18:00') {
                $positivo = false;
            }

            if (count($horariosOcupados) === 0) { //no hay horarios ocupados, por ende todos estan libres en el dia.
                return 2;
            }

            $primerHorarioOcupado = obtenerPrimerHorarioOcupado($horariosOcupados, $positivo);
            $diferenciaHoraria = obtenerDiferenciaHoraria($horarioLibre, $primerHorarioOcupado);

            if ($diferenciaHoraria >= 4) { //si la diferencia es de 4 entonces son las 12/15hs
                return 2;
            } else {
                if ($diferenciaHoraria == 3) { //el primer horario ocupado es a las 11/16hs
                    return 4;
                } else {
                    if ($diferenciaHoraria == 2) { //el primer horario ocupado es a las 10/17hs
                        if (count($horariosOcupados) >= 2) { //evaluo si el proximo horario ocupado es a las 11/16 para hacer un calculo mas preciso
                            $segundoHorarioOcupado = obtenerSegundoHorarioOcupado($horariosOcupados, $positivo);
                            if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 3) { //el proximo horario ocupado es a las 11/16hs //por ende a las 9 esta libre, pero a las 10 y 11 esta ocupado
                                return 7;
                            } else { //el proximo horario ocupado no es a las 11.
                                return 6;
                            }
                        } else { //solo a las 10 esta ocupado, no posee mas ocupados.
                            return 6;
                        }
                    } else {
                        if (count($horariosOcupados) >= 2) {
                            $segundoHorarioOcupado = obtenerSegundoHorarioOcupado($horariosOcupados, $positivo);
                            if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 1 && obtenerDiferenciaHoraria($primerHorarioOcupado, $segundoHorarioOcupado) == 2) { //el proximo horario ocupado es a las 11/16hs
                                return 10;
                            } else {
                                if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 2 && obtenerDiferenciaHoraria($primerHorarioOcupado, $segundoHorarioOcupado) == 1) {
                                    return 10;
                                } else {
                                    return 8;   
                                }
                            }
                        } else { 
                            return 8;
                        }
                    }
                }
            }
        }

        //deprecated
        function obtenerRating10($horariosOcupados, $horarioLibre) {
            $positivo = true;
            if (count($horariosOcupados) === 0) { //no hay horarios ocupados, por ende todos estan libres en el dia.
                return 2;
            }

            $primerHorarioOcupado = obtenerPrimerHorarioOcupado($horariosOcupados, $positivo);
            $diferenciaHoraria = obtenerDiferenciaHoraria($horarioLibre, $primerHorarioOcupado);

            if ($diferenciaHoraria >= 4) { //si la diferencia es de 4 entonces son las 12/15hs
                return 2;
            } else {
                if ($diferenciaHoraria == 3) { //el primer horario ocupado es a las 11/16hs
                    return 4;
                } else {                
                    if ($diferenciaHoraria == 2) { //el primer horario ocupado es a las 8/12hs
                        if(strtotime($horarioLibre)<=strtotime($primerHorarioOcupado)) { //entonces el primer horario ocupado es a las 12
                            if (count($horariosOcupados) >= 2) {
                                $segundoHorarioOcupado = obtenerSegundoHorarioOcupado($horariosOcupados, $positivo);                          
                                if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 3) {
                                    return 7;
                                } else { //el proximo horario ocupado no es a las 13.
                                    return 6;
                                }
                            } else { //el unico horario ocupado es a las 12.
                                return 6;
                            }
                        } else { //el primer horario ocupado es a las 8
                            if (count($horariosOcupados) >= 2) { //no hay mas horarios ocupados, solo el de las 8
                                $segundoHorarioOcupado = obtenerSegundoHorarioOcupado($horariosOcupados, $positivo);
                                if (obtenerDiferenciaHoraria($primerHorarioOcupado, $segundoHorarioOcupado) == 1) { // a las 8 esta ocupado y a las 9 tambien
                                    return 10;
                                } else { //a las 8 esta ocupado pero ni a las 9 ni a las 10 esta ocupado
                                    return 8;
                                }
                            } else {
                                return 8;
                            }
                        }
                    } else { //el primer horario ocupado es a las 9/11hs
                        if (count($horariosOcupados) >= 2) {
                            $segundoHorarioOcupado = obtenerSegundoHorarioOcupado($horariosOcupados, $positivo);
                            if(strtotime($horarioLibre)<=strtotime($primerHorarioOcupado)) { //entonces el primer horario ocupado es a las 11
                                if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 2) { //el segundo horario libre es a las 12hs (a las 8 nunca podria llegar a este punto)
                                    return 10;
                                } else {
                                    return 8;
                                }
                            } else { //es a las 9 el primer horario ocupado
                                if (obtenerDiferenciaHoraria($horarioLibre, $segundoHorarioOcupado) == 1) { //es a las 11 el segundo ocupado
                                    return 10;
                                } else {
                                    return 8;
                                }
                            }
                        } else { //no hay mas horarios ocupados, solo a las 9/11hs
                            return 8;
                        }
                    }
                }
            }
        }

        //deprecated
        /*function obtenerRatingHorario($horario, $horariosOcupados) { 
            return obtenerRatingHorariosTotales($horariosOcupados, $horario);
            /*  if ($horario === '08:00' || $horario === '19:00' || $horario === '09:00' || $horario === '18:00') { //es el primer horario o el ultimo horario
                return obtenerRating819y918($horariosOcupados, $horario);
            }
            
            if ($horario === '10:00') { //los horarios del medio
                return obtenerRating10($horariosOcupados, $horario);
            }        
        }*/

        function obtenerDiferenciaDias($fechaInicio, $fechaBusqueda) {
            $earlier = date_create($fechaInicio);
            $diff = date_diff($earlier, $fechaBusqueda);
            return $diff->days;
        }

        function obtenerRatingHorario($horariosOcupados, $horarioLibre, $tolerancias, $fechaInicio, $fechaBusqueda) {
            if (count($horariosOcupados) === 1 && $horariosOcupados[0] === null) { //no hay horarios ocupados, por ende todos estan libres en el dia.
                if ( (int) $this->obtenerDiferenciaDias($fechaInicio, $fechaBusqueda) > (int) $tolerancias[0]) {
                    return 10;
                } else {
                    if ( (int) $this->obtenerDiferenciaDias($fechaInicio, $fechaBusqueda) <= (int) $tolerancias[1]) {
                        return 3;
                    } else {
                        return 6;
                    }
                }                
            }

            $horariosOcupadosSorted = [];        
            foreach ($horariosOcupados as $horarioOcupado) {
                $horariosOcupadosSorted[$horarioOcupado] = abs($this->obtenerDiferenciaHoraria($horarioOcupado, $horarioLibre));
            }

            asort($horariosOcupadosSorted);
            $horariosOcupadosSortedValues = array_values($horariosOcupadosSorted);
            $horariosOcupadosSortedKeys = array_keys($horariosOcupadosSorted);

            if($horariosOcupadosSortedValues[0] >= 4) {
                return 2;
            } else {
                if ($horariosOcupadosSortedValues[0] == 3 || $horariosOcupadosSortedValues[0] == 3.15 || $horariosOcupadosSortedValues[0] == 3.30 || $horariosOcupadosSortedValues[0] == 3.45) {
                    return 4;
                } else { 
                    if ($horariosOcupadosSortedValues[0] == 2 || $horariosOcupadosSortedValues[0] == 2.15 || $horariosOcupadosSortedValues[0] == 2.30 || $horariosOcupadosSortedValues[0] == 2.45) {
                        if (count($horariosOcupadosSortedValues) >= 2) {
                            if ($horariosOcupadosSortedValues[1] == 2 || $horariosOcupadosSortedValues[1] == 2.15 || $horariosOcupadosSortedValues[1] == 2.30 || $horariosOcupadosSortedValues[1] == 2.45) { //si el segundo horario mas cercano tambien es a las 2, busco el proximo
                                if (count($horariosOcupadosSortedValues) >= 3) {
                                    if ($horariosOcupadosSortedValues[2] == 3 || $horariosOcupadosSortedValues[2] == 3.15 || $horariosOcupadosSortedValues[2] == 3.30 || $horariosOcupadosSortedValues[2] == 3.45) { //esta pegado al anterior horario
                                        return 7;
                                    } else { //es 4 o mayor, por lo que no esta pegado al anterior
                                        return 6;
                                    }
                                } else { //solo hay 2 horarios ocupados y son ambos a 2 horas de diferencia 
                                    return 6;
                                }
                            } else { //el segundo horario mas cercano no es a 2 horas
                                if ($horariosOcupadosSortedValues[1] == 3 || $horariosOcupadosSortedValues[1] == 3.15 || $horariosOcupadosSortedValues[1] == 3.30 || $horariosOcupadosSortedValues[1] == 3.45) {
                                    return 7;
                                } else {
                                    return 6;
                                }
                            }
                        } else { //solo tiene 1 horario ocupado y es a 2 horas de diferencia
                            return 6;
                        }
                    } else { //la diferencia es 1 con el horario mas cercano
                        if (count($horariosOcupadosSortedValues) >= 2) { //hay mas horarios ocupados
                            if ($horariosOcupadosSortedValues[1] == 2 || $horariosOcupadosSortedValues[1] == 2.15 || $horariosOcupadosSortedValues[1] == 2.30 || $horariosOcupadosSortedValues[1] == 2.45) { //si el segundo horario ocupado es a 2 horas, entonces ya es un 10
                                //evaluo si la diferencia entre [0] y [1] es de 1 hora, si es de 1 hora es un 10, sino un 8
                                $diff = $this->obtenerDiferenciaHoraria($horariosOcupadosSortedKeys[0], $horariosOcupadosSortedKeys[1]);
                                if($diff == 1 || $diff == 1.15 || $diff == 1.30 || $diff == 1.45) {
                                    return 10;
                                } else {
                                    return 8;
                                }
                            } else {
                                if ($horariosOcupadosSortedValues[1] == 1 || $horariosOcupadosSortedValues[1] == 1.15 || $horariosOcupadosSortedValues[1] == 1.30 || $horariosOcupadosSortedValues[1] == 1.45) { //el segundo horario ocupado tambien es a 1 hora
                                    return 10; //se forma un sanguchito
                                } else {
                                    return 8;
                                }
                            }
                        } else { //el unico horario ocupado es a 1 hora de diferencia
                            return 8;
                        }
                    }
                }
            }
        }
    }

?>