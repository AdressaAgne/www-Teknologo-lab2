<?php
class accountController extends database{
    public $uuid;
    public $username;
    public $email;
    
    //Shaing stuff like password
    protected function hashValue($value, $salt){
        return sha1($salt.$value.$salt);
    }
    
    //Generate a token
    protected function generateToken(){
        return sha1(microtime());
    }
    
    //generating and adding a new token hash for a user
    protected function newToken($uuid){
        $newHash = $this->generateToken();
        $query = $this->_db->prepare("UPDATE account SET cookieHash=:hash WHERE uuid = :uuid");
        $arr = array(
            'uuid' => $uuid,
            'hash' => $newHash
        );
        $this->arrayBinder($query, $arr);
        $query->execute();
        
        return $newHash;
    }
    
    // Normal Login
    public function login($username, $password, $remember){
        $cookieTimer = (time() + (86400 * 14));
        //fetch Unique Salt for user
        $hashQuery = $this->_db->prepare("SELECT salt FROM account WHERE username = :username");
        $hashArr = array(
            'username' => $username
        );
        $this->arrayBinder($hashQuery, $hashArr);
        $hashQuery->execute();
        
        //check if user exists
        if ($hashQuery->rowCount() === 1) {
            $hash = $hashQuery->fetch(PDO::FETCH_ASSOC);
            
            //prepare to check if user got right password
            $query = $this->_db->prepare("SELECT * FROM account WHERE username = :username AND password = :password");
            $arr = array(
                'username' => $username,
                'password' => $this->hashValue($password, $hash['salt'])
            );
            $this->arrayBinder($query, $arr);
            $query->execute();
            
            // Do stuff when right userinformation is entered.
            if ($query->rowCount() === 1) {
                $user = $query->fetch(PDO::FETCH_ASSOC);
                
                $this->uuid = $user["uuid"];
                $this->username = $user["username"];
                $this->email = $user["mail"];
                
                $_SESSION['uuid'] = $this->uuid;
                //$_SESSION['token'] = $user['cookieHash'];
                
                if($remember){
                    setcookie('lab2_uuid', $this->uuid, $cookieTimer, "/");
                    setcookie('lab2_token', $this->newToken($this->uuid), $cookieTimer, "/");
                    setcookie('lab2_remember', true, $cookieTimer, "/");
                }
                return true;
            } else {
                $_GET['error'] = "Wrong login Information";
            }
        } else {
            $_GET['error'] = "Wrong login Information";
        }
    }
    
    //complete userlogout
    public function logout() {
        unset($_SESSION['remember']);
	unset($_SESSION['uuid']);
        unset($_SESSION['token']);
		
        setcookie('lab2_remember', null, -1, '/');
	setcookie('lab2_token', null, -1, '/');
	setcookie('lab2_uuid', null, -1, '/');
		
	unset($_COOKIE['lab2_remember']);
	unset($_COOKIE['lab2_token']);
	unset($_COOKIE['lab2_uuid']);
		
	session_destroy();
	}
    
    //login using cookies
    public function loginAdvanced($uuid, $hash){
        $cookieTimer = (time() + (86400 * 14));
        //prepare to check if user got right password
        $query = $this->_db->prepare("SELECT * FROM account WHERE uuid = :uuid AND cookieHash = :hash");
        $arr = array(
            'uuid' => $uuid,
            'hash' => $hash
        );
        $this->arrayBinder($query, $arr);
        $query->execute();

        // Do stuff when right userinformation is entered.
        if ($query->rowCount() === 1) {
            $user = $query->fetch(PDO::FETCH_ASSOC);
            $this->uuid = $user["uuid"];
            $this->username = $user["username"];
            $this->email = $user["mail"];

            $_SESSION['uuid'] = $this->uuid;
            
            setcookie('lab2_uuid', $this->uuid, $cookieTimer, "/");
            setcookie('lab2_token', $this->newToken($this->uuid), $cookieTimer, "/");
            
            return true;
        } else {
            $_GET['error'] = "You were logged out from another computer.";
        }
    }

    // Create an account
    public function addAccount($username, $password, $mail){
        $query = $this->_db->prepare("INSERT INTO account (username, password, salt, mail) VALUES(:username, :password, :salt, :mail)");
        
        $salt = $this->generateToken();
        
        $arr = array(
            'username'  => $username,
            'password'  => $this->hashValue($password, $salt),
            'mail'      => $mail,
            'salt'      => $salt
        );
        $this->arrayBinder($query, $arr);
        $query->execute();
    }
    
    public function lostPassword($username, $mail){
        //Todo: Send mail to user with link so they can change there password if needed.
    }
}
$account = new accountController();
