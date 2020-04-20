<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');
    //Va a ser utilizada cuando existan sesiones
    //require_once('token.php');

    class Auto {

        public $db;
        public $conn;
        
        function __construct() { 
            $this->db = new ConnectionDB();
            $this->conn = $this->db->getConnection();
        }

        function obtenerAutos() {
            $state = $this->conn->prepare('SELECT * FROM auto');
            $state->execute();
            $result = $state->get_result();
            $autos = (array) [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {

                    array_push($autos, $row);
                }
            } else {
                return [];
            }
            return $autos;
        }

        function obtenerZonas() {
            $state = $this->conn->prepare('SELECT * FROM zonamaster');
            $state->execute();
            $result = $state->get_result();
            $zonas = (array) [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {

                    array_push($zonas, $row);
                }
            } else {
                return [];
            }
            return $zonas;
        }

        function crearAuto($zonaAuto, $patenteAuto, $dispoAuto, $descripAuto, $modeloAuto, $colorAuto) {
            $state = $this->conn->prepare('INSERT INTO auto (patente, color, disponibilidad, descripcion, zonaMaster, modelo) VALUES (?,?,?,?,?,?)');
            $state->bind_param('ssssis', $patenteAuto, $colorAuto, $dispoAuto, $descripAuto, $zonaAuto, $modeloAuto);
            $state->execute();
            $result = $state->get_result();
            
            echo mysqli_error($this->conn);

            if ($result) { 
                return true;
            } else {
                return false;
            }
        }

        function modificarAuto($idAuto, $zonaAuto, $patenteAuto, $dispoAuto, $descripAuto, $modeloAuto, $colorAuto) {
            
            $state = $this->conn->prepare('UPDATE auto SET patente = ?, color = ?, disponibilidad = ?, descripcion = ?, zonaMaster = ?, modelo = ? WHERE auto.idAuto = ?');
            $state->bind_param('ssssisi', $patenteAuto, $colorAuto, $dispoAuto, $descripAuto, $zonaAuto, $modeloAuto, $idAuto);
            $state->execute();
            $result = $state->get_result();
            
            echo mysqli_error($this->conn);

            if ($result) { 
                return true;
            } else {
                return false;
            }
        }

    }

?>