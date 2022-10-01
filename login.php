<?php
   include("config.php");
   //session_start();

   if($_SERVER["REQUEST_METHOD"] == "POST") {
      // username and password sent from form

      $myusername = mysqli_real_escape_string($db,$_POST['username']);
      $mypassword = mysqli_real_escape_string($db,$_POST['password']);


      $sql = "SELECT firstname, username, password  FROM users WHERE username = '$myusername' and password = '$mypassword'";
      $result = mysqli_query($db,$sql);
      $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
      $active = $row['active'];
      $count = mysqli_num_rows($result);
      $error = "";

      $myfName = $row['firstname'];

    //   $error = "";
      // If result matched $myusername and $mypassword, table row must be 1 row

      if($count == 1) {
        //  session_register("myusername");
        //session_regenerate_id();
         $_SESSION['login_user'] = $myusername;
         $_SESSION['user_name'] = $myfName;
         $_SESSION['loggedin'] = TRUE;
         header("location: index.php");
      }else {
        $error = "User Name or Password is wrong";
        //   if(isset($_SESSION))
        //  $error = "Your Login Name or Password is invalid";
      }
   }
?>
<html>

   <head>
      <title>Login Page</title>
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
      <link href="style.css" rel="stylesheet" type="text/css">
   </head>

   <body>
         <div class="login">
            <h1>Login
                <br><br>
                    <span><?php echo ((isset($error) && $error != '') ? $error : ''); ?> </span>
            </h1>
               <form action = "" method = "post">
                  <label><i class="fas fa-user"></i></label>
                  <input type = "text" placeholder="Username" required name = "username" class = "box"/><br /><br />
                  <label><i class="fas fa-lock"></i></label>
                  <input type = "password" placeholder="Password" required name = "password" class = "box" /><br/><br />
                  <input id="btn" type = "submit" value = " Submit "/>
                  <input id="btn" type="button" onclick="window.location.href = 'signup.php';" value="Sign Up"/><br />
               </form>

      </div>

   </body>
</html>
<?php
    unset($error);
?>
