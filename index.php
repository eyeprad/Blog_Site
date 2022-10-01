<?php
   include("config.php");
   //session_start();

   // check if logged in
   if(!isset($_SESSION['loggedin'])){
      header("location:login.php");
      die();
   }

   function generate_string($input, $strength = 16) {
      $input_length = strlen($input);
      $random_string = '';
      for($i = 0; $i < $strength; $i++) {
          $random_character = $input[mt_rand(0, $input_length - 1)];
          $random_string .= $random_character;
      }

      return $random_string;
  }

  if($_SERVER["REQUEST_METHOD"] == "POST") {

     if(!$db){
       //  console.log($db);
        $error = 'Not Connected Server';
    }
   // generating 10 random string


    if (isset($_POST['Database_Initialize'])) {
     echo "<script>console.log('posting?' );</script>";

     $sql = "CREATE TABLE Topics (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        firtname VARCHAR(30) NOT NULL
        )";

        $result = mysqli_query($db,$sql);

        if ($result) {
            echo "Database created successfully!";
        } else {
            echo "Error creating table: " . $db->error;
        }

     $permitted_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

     for($i =0; $i < 10; $i ++){
        $temp  = generate_string($permitted_chars, 10);

        // $sql2 = "INSERT INTO Guests(firtname)
        // VALUES ('$temp')";
        echo "<script>console.log('this is my function?' );</script>";


        //-----sql injection protection
        $stmt = $db->prepare("INSERT INTO Topics (firtname) VALUES (?)");
        $stmt->bind_param("s", $firtname);

        $firtname = $temp;
        $stmt->execute();

     //    if ($db->query($stmt) === TRUE) {
     //       echo "New record reated successfully";
     //   } else {
     //       echo "Error: " . $sql2 . "<br>" . $db->error;
     //   }
     }
    }

  //   $stmt->close();
  //   $db->close();
  }

?>

<html>

   <head>
       <meta charset="utf-8">
      <title>Welcome </title>
      <link href="style.css" rel="stylesheet" type="text/css">
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
   </head>
   <style>
</style>

   <body class="loggedin">
       <nav class="navtop">
           <div>
               <h1><a style href="index.php">BLOGSSTER</a></h1>
               <a href="blogListings.php"></i>View Blogs</a>
               <a href="blog.php"><i class="fas fa-plus"></i>Create Blog</a>
               <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
           </div>
       </nav>
       <div class="content">
           <h2>Home</h2>
           <p style="font-size:32px;">Welcome back, <?=$_SESSION['user_name']?>!</p>
           <p style="font-size:18px;">To ensure the quality of the website, each user can post at most 2 blogs a 
day, and each user can give at most 3 comments in one day. For each blog, the user who posted 
the blog cannot  give any comment  (no self-comment), and another user can give at most one 
comment. Each blog is identified by a blog id, subject, description, and a list of tags for search 
purposes. Each comment is identified by a comment id, a sentiment (positive or negative), and a 
description. </p>
           <form action = "" method = "post">
              <!-- <input type = "submit" value = " Initialize Data "/> -->
              <input id="btn" type="submit" name="Database_Initialize" value="Initialize Database" />
              <!-- <span><?php echo ((isset($error) && $error != '') ? $error : ''); ?> </span> -->
           </form>
       </div>
   </body>

</html>
