<?php
include('lib/common.php');
// written by GTusername1

if($showQueries){
  array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

//Note: known issue with _POST always empty using PHPStorm built-in web server: Use *AMP server instead
if( $_SERVER['REQUEST_METHOD'] == 'POST') {

    $enteredUsername = mysqli_real_escape_string($db, $_POST['username']);
    $enteredPassword = mysqli_real_escape_string($db, $_POST['password']);

    if (empty($enteredUsername)) {
            array_push($error_msg,  "Please enter an usnername.");
    }

    if (empty($enteredPassword)) {
            array_push($error_msg,  "Please enter a password.");
    }
    
    if ( !empty($enteredUsername) && !empty($enteredPassword) )   { 

        $query = "SELECT password FROM LoginUser WHERE username='$enteredUsername'";

        $result = mysqli_query($db, $query);
        include('lib/show_queries.php');
        $count = mysqli_num_rows($result); 
        
        if (!empty($result) && ($count > 0) ) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $storedPassword = $row['password']; 
            
            $options = [
                'cost' => 8,
            ];
             //convert the plaintext passwords to their respective hashses
             // 'michael123' = $2y$08$kr5P80A7RyA0FDPUa8cB2eaf0EqbUay0nYspuajgHRRXM9SgzNgZO
            $storedHash = password_hash($storedPassword, PASSWORD_DEFAULT , $options);   //may not want this if $storedPassword are stored as hashes (don't rehash a hash)
            $enteredHash = password_hash($enteredPassword, PASSWORD_DEFAULT , $options); 
            
            if($showQueries){
                array_push($query_msg, "Plaintext entered password: ". $enteredPassword);
                //Note: because of salt, the entered and stored password hashes will appear different each time
                array_push($query_msg, "Entered Hash:". $enteredHash);
                array_push($query_msg, "Stored Hash:  ". $storedHash . NEWLINE);  //note: change to storedHash if tables store the plaintext password value
                //unsafe, but left as a learning tool uncomment if you want to log passwords with hash values
                //error_log('email: '. $enteredUsername  . ' password: '. $enteredPassword . ' hash:'. $enteredHash);
            }
            
            //depends on if you are storing the hash $storedHash or plaintext $storedPassword 
            if (password_verify($enteredPassword, $storedHash) ) {
                array_push($query_msg, "Password is Valid! ");
                $_SESSION['username'] = $enteredUsername;
                array_push($query_msg, "logging in... ");

                $query = "SELECT username  FROM Manager WHERE username='$enteredUsername'";
                $result = mysqli_query($db, $query);
                $count = mysqli_num_rows($result); 
                if($count > 0) {$_SESSION['is_manager']=1;}

                $query = "SELECT username  FROM InventoryClerk WHERE username='$enteredUsername'";
                $result = mysqli_query($db, $query);
                $count = mysqli_num_rows($result); 
                if($count > 0) {$_SESSION['is_inventorycleck']=1;}

                $query = "SELECT username  FROM Salespeople WHERE username='$enteredUsername'";
                $result = mysqli_query($db, $query);
                $count = mysqli_num_rows($result); 
                if($count > 0){$_SESSION['is_salespeople']=1;}

                array_push($query_msg, "roles: ".$_SESSION['is_manager'].$_SESSION['is_salspeople'].$_SESSION['is_manager']);


                header('location:index.php');      //to view the password hashes and login success/failure
            } else {
                array_push($error_msg, "Login failed: " . $enteredUsername . NEWLINE);
                array_push($error_msg, "To demo enter: ". NEWLINE . "michael@bluthco.com". NEWLINE ."michael123");
            }
            
        } else {
            array_push($error_msg, "The username entered does not exist: " . $enteredUsername);
        }
    }
}
?>

<?php include("lib/header.php"); ?>
  <title>Login</title>
</head>
<body>
<div class="main_container">
    <div class="top-btn right">
      <button class="btn"><a href="index.php">Home</a></button>
    </div>  <!-- .login-btn -->

    <form name="login_form" method="post" action="login.php" enctype="multipart/form-data">
      <h3>Login</h3> 

      <div class="form-group">
        <label>username*</label>
        <input class="form-control" type="text" name="username" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>password*</label>
        <input class="form-control" type="text" name="password" required>
      </div><!-- .form-group -->

      <input type="submit" class="btn btn-default" name="submit" value="Submit">
    </form>
    <?php include("lib/error.php"); ?>
</div> <!-- .main_container -->
</body>
</html>