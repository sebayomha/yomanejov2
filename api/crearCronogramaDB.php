<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');
    //Va a ser utilizada cuando existan sesiones
    //require_once('token.php');

    /*Funcionalidad que falta hacer
        1. Los autos que no poseen clases tenerlos en cuenta;
        2. Si el dia no posee clases llenar el cronograma con todos los autos
        3. Hay 1 auto que por las mañanas no puede, ese no hay que tenerlo en cuenta.
        4. Lo mismo para el punto 3) pero si es por la tarde    
        5. Obtener zona de la clase
        6. Cargar la BD
        7. Hacer el codigo principal de la llamada GET con las validaciones
        8. Hacer logica con los horarios REALES
    */

    //Funcion principal que se encargara de armar el cronograma
    function obtenerCronograma($cantClases, $disponibilidad, $direccion, $fechaInicio){
        $horariosTentativos = array(); //arreglo que se va a retornar con el cronograma

        $fechaBusqueda = DateTime::createFromFormat("Y-m-d", $fechaInicio);
        $nombreDiaBusqueda = strftime("%A",$fechaBusqueda->getTimestamp());

        //**********//
        //se arma toda la informacion de las zonas
        //**********//
        $zonas = obtenerZonas(); //obtengo las zonas y las cargo solo 1 vez
        $arrayGrafo = crearGrafoZonas($zonas); //creo el grafo con todas las zonas y sus adyacentes
        $zonaAlumno = obtenerZonaAlumno($direccion);

        $totalDiasTentativosRetornar; //Total de dias tentativos a retornar

        switch ($cantClases) {
            case 1:  $totalDiasTentativosRetornar = 7; break;
            case 2:  $totalDiasTentativosRetornar = 8; break;
            case 3:  $totalDiasTentativosRetornar = 10; break;
            case 4:  $totalDiasTentativosRetornar = 14; break;
            case 5:  $totalDiasTentativosRetornar = 16; break;
            case 6:  $totalDiasTentativosRetornar = 17; break;
            case 8:  $totalDiasTentativosRetornar = 21; break;
            case 10: $totalDiasTentativosRetornar = 25; break;
            case 12: $totalDiasTentativosRetornar = 30; break;
        };

        $i = 1;
        while ($i <= $totalDiasTentativosRetornar) { //Se recorrera hasta tanto obtener la cantidad de dias posibles a retornar
            if ($disponibilidad[$nombreDiaBusqueda] != null) { //entonces es un dia que el usuario esta disponible
                $clasesDelDiaPorAuto = obtenerCronogramaDelDia($fechaBusqueda); //armo un diccionario auto => clases

                //hasta el momento no estoy teniendo en cuenta los autos que ese dia no tienen asignada ninguna clase y estan absolutamente libres !!!!
                if(!empty($clasesDelDiaPorAuto)) { //este dia posee clases ya
                    $clasesDelDiaPorAuto = eliminarAutosInactivos($clasesDelDiaPorAuto, $fechaBusqueda); //quito los autos que esten inactivos esa fecha
                    foreach ($clasesDelDiaPorAuto as $idAuto => $clases) { //recorro cada clase
                    
                        //horarios libres va a contener los dias que el auto no este ocupado y el usuario este disponible
                        $horariosLibres = obtenerHorariosLibresAutoYAlumno($clases, $disponibilidad, $nombreDiaBusqueda);
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
                            $horarioData['horaInicio'] = $horarioAuto;
                            $horarioData['ratingHorario'] = obtenerRatingHorario($horariosOcupados, $horarioAuto);
                            $zonasVecinas = zonasDeClasesVecinas($clases, $horarioAuto); //busco si el horario posee clases vecinas para ver si es necesario calcular la cercania o no.
                                                                                        //Si no posee clases vecinas no vale la pena calcular la distancia
                            if(!empty($zonasVecinas)) {
                                $ratingZonaMasCerca = 0;
                                foreach ($zonasVecinas as $zona) {
                                    $posicionGrafoZonaClase = array_search($zona, array_column($arrayGrafo, 'idZona'));
                                    $ratingZonaActual = obtenerRatingZona($posicionGrafoZonaClase, $zonaAlumno, $arrayGrafo);
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
                            array_push($horariosLibresDataGeneral, $horarioData);
                        }
    
                        $fechaBusquedaString = $fechaBusqueda->format('Y-m-d');
                        $diccionarioFechaHorariosLibres = [];
                        if (array_key_exists($idAuto, $diccionarioFechaHorariosLibres)) { //si este auto ya fue agregado solo le pusheo los nuevos valores
                            array_push($diccionarioFechaHorariosLibres[$idAuto], $horariosLibresDataGeneral);
                        } else {
                            $diccionarioFechaHorariosLibres = $horariosLibresDataGeneral; //el auto aun no fue agregado
                        }
                        $horariosTentativos[$fechaBusquedaString][$idAuto] = $diccionarioFechaHorariosLibres; //agrego en el diccionario el nuevo auto con sus horarios disponibles que coinciden con los del alumno
                    }
                } else { //ese dia aun no posee clases
                    $diasDisponiblesUsuario = $disponibilidad[$nombreDiaBusqueda];
                    $horariosLibresDataGeneral = [];
                    $horarioData = [
                        'horaInicio' => '',
                        'ratingHorario' => '',
                        'ratingZona' => '',
                        'ratingGeneral' => ''
                    ];
                    foreach($diasDisponiblesUsuario as $dia) {
                        $horarioData['horaInicio'] = $dia;
                        $horarioData['ratingHorario'] = 10;
                        $horarioData['ratingZona'] = null;
                        $horarioData['ratingGeneral'] = 10;
                        array_push($horariosLibresDataGeneral, $horarioData);
                    }

                    $fechaBusquedaString = $fechaBusqueda->format('Y-m-d');
                    $diccionarioFechaHorariosLibres = [];
                    if (array_key_exists($idAuto, $diccionarioFechaHorariosLibres)) { //si este auto ya fue agregado solo le pusheo los nuevos valores
                        array_push($diccionarioFechaHorariosLibres[$idAuto], $horariosLibresDataGeneral);
                    } else {
                        $diccionarioFechaHorariosLibres = $horariosLibresDataGeneral; //el auto aun no fue agregado
                    }
                    $horariosTentativos[$fechaBusquedaString][$idAuto] = $diccionarioFechaHorariosLibres; //agrego en el diccionario el nuevo auto con sus horarios disponibles que coinciden con los del alumno
                }
            } else { //aumento el total de dia para poder retornar la cantidad correspondiente
                $totalDiasTentativosRetornar++;
            }
            $fechaBusqueda = DateTime::createFromFormat("Y-m-d", $fechaInicio);
            $fechaBusqueda->modify('+'.$i.' day');
            $nombreDiaBusqueda = strftime("%A",$fechaBusqueda->getTimestamp());
            $i++;
        }

        return $horariosTentativos;     
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

    function obtenerCronogramaDelDia($fecha) {
        $fechaString = $fecha->format('Y-m-d');

        $db = new ConnectionDB();
        $conn = $db->getConnection();
        $state = $conn->prepare('SELECT * FROM clase WHERE clase.fecha = ? ORDER BY clase.horaInicio');
        $state->bind_param('s', $fechaString);
        $state->execute();
        $result = $state->get_result();

        $cronograma = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $cronograma[$row['auto']][] = $row;
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

            mysqli_close($conn);
            return $cronogramaDelDiaPorAuto;
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

    function obtenerZonaAlumno($direccion) {
        return 2;
    }

    function zonasDeClasesVecinas($clases, $horario) {
        $zonasVecinas = [];
        foreach ($clases as $clase) {
            if(obtenerDiferenciaHoraria($clase['horaInicio'], $horario) == 1) {
                array_push($zonasVecinas, $clase['idZona']);
            }
        }

        return $zonasVecinas;
    }

    function obtenerDiferenciaHoraria($horarioLibre, $primerHorarioOcupado) {
        return abs(round((strtotime($horarioLibre) - strtotime($primerHorarioOcupado))/3600, 1));
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

    function obtenerRatingHorario($horariosOcupados, $horarioLibre) {
        if (count($horariosOcupados) === 0) { //no hay horarios ocupados, por ende todos estan libres en el dia.
            return 10;
        }

        $horariosOcupadosSorted = [];        
        foreach ($horariosOcupados as $horarioOcupado) {
            $horariosOcupadosSorted[$horarioOcupado] = abs(obtenerDiferenciaHoraria($horarioOcupado, $horarioLibre));
        }

        asort($horariosOcupadosSorted);
        $horariosOcupadosSortedValues = array_values($horariosOcupadosSorted);
        $horariosOcupadosSortedKeys = array_keys($horariosOcupadosSorted);

        if($horariosOcupadosSortedValues[0] >= 4) {
            return 2;
        } else {
            if ($horariosOcupadosSortedValues[0] == 3) {
                return 4;
            } else { 
                if ($horariosOcupadosSortedValues[0] == 2) {
                    if (count($horariosOcupadosSortedValues) >= 2) {
                        if ($horariosOcupadosSortedValues[1] == 2) { //si el segundo horario mas cercano tambien es a las 2, busco el proximo
                            if (count($horariosOcupadosSortedValues) >= 3) {
                                if ($horariosOcupadosSortedValues[2] == 3) { //esta pegado al anterior horario
                                    return 7;
                                } else { //es 4 o mayor, por lo que no esta pegado al anterior
                                    return 6;
                                }
                            } else { //solo hay 2 horarios ocupados y son ambos a 2 horas de diferencia 
                                return 6;
                            }
                        } else { //el segundo horario mas cercano no es a 2 horas
                            if ($horariosOcupadosSortedValues[1] == 3) {
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
                        if ($horariosOcupadosSortedValues[1] == 2) { //si el segundo horario ocupado es a 2 horas, entonces ya es un 10
                            //evaluo si la diferencia entre [0] y [1] es de 1 hora, si es de 1 hora es un 10, sino un 8
                            if(obtenerDiferenciaHoraria($horariosOcupadosSortedKeys[0], $horariosOcupadosSortedKeys[1]) == 1) {
                                return 10;
                            } else {
                                return 8;
                            }
                        } else {
                            if ($horariosOcupadosSortedValues[1] == 1) { //el segundo horario ocupado tambien es a 1 hora
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


    $disponibilidad = [
        'Monday' => ['09:00', '12:00', '15:00', '19:00'], 
        'Tuesday' => null,
        'Wednesday' => ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00','19:00'],
        'Thursday' => ['08:00', '09:00', '10:00', '11:00', '12:00', '17:00', '18:00', '19:00'],
        'Friday' => ['09:00', '12:00', '15:00', '19:00'],
        'Saturday' => null,
        'Sunday' => ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00','19:00']
    ];

    echo json_encode(obtenerCronograma(4, $disponibilidad, 'no importa la direccion por ahora', '2020-01-09'));

?>