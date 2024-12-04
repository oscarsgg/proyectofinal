<?php 
    require "con.php";

    class Admin {

        private $email;
        private $password;
        private $confPassword;
        
        public function get_email(){
            return $this->email;
        }
        public function set_email($email){
            $this->email = $email;
        }
        public function get_password(){
            return $this->password;
        }
        public function set_password($password){
            $this->password = $password;
        }
        public function get_confPassword(){
            return $this->password;
        }
        public function set_confPassword($confPassword){
            $this->confPassword = $confPassword;
        }


        public function __construct($email = '', $password = '', $confPassword = '') {
            $this->email = $email;
            $this->password = $password;
            $this->confPassword = $confPassword;
        }  
              
        public function insertAdmin($connection) {
            $mensaje = '';
            if(strlen($this->password) === 0 && strlen($this->email) === 0 && strlen($this->confPassword) === 0){
                return $mensaje;
            }else if ($this->email === '') {
                $mensaje = "El correo electrónico no puede estar vacío";
                return $mensaje;
            }else if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                $mensaje = "Escribe la dirección de correo electrónico con el formato someone@example.com.";
                return $mensaje;
            }else if(strlen($this->email) >= 25){
                $mensaje = "Tu correo debe ser menor a 25 carracteres";
                return $mensaje;
            }else if(strlen($this->password) >= 25){
                $mensaje = "La contraseña debe ser menor a 25 carracteres";
                return $mensaje;
            }else if(strlen($this->confPassword) >= 25){
                $mensaje = "La contraseña debe ser menor a 25 carracteres";
                return $mensaje;
            }else if (strlen($this->password) === 0) {
                $mensaje = "La contraseña no puede estar vacío";
                return $mensaje;
            }else if($this->password != $this->confPassword){
                $mensaje = "Las contraseñas no conciden"; 
                return $mensaje;
            }else{
                $query = "CALL SP_insertAdmin('".$this->email."', '".$this->password."',@msj)";
                $stmt = mysqli_query($connection,$query);
                if($stmt){
                    $query = "select @msj as msj";
                    $stmt = mysqli_query($connection,$query);
                    $row = $stmt->fetch_assoc();
                    $mensaje = $row['msj'];
                    return $mensaje;
                }
            }
        }

    }



    
    
?>