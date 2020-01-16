<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');
    setlocale(LC_ALL,"es_AR");
    //Va a ser utilizada cuando existan sesiones
    //require_once('token.php');


    //Funcion principal que se encargara de armar el cronograma
    function obtenerCronograma($cantClases, $disponibilidad, $direccion, $fechaInicio){
        $fechaBusqueda = DateTime::createFromFormat("Y-m-d", $fechaInicio);
        $nombreDiaBusqueda = strftime("%A",$fechaBusqueda->getTimestamp());
        
        $zonas = obtenerZonas(); //obtengo las zonas y las cargo solo 1 vez

        $totalDiasTentativosRetornar; //Total de dias tentativos a retornar
        $cantDiasTentativos = 0; //cantidad actual de dias tentativos acumulados;

        switch ($cantClases) {
            case 1:  $totalDiasTentativosRetornar = 7; break;
            case 4:  $totalDiasTentativosRetornar = 14; break;
            case 8:  $totalDiasTentativosRetornar = 21; break;
            case 12: $totalDiasTentativosRetornar = 30; break;
        };

        $horariosTentativos = array();

        $i = 1;
        //$cantDiasTentativos <= $totalDiasTentativosRetornar
        while ($i <= 1) { //Se recorrera hasta tanto obtener la cantidad de dias posibles a retornar
            if ($disponibilidad[$nombreDiaBusqueda] != null) { //entonces es un dia que el usuario esta disponible
                $clasesDelDiaPorAuto = obtenerCronogramaDelDia($fechaBusqueda); //armo un diccionario auto => clases
                $clasesDelDiaPorAuto = eliminarAutosInactivos($clasesDelDiaPorAuto, $fechaBusqueda); //quito los autos que esten inactivos esa fecha

                //hasta el momento no estoy teniendo en cuenta los autos que ese dia no tienen asignada ninguna clase y estan absolutamente libres !!!!
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
                        $horarioData['ratingHorario'] = obtenerRatingHorario($horarioAuto, $horariosOcupados);
                        $horarioData['ratingZona'] = obtenerRatingZona(3, 1, $zonas);
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

            }
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
            return "0 results";
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

    function obtenerRatingZona($zonaCliente, $zonaClase, $zonas) {
        $zonasAledañasAlCliente = $zonas[$zonaCliente];
        if (in_array($zonaCliente, $zonasAledañasAlCliente)) { //es una zona aledaña entonces es grado 1

        } else { //no es una zona aledaña, debo empezar a buscar zona por zona
            foreach ($zonasAledañasAlCliente as $zona) {
                $zonas[$zona];
            }
        }
        return 10;
    }

    function obtenerDiferenciaHoraria($horarioLibre, $primerHorarioOcupado) {
        return abs(round((strtotime($horarioLibre) - strtotime($primerHorarioOcupado))/3600, 1));
    }

    function obtenerPrimerHorarioOcupado($horariosOcupados, $positivo) {
        if ($positivo) {
            return $horariosOcupados[0];
        } else {
            return end($horariosOcupados);
        }
    }

    function obtenerSegundoHorarioOcupado($horariosOcupados, $positivo) {
        if ($positivo) {
            return $horariosOcupados[1];
        } else {
            return $horariosOcupados[count($horariosOcupados) - 2];
        }
    }

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

    function obtenerRatingHorario($horario, $horariosOcupados) { 
        if ($horario === '08:00' || $horario === '19:00' || $horario === '09:00' || $horario === '18:00') { //es el primer horario o el ultimo horario
            return obtenerRating819y918($horariosOcupados, $horario);
        }
        
        if ($horario === '10:00') { //los horarios del medio
            return obtenerRating10($horariosOcupados, $horario);
        }
        
    }

    $disponibilidad = [
        'lunes' => '09:00-15:00',
        'martes' => null,
        'miercoles' => '08:00-20:00',
        'jueves' => ['08:00', '09:00', '10:00', '11:00', '12:00', '17:00', '18:00', '19:00'],
        'viernes' => '14:00-20:00',
        'sabado' => null,
        'domingo' => '11:00-16:00'
    ];

    echo json_encode(obtenerCronograma(4, $disponibilidad, 'no importa la direccion por ahora', '2020-01-09'));

?>