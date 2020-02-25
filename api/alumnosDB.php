<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');
    //Va a ser utilizada cuando existan sesiones
    //require_once('token.php');

    class Alumno {

        public $db;
        public $conn;
        
        function __construct() { 
            $this->db = new ConnectionDB();
            $this->conn = $this->db->getConnection();
        }

        function obtenerAlumnos() {
            $state = $this->conn->prepare('SELECT * FROM alumno ORDER BY alumno.idAlumno DESC');
            $state->execute();
            $result = $state->get_result();
            $alumnos = (array) [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    array_push($alumnos, $row);
                }
            } else {
                return [];
            }
            return $alumnos;
        }
    }

?>