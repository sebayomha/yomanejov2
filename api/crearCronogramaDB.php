<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');
    //Va a ser utilizada cuando existan sesiones
    //require_once('token.php');

    class Cronograma {

        function __construct() { }

        //Funcion principal que se encargara de armar el cronograma
        function calcularCronograma($cantClases, $disponibilidad, $direccion, $fechaInicio, $excepciones){
            $horariosTentativos = array(); //arreglo que se va a retornar con el cronograma

            $fechaBusqueda = DateTime::createFromFormat("Y-m-d", $fechaInicio);
            $nombreDiaBusqueda = strftime("%A",$fechaBusqueda->getTimestamp());

            //**********//
            //se arma toda la informacion de las zonas
            //**********//
            $zonas = $this->obtenerZonas(); //obtengo las zonas y las cargo solo 1 vez
            $arrayGrafo = $this->crearGrafoZonas($zonas); //creo el grafo con todas las zonas y sus adyacentes
            $zonaAlumno = $this->obtenerZonaAlumno($direccion); //obtengo la zona del alumno a partir de su direccion
            
            if ($zonaAlumno === null) {
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
                if ($disponibilidad[$nombreDiaBusqueda] != null) { //entonces es un dia que el usuario esta disponible
                    $clasesDelDiaPorAuto = $this->obtenerCronogramaDelDia($fechaBusqueda); //armo un diccionario auto => clases
                    $clasesDelDiaPorAuto = $this->eliminarAutosInactivos($clasesDelDiaPorAuto, $fechaBusqueda); //quito los autos que esten inactivos esa fecha
                    $autos = []; //array que se usa para armar la estructura final que se retornara

                    foreach ($clasesDelDiaPorAuto as $idAuto => $clases) { //recorro cada clase
                        $cronogramasActuales = [];
                        
                        //horarios libres va a contener los dias que el auto no este ocupado y el usuario este disponible
                        $horariosLibres;
                        $result = $fechaBusqueda->format('Y-m-d');

                        if (in_array($result, $fechasExcepciones) && count($excepciones[$result]) > 0) {
                            $horariosLibres = $this->obtenerHorariosLibresAutoYAlumnoExcepciones($clases, $excepciones[$result]);
                        } else {
                            $horariosLibres = $this->obtenerHorariosLibresAutoYAlumno($clases, $disponibilidad, $nombreDiaBusqueda);
                        }

                        $horariosOcupados = array_column($clases, 'horaInicio'); //son los horarios que estan efectivamente ocupados por clases

                        $horariosLibresDataGeneral = [];
                        $horarioData = [
                            'horaInicio' => '',
                            'ratingHorario' => '',
                            'ratingZona' => '',
                            'ratingGeneral' => ''
                        ];

                        //**********//
                        //comienzo a armar el array que se va a retornar
                        //**********//
                        foreach ($horariosLibres as $horarioAuto) { //en base a los horarios libres instancio los objetos que se van a terminar retornando
                            if($disponibilidadesAutos[$idAuto] === "A" || $this->esTurnoDisponible($disponibilidadesAutos[$idAuto], $horarioAuto, $nombreDiaBusqueda)) { //si el horario esta en el turno que el auto puede (A = todo el dia,T= solo por la tarde, M= solo por la maniana)
                                
                                $horarioData['horaInicio'] = $horarioAuto;
                                $horarioData['ratingHorario'] = $this->obtenerRatingHorario($horariosOcupados, $horarioAuto, $tolerancias, $fechaInicio, $fechaBusqueda);
                                $zonasVecinas = $this->zonasDeClasesVecinas($clases, $horarioAuto); //busco si el horario posee clases vecinas para ver si es necesario calcular la cercania o no.
                                                                                            //Si no posee clases vecinas no vale la pena calcular la distancia
                                if(!empty($zonasVecinas)) {
                                    $ratingZonaMasCerca = 0;
                                    foreach ($zonasVecinas as $zona) {
                                        $posicionGrafoZonaClase = array_search($zona, array_column($arrayGrafo, 'idZona'));
                                        $ratingZonaActual = $this->obtenerRatingZona($posicionGrafoZonaClase, $zonaAlumno, $arrayGrafo);
                                        if($ratingZonaActual > $ratingZonaMasCerca) {
                                            $ratingZonaMasCerca = $ratingZonaActual;
                                        }
                                    }
                                } else {
                                    $ratingZonaMasCerca = null;
                                }
        
                                $horarioData['ratingZona'] = $ratingZonaMasCerca;
        
                                if($ratingZonaMasCerca == null) {
                                    $horarioData['ratingGeneral'] = $horarioData['ratingHorario'];
                                } else {
                                    $horarioData['ratingGeneral'] = abs($horarioData['ratingHorario'] + $horarioData['ratingZona']) / 2;
                                }

                                if ($idAuto == $idAutoMaster ) { //si es el auto master entonces siempre lo agrego
                                    array_push($horariosLibresDataGeneral, $horarioData);
                                } else {
                                    if ($ratingZonaMasCerca != null && $ratingZonaMasCerca > 6) { //no es el auto master pero tiene clases cercanas
                                        array_push($horariosLibresDataGeneral, $horarioData);
                                    }
                                }
                            }
                        }

                        $fechaBusquedaString = $fechaBusqueda->format('Y-m-d');
                        $diccionarioFechaHorariosLibres = [];
                        if (array_key_exists($idAuto, $diccionarioFechaHorariosLibres)) { //si este auto ya fue agregado solo le pusheo los nuevos valores
                            array_push($diccionarioFechaHorariosLibres[$idAuto], $horariosLibresDataGeneral);
                        } else {
                            $diccionarioFechaHorariosLibres = $horariosLibresDataGeneral; //el auto aun no fue agregado
                        }

                        /************/
                        /* ARMO LA ESTRUCTURA QUE SE VA A RETORNAR */
                        /************/
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

                        $esDeLaZona = false;
                        if ($idAutoMaster == $idAuto) {
                            $esDeLaZona = true;
                        }
                        
                        $autoObject = (object) [
                            'horarios' => $diccionarioFechaHorariosLibres,
                            'cronogramaActual' => $cronogramasActuales,
                            'tieneEldiaLibre' => $tieneEldiaLibre,
                            'idAuto' => $idAuto,
                            'esDeLaZona' => $esDeLaZona
                        ];

                        if (!empty($autoObject->horarios)) { //descarto los autos que no posean horarios disponibles.
                            array_push($autos, $autoObject);                        
                        }

                        usort($autos, array($this, 'sortAutosPorID'));
                        
                        $fechaObject = (object) [
                            'fecha' => $fechaBusquedaString,
                            'dia' => $this->obtenerNombreDia($fechaBusquedaString),
                            'autos' => $autos                         
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

            //quitarAutosDeHorarios($horariosTentativos);
            return $horariosTentativos;     
        }

        function sortAutosPorID($a, $b) {
            return strcmp($a->idAuto, $b->idAuto);
        }
        
        function obtenerIdAutoMaster($zonaAlumno) {
            $db = new ConnectionDB();
            $conn = $db->getConnection();
            $state = $conn->prepare('SELECT auto.idAuto FROM auto WHERE auto.zonaMaster IN (SELECT zona.zonaMaster FROM zona WHERE zona.idZona = ?)');
            $state->bind_param('s', $zonaAlumno);
            $state->execute();
            $result = $state->get_result();

            $idAutoMaster;
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $idAutoMaster = $row['idAuto'];
                }
                mysqli_close($conn);
                return $idAutoMaster;
            } else {
                mysqli_close($conn);
                return null;
            }
        }

        function obtenerHorariosLibresAutoYAlumno($clases, $disponibilidad, $nombreDiaBusqueda) {
            $horariosOcupados = [];
            $claseData = [
                'idClase' => '',
                'horaInicio' => '',
                'idZona' => ''
            ];

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

        function obtenerHorariosLibresAutoYAlumnoExcepciones($clases, $disponibilidad) {
            $horariosOcupados = [];
            $claseData = [
                'idClase' => '',
                'horaInicio' => '',
                'idZona' => ''
            ];

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

        function obtenerCronogramaDelDia($fecha) {
            $fechaString = $fecha->format('Y-m-d');

            $db = new ConnectionDB();
            $conn = $db->getConnection();
            //$state = $conn->prepare('SELECT * FROM clase WHERE clase.fecha = ? ORDER BY clase.horaInicio');
            //$state = $conn->prepare('SELECT * FROM auto LEFT JOIN clase ON auto.idAuto = clase.auto WHERE clase.fecha = ? OR clase.fecha IS NULL ORDER BY clase.horaInicio');
            $state = $conn->prepare('SELECT * FROM auto LEFT JOIN clase ON auto.idAuto = clase.auto AND clase.fecha = ? ORDER BY clase.horaInicio');
            $state->bind_param('s', $fechaString);
            $state->execute();
            $result = $state->get_result();

            $cronograma = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $cronograma[$row['idAuto']][] = $row;
                }
            } else {
                mysqli_close($conn);
                return [];
            }

            mysqli_close($conn);
            return $cronograma;
        }

        function eliminarAutosInactivos($cronogramaDelDiaPorAuto, $fechaBusqueda) {
            $db = new ConnectionDB();
            $conn = $db->getConnection();

            foreach ($cronogramaDelDiaPorAuto as $idAuto => $cronogramaAuto) {
                $state = $conn->prepare('SELECT * FROM autoinactivo WHERE autoinactivo.idAuto = ?');
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
            mysqli_close($conn);
            return $cronogramaDelDiaPorAuto;
        }

        function obtenerDisponibilidadesAutos() {
            $db = new ConnectionDB();
            $conn = $db->getConnection();
            $state = $conn->prepare('SELECT auto.idAuto, auto.disponibilidad FROM auto');
            $state->execute();
            $result = $state->get_result();

            $disponibilidades = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $disponibilidades[$row['idAuto']] = $row['disponibilidad'];
                }
                mysqli_close($conn);
                return $disponibilidades;
            } else {
                mysqli_close($conn);
                return [];
            }
        }

        function obtenerZonas() {
            $db = new ConnectionDB();
            $conn = $db->getConnection();

            $state = $conn->prepare('SELECT zona.idZona, zona.nombreZona, zonasvecinas.idZonaVecina FROM zona INNER JOIN zonasvecinas ON zona.idZona = zonasvecinas.idZona');
            $state->execute();
            $result = $state->get_result();

            $zonas = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $zonas[$row['idZona']][] = $row['idZonaVecina'];
                }
            }
            mysqli_close($conn);
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
            $db = new ConnectionDB();
            $conn = $db->getConnection();

            $state = $conn->prepare('SELECT * FROM zona');
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
            mysqli_close($conn);
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
            $db = new ConnectionDB();
            $conn = $db->getConnection();
            $state = $conn->prepare('SELECT * FROM alumno WHERE alumno.idAlumno = ?');
            $state->bind_param('i', $idAlumno);
            $state->execute();
            $result = $state->get_result();

            $nombreAlumno = '';
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $nombreAlumno .= $row['nombre']. ' '.$row['apellido'];
                }
                mysqli_close($conn);
                return $nombreAlumno;
            } else {
                mysqli_close($conn);
                return null;
            }
        }

        function obtenerDireccionClase($idDireccion) {
            $db = new ConnectionDB();
            $conn = $db->getConnection();
            $state = $conn->prepare('SELECT * FROM direccion WHERE direccion.idDireccion = ?');
            $state->bind_param('i', $idDireccion);
            $state->execute();
            $result = $state->get_result();

            $direccionStringFormateada;
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $direccionStringFormateada = $this->obtenerDireccionParaMostrar($row['calle'], $row['calle_diag'], $row['calle_a'], $row['calle_a_diag'], $row['calle_b'], $row['calle_b_diag'], $row['numero'], $row['ciudad']);
                }
                mysqli_close($conn);
                return $direccionStringFormateada;
            } else {
                mysqli_close($conn);
                return null;
            }
        }

        function obtenerDireccionParaMostrar($calle, $calle_diag, $calle_a, $calle_a_diag, $calle_b, $calle_b_diag, $numero, $ciudad) {
            $stringDireccion = "";

            if($calle_diag) {
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

            $stringDireccion .= ', '.$ciudad;

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
            $db = new ConnectionDB();
            $conn = $db->getConnection();

            $state = $conn->prepare('SELECT * FROM parametros WHERE idParametro = ?');
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
            mysqli_close($conn);
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