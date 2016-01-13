<?php
    session_start();
    require_once("class/pdo.php");
    require_once("class/accountController.php");
    
    /* Database
    CREATE TABLE `account` (
      `uuid` int(11) NOT NULL,
      `username` varchar(64) NOT NULL,
      `mail` varchar(255) NOT NULL,
      `password` varchar(255) NOT NULL,
      `salt` varchar(255) NOT NULL,
      `cookieHash` varchar(255) NOT NULL,
      `timeStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
    */

    //$account->addAccount("username", "password", "mail");

    $loginHidden = "";
    $logoutHidden = "hidden";
    
    if(isset($_POST['logout'])){
        $account->logout();
        header("location: index.php");
    }

    if(isset($_POST['un']) && isset($_POST['pw'])){
        if(isset($_POST['rememberme'])){
            $remember = true;
        } else {
            $remember = false;
        }
        if($account->login($_POST['un'], $_POST['pw'], $remember)){
            $loginHidden = "hidden";
            $logoutHidden = "";
        }
    }

    if(isset($_COOKIE['lab2_remember'])){
       if($account->loginAdvanced($_COOKIE['lab2_uuid'], $_COOKIE['lab2_token'])){
            $loginHidden = "hidden";
            $logoutHidden = "";
       }
        
    }
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>www-teknologi lab 2</title>
    <link rel="stylesheet" href="css/main.css">
  </head>
  <body>
    <div class="container">
      <form class="form" method="post" <?=$loginHidden?>>
        <?php
        if(isset($_GET['error'])){
            echo $_GET['error'];
        }
        ?>
        <div class="form__elm">
          <label for="un" class="form__label">Username
            <input type="text" placeholder="Psername" id="un" name="un" class="input__text" required="" autofocus="">
          </label>
        </div>
        <div class="form__elm">
          <label for="pw" class="form__label">Password
            <input type="password" placeholder="Password" id="pw" name="pw" class="input__text" required="">
          </label>
        </div>
        <div class="form__elm">
            <label for="remeberme" class="form__label">Remeber me
                <input type="checkbox" name="rememberme" id="remeberme">
            </label>
        </div>
        <div class="form_elm">
          <input type="submit" value="Login" class="input__btn">
        </div>
      </form>
      
       <form class="form" method="post"<?=$logoutHidden?>>
        <p>Welcome <?=$account->username?></p>
        <div class="form_elm">
          <input type="submit" value="Logout" name="logout" class="input__btn">
        </div>
      </form>
      
    </div>
    </div>
  </body>
</html>