<?php
   include("config.php");
   //session_start();

   // check if logged in
   if(!isset($_SESSION['loggedin'])){
      header("location:login.php");
      die();
   }

   if($_SERVER["REQUEST_METHOD"] == "POST") {
       $mySubj = mysqli_real_escape_string($db,$_POST['blog_subject']);
       $myDesc = mysqli_real_escape_string($db,$_POST['blog_des']);
       $myTagString = mysqli_real_escape_string($db,$_POST['blog_tags']);

       $myTags = explode(",", $myTagString);
       for($x = 0; $x < count($myTags); $x++) {
           $myTags[$x] = trim($myTags[$x]);
       }
       $myTags = array_unique($myTags);

       if(!$db){
        $error = 'Not Connected Server';
       }

       $myPostUser = $_SESSION['login_user'];

       // Check if posted twice already
       $checkQuery = "SELECT * FROM blogs
       WHERE postuser = '$myPostUser' AND pdate = CURDATE()";
       $result = mysqli_query($db,$checkQuery);
       $count = mysqli_num_rows($result);




       if($count > 1) { // two already posted today
           $error = "ERROR!!! You've already posted twice today";
           /*?>
           <script type="text/javascript">
           alert("ERROR!!!! You've already posted twice today");
           </script>
               <?php*/
       }
       else { // user can post
           // POST BLOG
           $sql = "INSERT INTO blogs(subject, description, postuser, pdate)
           VALUES ('$mySubj', '$myDesc', '$myPostUser', CURDATE())"; // CURDATE() gets current date
           $error = "";

           $result = mysqli_query($db,$sql);
           $blogID = mysqli_insert_id($db);
           if($result){
               // POST TAG
               for($x = 0; $x < count($myTags); $x++) {
                   //$myTrimmedTag = trim($myTags[$x]);
                   $sql2 = "INSERT INTO blogstags(blogid, tag)
                   VALUES ('$blogID', '$myTags[$x]')";

                   $result2 = mysqli_query($db,$sql2);
               }

               if($result2) {
                   $error = "Blog has been posted";
                   /*
                   ?>
                   <script type="text/javascript">
                   alert("Blog has been posted");
                   </script>
                       <?php*/
                 } // end of if (result2)
                 else {
                    $error = "Error! Tags weren't saved";
                 }
               }
           else{
                $error = "Error! Blog didn't post";
           }// end of else (result)
       } // end of else (check 2 posts)
   }// end of POST if






?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Main Page</title>
		<link href="style.css" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
	</head>
	<body class="loggedin">
		<nav class="navtop">
            <div>
                <h1><a style href="index.php">BLOGSSTER</a></h1>
                <a href="blogListings.php"></i>View Blogs</a>
                <a href="blog.php"><i class="fas fa-plus"></i>Create Blog</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a>
            </div>
		</nav>
		<div class="blog-content">
			<h2>Create a Blog</h2>
                <form action = "" method = "post">
                   <label>Subject: </label>
                   <input pattern="^[a-zA-Z1-9].*" type = "text" placeholder="Subject" name = "blog_subject" required class = "box"/><br />
                   <label>Description: </label>
                   <input pattern="^[a-zA-Z1-9].*" type = "text" placeholder="Description" name = "blog_des" required class = "box" /><br/>
                   <label>Tags: </label>
                   <input pattern="^[a-zA-Z1-9].*" type = "text" placeholder="separate by comma, no spaces (i.e. tag1,tag2,tag3)" name = "blog_tags" required class = "box" /><br />
                   <span style="font-size:20px"><strong><?php echo ((isset($error) && $error != '') ? $error : ''); ?> </strong></span> <br>
                   <input id="btn" type = "submit" name="blog_submit" value = " Post Blog "/>
                </form>
		</div>
	</body>
</html>
<?php
    unset($_nameError);
?>
