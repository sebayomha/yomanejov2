<?php
    iconv_set_encoding("internal_encoding", "UTF-8");
    date_default_timezone_set('America/Argentina/Buenos_Aires');
    require_once('connectDB.php');
    require_once('token.php');
    require_once('utils.php');
    require __DIR__ . '../../../vendor/autoload.php';
    use \Firebase\JWT\JWT;

    use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;


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
                            'data' => 'Contrasenia correcta'
                        ];
                    }else{ //En este caso la PW es la default
                        $data = (object) [
                            'code'=> 2,
                            'iduser'=> $result[0]['idUsuario'],
                            'name'=> $result[0]['nombre'],
                            'role'=> $result[0]['role'],
                            'data'=> 'la contraseña del usuario es la default'
                        ];
                    }
                 }else { //En este caso el usuario es correcto y la pw es incorrecta
                    $data = (object) [
                        'code' => 1,
                        'data' => 'Usuario o contraseña incorrecto'
                    ];                   
                }               
                }else { //No lo encontro porque no existe el usuario
                    $data = (object) [
                        'code' => 3,
                        'data' => 'Usuario o contraseña incorrecto'
                    ];
                }
            mysqli_close($this->conn);
            return $data;
        } catch(Exception $e) {
            mysqli_close($this->conn);
            return $e->errorMessage();
        }
    }

    function firstPasswordChange($user) {
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
            if (password_verify($user['password'], $result[0]['password'])) { //no modifico la pw
                $data = (object) [
                    'code' => 1,
                    'data' => 'La nueva contraseña tiene que ser distinta a la default'
                ];
                mysqli_close($this->conn);
                return $data;
            } else { //la nueva pw es distinta a la actual, caso exitoso
                $firstPasswordChange = 'true';
                $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
                

                $state = $this->conn->prepare('UPDATE usuario SET password = ?, firstPasswordChange = ? WHERE usuario.email = ?');
                $state->bind_param('sss',$hashedPassword, $firstPasswordChange, $user['email']);
                if ($state->execute()) {
                    return $this->login($user);
                } else {
                    $data = (object) [
                        'code' => 1,
                        'data' => 'Ocurrio un error al actualizar la contraseña'
                    ];
                    mysqli_close($this->conn);
                    return $data;
                }
                mysqli_close($this->conn);
                return $data;
            }
        } else {
            $data = (object) [
                'code' => 3,
                'data' => 'Usuario o contraseña incorrecto'
            ];
            mysqli_close($this->conn);
            return $data;
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
            $loginTime = time();
            $expirationRefreshToken = strtotime(date('Y-m-d H:i:s', strtotime("+1 month")));
            $state = $this->conn->prepare('INSERT INTO tokenusuario (idUsuario, refreshToken, expirationRefreshToken, loginTime) VALUES (?,?,?,?)');
            $state->bind_param('isss', $idUsuario, $rt, $expirationRefreshToken, $loginTime);
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

    function changePassword($idUsuario, $oldPassword, $newPassword) {
        if ($oldPassword == $newPassword) { //la nueva contraseña es igual a la anterior
            return false;
        }

        $conn = $this->db->getConnection();
        $state = $this->conn->prepare('SELECT usuario.idUsuario, usuario.email, usuario.password, usuario.role, usuario.nombre FROM usuario WHERE usuario.idUsuario = ?');
        $state->bind_param('i', $idUsuario);
        $state->execute();
        $ress = $state->get_result();

        if($ress->num_rows > 0){
            $result = array();
            while($row = $ress->fetch_assoc()){
                $result[] = $row;
            }

            if (password_verify($newPassword, $result[0]['password'])) { //la nueva contraseña es igual a la anterior
                return false;
            }

            if (!password_verify($oldPassword, $result[0]['password'])) { //la antigua contraseña no es igual a la actual
                return false;
            }

            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $now = date('Y-m-d h:i:s a', time());
            $state = $this->conn->prepare('UPDATE usuario SET password = ?, changePasswordTime = ? WHERE usuario.idUsuario = ?');
            $state->bind_param('ssi', $hashedNewPassword, $now, $idUsuario);
            if ($state->execute()) { //password actualizada correctamente
                $token = new Token();
                $result['iat'] = time();
                $result['exp'] = strtotime(date('Y-m-d H:i:s', strtotime("+10 min")));
                $result['idUsuario'] = $result[0]['idUsuario'];
                $result['role'] = $result[0]['role'];
                $result['nombre'] = $result[0]['nombre'];
                unset($result[0]);  //Unset
                $jwt = $token->generateToken($result, $hashedNewPassword); //genero un nuevo token porque los voy firmando con la contraseña
                $data = (object) [
                    'code'=> 0,
                    'data'=> "Contraseña actualizada correctamente",
                    'jwt'=>$jwt
                ];
                return $data;
            }
        }
    }

    function changeForgottenPassword($idUsuario, $newPassword, $token) {
        $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $now = date('Y-m-d h:i:s a', time());
        $state = $this->conn->prepare('UPDATE usuario SET password = ?, changePasswordTime = ? WHERE usuario.idUsuario = ?');
        $state->bind_param('ssi', $hashedNewPassword, $now, $idUsuario);
        if ($state->execute()) { //password actualizada correctamente
            $user = (array) [
              'email' => $token->email,
              'password' => $newPassword 
            ];
            return $this->login($user);
        } else {
            return false;
        }
    }

    function forgotPasswordEmail($email) {
        $conn = $this->db->getConnection();
        $state = $this->conn->prepare('SELECT usuario.idUsuario, usuario.email, usuario.password, usuario.role, usuario.nombre FROM usuario WHERE usuario.email = ?');
        $state->bind_param('s', $email);
        $state->execute();
        $ress = $state->get_result();

        if($ress->num_rows > 0){
            $result = array();
            while($row = $ress->fetch_assoc()){
                $result[] = $row;
            }

            $token = new Token();
            $result['iat']          = time();
            $result['exp']          = strtotime(date('Y-m-d H:i:s', strtotime("+1 hour")));
            $result['idUsuario']    = $result[0]['idUsuario'];
            $result['role']         = $result[0]['role'];
            $result['nombre']       = $result[0]['nombre'];
            $result['email']        = $result[0]['email'];
            $hashedPassword         = $result[0]['password'];
            unset($result[0]);  //Unset
            $jwt = $token->generateToken($result, $hashedPassword); //genero el token para recuperar la pw
            $host = getenv('HOST');
            
            $url = 'http://'.$host.'/cambiarContraseña/'.$jwt;
            $mailContent = $this->getMailMessage($result['nombre'], $url);
            
            try {
                
                $mail = new PHPMailer(true); //Server settings
                        
                $mail->isSMTP();                                            // Send using SMTP
                $mail->Host       = 'smtp.gmail.com';                    // Set the SMTP server to send through
                $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                $mail->Username   = 'yomanejoserver@gmail.com';                     // SMTP username
                $mail->Password   = 'hpyqykxjvhqcnkwy';                               // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
                $mail->Port       = 587;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

                $mail->CharSet = 'UTF-8';
                //Recipients
                $mail->setFrom('yomanejoserver@gmail.com', 'YoManejo');
                $mail->Sender = 'yomanejoserver@gmail.com';
                $mail->From = 'yomanejoserver@gmail.com';
                $mail->AddAddress($email);


                // MS Outlook custom header
                // May set to "Urgent" or "Highest" rather than "High"
                $mail->AddCustomHeader("X-MSMail-Priority: High");
                // Not sure if Priority will also set the Importance header:
                $mail->addCustomHeader("X-Message-Flag: High"); 
                $mail->AddCustomHeader('X-Priority: 1');
                $mail->AddCustomHeader('X-Mailer: PHP/' . phpversion());

                $mail->isHTML(true);                                  // Set email format to HTML
                $mail->Subject = 'Reestablecer contraseña';
                $mail->Body = $mailContent;
                $mail->send();
                return true;
            }   catch (Exception $e) {
                return false;
            }
        } else {
            return true; //aunque no exista el correo, es para evitar que se sepa que no existe el usuario
        }
    }

    function getMailMessage($name, $url) {
        $content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	        <html xmlns="http://www.w3.org/1999/xhtml">
	        <head>
	            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	        </head>
	        <body>

	        <div>
                <p style="font-weight: 500;"> Hola '.$name.' !</p>
                <p>Por favor, haga click <a style="font-weight: 500" href ="'.$url.'">aquí</a> para reestablecer su contraseña.</p>
                <p>Este link expirará en 1h.</p>
            </div>
            
            <div>
                <p>Muchas gracias</p>
                <p>El equipo de YoManejo</p>
            </div>
	        </body>
	        </html>';
	        return $content;
    }
}


?>