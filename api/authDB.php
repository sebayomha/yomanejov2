<?php
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');
    require_once('token.php');
    require_once('utils.php');
    require __DIR__ . '../../../vendor/autoload.php';
    use \Firebase\JWT\JWT;

class Auth{

    private $utils;
    public $db;
    public $conn;

    function __construct(){
        $this->utils = new Utils();
        $this->db = new ConnectionDB();
        $this->conn = $this->db->getConnection();
    }

    function login($user){
        try{
            $conn = $this->db->getConnection();

            $state = $this->conn->prepare('SELECT usuario.firstPasswordChange, usuario.idUsuario, usuario.password, usuario.role, usuario.nombre FROM usuario WHERE usuario.email = ?');
            $state->bind_param('s', $user['email']);
            $state->execute();
            
            $ress = $state->get_result();

            //$data va a ser la respuesta
            $data;

            if($ress->num_rows > 0){
                $result = array();
                while($row = $ress->fetch_assoc()){
                    $result[] = $row;
                }

                //En este caso password y usuario es correcto, entonces creo una sesion
                if(password_verify($user['password'], $result[0]['password'])){
                    if($result[0]['firstPasswordChange'] == 'true'){ //Valido primero que todo que la pw no sea la default
                        $hashedPassword = $result[0]['password'];
                        $result['iat'] = time();
                        $result['exp'] = strtotime(date('Y-m-d H:i:s', strtotime("+10 min")));
                        $result['idUsuario'] = $result[0]['idUsuario'];
                        $result['role'] = $result[0]['role'];
                        $result['nombre'] = $result[0]['nombre'];
                        unset($result[0]);  //Unset
                        $token = new Token();
                        $jwt = $token->generateToken($result, $hashedPassword);
                        $rt = uniqid($result['idUsuario'], true);
                        $rt = $this->storeRefreshToken($result['idUsuario'], $rt);
                        $data = (object) [
                            'code' => $conn->errno,
                            'loggedIn' => true,
                            'jwt' => $jwt,
                            'rt' => $rt,
                            'result' => 'Contrasenia correcta'
                        ];
                    }else{ //En este caso la PW es la default
                        $data = (object) [
                            'code'=> 2,
                            'iduser'=> $result[0]['idUsuario'],
                            'name'=> $result[0]['nombre'],
                            'role'=> $result[0]['role'],
                            'result'=> 'la contraseña del usuario es la default'
                        ];
                    }
                 }else { //En este caso el usuario es correcto y la pw es incorrecta
                    $data = (object) [
                        'code' => 1,
                        'result' => 'Usuario y contrasenia incorrecta'
                    ];                   
                }               
                }else { //No lo encontro porque no existe el usuario
                    $data = (object) [
                        'code' => 1,
                        'message' => 'Usuario no existe'
                    ];
                }
            mysqli_close($this->conn);
            return $data;
        } catch(Exception $e) {
            mysqli_close($this->conn);
            return $e->errorMessage();
        }
    }

    function getPasswordById($idUsuario) {
        $conn = $this->db->getConnection();

        $state = $this->conn->prepare('SELECT usuario.password FROM usuario WHERE usuario.idUsuario = ?');
        $state->bind_param('i', $idUsuario);
        $state->execute();
        
        $ress = $state->get_result();

        if($ress->num_rows > 0){
            $result = array();
            while($row = $ress->fetch_assoc()){
                $result[] = $row;
            }
            mysqli_close($this->conn);
            return $result[0]['password'];
        } else {
            mysqli_close($this->conn);
            return null;
        }
    }

    function storeRefreshToken($idUsuario, $rt) {
        $conn = $this->db->getConnection();
        $state = $this->conn->prepare('SELECT * FROM tokenusuario WHERE tokenusuario.idUsuario = ?');
        $state->bind_param('i', $idUsuario);
        $state->execute();
        $ress = $state->get_result();
        if($ress->num_rows > 0){
            $result = array();
            while($row = $ress->fetch_assoc()){
                $result = $row;
            }
            return $result['refreshToken'];
        } else {
            $expirationRefreshToken = strtotime(date('Y-m-d H:i:s', strtotime("+1 month")));
            $state = $this->conn->prepare('INSERT INTO tokenusuario (idUsuario, refreshToken, expirationRefreshToken) VALUES (?,?,?)');
            $state->bind_param('iss', $idUsuario, $rt, $expirationRefreshToken);
            $state->execute();
            return $rt;
        }
    }

    function validateRefreshToken($refreshToken, $idUsuario) {
        $conn = $this->db->getConnection();
        $state = $this->conn->prepare('SELECT * FROM tokenusuario WHERE tokenusuario.idUsuario = ? AND tokenusuario.refreshToken = ?');
        $state->bind_param('is', $idUsuario, $refreshToken);
        $state->execute();
        $ress = $state->get_result();
        if($ress->num_rows > 0){
            $result = array();
            while($row = $ress->fetch_assoc()){
                $result = $row;
            }

            if ($result['expirationRefreshToken'] <= time()) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    function generatePayload($idUsuario) {
        $payload;
        $conn = $this->db->getConnection();
        $state = $this->conn->prepare('SELECT * FROM usuario WHERE usuario.idUsuario = ?');
        $state->bind_param('i', $idUsuario);
        $state->execute();
        $result = array();
        $ress = $state->get_result();
        while($row = $ress->fetch_assoc()){
            $result = $row;
        }
        $payload['iat'] = time();
        $payload['exp'] = strtotime(date('Y-m-d H:i:s', strtotime("+10 min")));
        $payload['idUsuario'] = $result['idUsuario'];
        $payload['role'] = $result['role'];
        $payload['nombre'] = $result['nombre'];
        return $payload;
    }

    function logout($idUsuario) {
        $conn = $this->db->getConnection();
        $state = $this->conn->prepare('DELETE FROM tokenusuario WHERE tokenusuario.idUsuario = ?');
        $state->bind_param('i', $idUsuario);
        $state->execute();
        $ress = $state->get_result();
        if (mysqli_affected_rows($this->conn) > 0) {
            return true;
        }
        return false;
    }
}


?>