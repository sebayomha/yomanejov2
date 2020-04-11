<?php
	require __DIR__ . '../../../vendor/autoload.php';
    use \Firebase\JWT\JWT;
	
	class Token{

		function generateToken($payload, $pass){
			$key = getenv('KEY').$pass;
            $jwt = JWT::encode($payload, $key);
            return $jwt;
		}

		function decodeToken($jwt, $pass){
			try{
				$key = getenv('KEY').$pass;
				$result = JWT::decode($jwt, getenv('KEY').$pass, array('HS256'));
			} catch ( \Firebase\JWT\ExpiredException $e ) {
				return "expired";
			}catch(Exception $e){
				$result =  false;
			}
			return $result;
		}
	}
?>