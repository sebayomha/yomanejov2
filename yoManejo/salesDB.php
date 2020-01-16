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
                    
                    $horariosLibres = obtenerHorariosLibresAutoYAlumno($clases, $disponibilidad, $nombreDiaBusqueda);

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
                        $horarioData['ratingHorario'] = obtenerRatingHorario($horarioAuto, $horariosLibres);
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

    function obtenerDiferenciaHoraria($horariosLibres, $horarioLibre, $saltoNivel, $positivo) {
        $primerSiguienteHorarioLibre;
        if($positivo) {
            $primerSiguienteHorarioLibre = $horariosLibres[array_search($horarioLibre, $horariosLibres) + $saltoNivel]; //busco el siguiente horario libre
        } else {
            $primerSiguienteHorarioLibre = $horariosLibres[array_search($horarioLibre, $horariosLibres) - $saltoNivel]; //busco el siguiente horario libre
        }
        return abs(round((strtotime($horarioLibre) - strtotime($primerSiguienteHorarioLibre))/3600, 1));
    }

    function obtenerRating8y19($horariosLibres, $horarioLibre) {
        $positivo = true;
        if ($horarioLibre == '19:00') {
            $positivo = false;
        }

        if (obtenerDiferenciaHoraria($horariosLibres, $horarioLibre, 1, $positivo) == 1) { //la diferencia con el proximo horario libre es de 1, o sea es a las 9 esta libre, entonces por el momento no es un buen horario   
            //evaluo si tiene un horario mas para ver si a las 10 esta libre
            if (count($horariosLibres) >= 3) { //posee otro horario libre, debo evaluar si la diferencia de hora es 2
                if (obtenerDiferenciaHoraria($horariosLibres, $horarioLibre, 2, $positivo) == 2) { //el proximo horario libre es a las 10 entonces sigue sin ser un buen horario, por lo que deberia saltar al nivel 3, por ende es un 4 por el momento                    
                    if (obtenerDiferenciaHoraria($horariosLibres, $horarioLibre, 3, $positivo) == 3) { //es que el proximo libre es a las 11, entonces ya se que debo pasar al nivel 4 a buscar
                        return 2;
                    } else { //a las 11 esta ocupado
                        return 4;
                    }
                } else { //es un buen horario porque a las 9 esta libre pero a las 10 esta ocupado
                    return 6; 
                }
            } else { //no posee otro horario libre, solo a las 8 y 9. Entonces quiere decir que a las 10 esta ocupado
                return 8;
            }
        } else { //la diferencia es > 1, entonces quiere decir que a las 9 esta ocupado, por el momento es un buen horario
            if (count($horariosLibres) >= 3) { //posee otro horario libre, debo evaluar si la diferencia de hora es 2
                if (obtenerDiferenciaHoraria($horariosLibres, $horarioLibre, 1, $positivo) == 2) { //el proximo horario libre es a las 10, entonces es un buen horario porque a las 9 esta ocupado, pero a las 10 esta libre
                    return 8;
                } else { //el proximo horario libre es despues de las 10, entonces es el mejor horario porque a las 9 esta ocupado y a las 10 tambien
                    return 10; 
                }
            } else { //no posee otro horario libre, solo a las 8 porque a las 9 esta ocupado   
                return 8;
            }
        }
    }

    function obtenerRatingHorario($horario, $horariosLibres) { 
        if (count($horariosLibres) === 1) { //si es el unico horario libre entonces es que el resto estan ocupados, por ende es un buen horario
            return 10;
        }
        if ($horario === '08:00' || $horario === '19:00') { //es el primer horario o el ultimo horario
            return obtenerRating8y19($horariosLibres, $horario);
        }

        if ($horario === '09:00' || $horario === '18:00') { //es el segundo horario o el anteultimo, o sea solo puedo avanzar 1 sola posicion
            return 10;
        }
        
        return 10;
    }

    $disponibilidad = [
        'lunes' => '09:00-15:00',
        'martes' => null,
        'miercoles' => '08:00-20:00',
        'jueves' => ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '18:00', '19:00'],
        'viernes' => '14:00-20:00',
        'sabado' => null,
        'domingo' => '11:00-16:00'
    ];

    echo json_encode(obtenerCronograma(4, $disponibilidad, 'no importa la direccion por ahora', '2020-01-09'));

?>