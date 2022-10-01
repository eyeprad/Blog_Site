<?php
   include("config.php");
   //session_start();

   // check if logged in
   if(!isset($_SESSION['loggedin'])){
      header("location:login.php");
      die();
   }

   if($_SERVER["REQUEST_METHOD"] == "POST") {

      if(!$db){
         $error = 'Not Connected to Server';
     }

    //----------------Search Tag Table--- after submit---------------
    if (isset($_POST['submit'])) {
      $mytag = mysqli_real_escape_string($db,$_POST['searchtags']);
      $sqlTag = "SELECT blogid FROM blogstags WHERE tag = '$mytag';";
      $result = $db->query($sqlTag);
      //$idRow = mysqli_fetch_array($result,MYSQLI_ASSOC);

      $blogs = "";

      if(mysqli_num_rows($result) > 0) {

      $blogs .= "<br>";
      $blogs .= "<table border='1'>";
      $blogs .= "<td>Blog ID </td><td>Author Username</td><td>Subject </td><td>Description</td><td>Tags</td>";
      while ($row = mysqli_fetch_assoc($result)) { // Check summary get row on array ..
          $blogs .= "<tr>";
          foreach ($row as $value) { // I you want you can right this line like this: foreach($row as $value) {
              $blogQuery = "SELECT postuser, subject, description, pdate FROM blogs WHERE blogid = '$value'";
              $BlogResult = $db->query($blogQuery);
              $blogRow = mysqli_fetch_array($BlogResult,MYSQLI_ASSOC);

              $blogSub = $blogRow['subject'];
              $blogDes = $blogRow['description'];
              $blogdate = $blogRow['pdate'];
              $blogID =$row['blogid'];
              $blogAuthor = $blogRow['postuser'];

              $blogs .= "<td>" . $blogID . "</td><td>" . $blogAuthor . "</td><td>" . $blogSub . "</td> <td>" . $blogDes . "</td><td>" . $mytag . "</td>"; // I just did not use "htmlspecialchars()" function.

          }
          $blogs .= "</tr>";
      }
      $blogs .= "</table>";

  } else {
      $blogs = "No blogs matched the tag!";
  }


   //    if ($result->num_rows > 0) {
   //       echo "Result for Tag search <br>";
   //       while($row = $result->fetch_assoc()) {
   //           echo "blogid: " . $row["blogid"]. " - tag: " . $row["tag"]. "<br> ";
   //       }
   //   } else {
   //       echo "0 results";
   //   }

    }
    // if "Post Comment" btn is pressed
    if (isset($_POST['comment_submit'])) {
        $myChosenID = mysqli_real_escape_string($db,$_POST['blog_ID']);
        $myComment = mysqli_real_escape_string($db,$_POST['comment_des']);
        $mySentiment= mysqli_real_escape_string($db,$_POST['commSentiment']);
        $myPostUser = $_SESSION['login_user'];

        // Check if Blog ID Exists
        $searchBlogQuery = "SELECT blogid FROM blogs WHERE blogid = '$myChosenID'";

        $resultOfsearchBlogQ = $db->query($searchBlogQuery);


        $error = "";

        if(mysqli_num_rows($resultOfsearchBlogQ) < 1) {
            $error = "Error! The given blog ID isn't valid!";
        } else {
            // Blog ID Exists
            // Check if author posted 3 times already
            $checkComLimitQ = "SELECT * FROM Comments
            WHERE author = '$myPostUser' AND cdate = CURDATE()";
            $ComLimitQResult = mysqli_query($db,$checkComLimitQ);
            $countOfComToday = mysqli_num_rows($ComLimitQResult);

            if($countOfComToday > 2) { // three already posted today
                $error = "Error! You've already posted three comments today";
            } else {
                // Check if author is double commenting on blog
                $doubleCommentQ = "SELECT * FROM Comments WHERE author='$myPostUser' AND blogid='$myChosenID'";
                $doubleCommResult = mysqli_query($db,$doubleCommentQ);
                if(mysqli_num_rows($doubleCommResult) > 0) {
                    $error = "Error! You have already commented on this blog!";
                }
                else { // autor is not double commenting
                    // Check if author is commenting on own blog
                    $ownBlogQ = "SELECT * FROM blogs WHERE postuser='$myPostUser' AND blogid='$myChosenID'";
                    $ownBlogResult = mysqli_query($db,$ownBlogQ);
                    if(mysqli_num_rows($ownBlogResult) > 0) {
                        $error = "Error! You can't comment on your own blog!";
                    } else {
                        // Author can post comment
                        $postCommQ = "INSERT INTO Comments(sentiment, description, cdate, blogid, author) VALUES ('$mySentiment','$myComment',CURDATE(),'$myChosenID','$myPostUser')";
                        $postCommresult = mysqli_query($db,$postCommQ);
                        if($postCommresult) {
                            $error = "Your comment has been posted!";
                        }
                    }


                }
            }
        }

        $commQuery = "";
    }

    // if "Follow User" btn is pressed
    if (isset($_POST['follow_submit'])) {
        $followError = '';
        $myChosenUser = mysqli_real_escape_string($db,$_POST['user_name']);
        // get session user
        $myPostUser = $_SESSION['login_user'];
        // Check if already following user
        $followQ = "SELECT * FROM Follows WHERE leader='$myChosenUser' AND follower='$myPostUser' ";
        $followQResult = mysqli_query($db,$followQ);

        if(mysqli_num_rows($followQResult) > 0) { // author is already followed
            $followError = "Error! You're already following this person!";
        } else { // author is not followed
            if($myChosenUser == $myPostUser) {
                $followError = "Error! You cannot follow yourself!";
            }
            else {
                $insertFollow = "INSERT INTO Follows(leader, follower) VALUES ('$myChosenUser', '$myPostUser')";
                $insertFollowResult = mysqli_query($db,$insertFollow);
                if($insertFollowResult) {
                    $followError = "Request completed!";
                }
            }
        }

    }

    // NUMBER 1
    if (isset($_POST['button2'])) {
    $tagX = mysqli_real_escape_string($db, $_POST['tagx']);
    $tagY = mysqli_real_escape_string($db, $_POST['tagy']);
    $sqlPart2 = "SELECT blogid FROM blogs WHERE postuser
In (SELECT postuser FROM blogs GROUP BY postuser HAVING COUNT(*) > 1)";

//$test2 = (("SELECT blogid FROM blogstags WHERE tag = '$tagX'") AND ("SELECT blogid FROM blogstags WHERE tag = '$tagY'"));
$test2 = "SELECT blogid FROM blogstags WHERE tag = '$tagX' OR tag = '$tagY'";
//$test3 = "SELECT blogid FROM blogstags WHERE tag = '$tagY'";

$result = $db->query($test2);
//$result = mysqli_query($test2, $test3);
//print_r($result);
$usersList = "";

     if(mysqli_num_rows($result) > 0) {

     $usersList .= "<br>";
     $usersList .= "<table border='1'>";
     $usersList .= "<td>Blog ID </td><td>Subject </td><td>Description</td><td>postuser</td><td>pdate</td>";
     while ($row = mysqli_fetch_assoc($result)) { 
        print_r($row);
         $usersList .= "<tr>";
         foreach ($row as $value) { // I you want you can right this line like this: foreach($row as $value) {
             //print_r($value);
             $blogQuery = "SELECT blogid,subject,description,postuser,pdate FROM blogs WHERE blogid = '$value'";
             $BlogResult = $db->query($blogQuery);
             $blogRow = mysqli_fetch_array($BlogResult,MYSQLI_ASSOC);

             $blogID =$blogRow['blogid'];
             $blogSub = $blogRow['subject'];
             $blogDes = $blogRow['description'];
             $blogPostuser = $blogRow['postuser'];
             $blogdate = $blogRow['pdate'];


             $usersList .= "<td>" . $blogID . "</td><td>" . $blogSub . "</td> <td>" . $blogDes . "</td><td>". $blogPostuser . "</td><td>".$blogdate."</td>";
             
         }
         $usersList .= "</tr>";
     }
     $usersList .= "</table>";

       } else {
           $usersList = "No blogs matched the tag!";
       }
   }

    // NUMBER 2
    if (isset($_POST['button3'])) {
        $userX = mysqli_real_escape_string($db, $_POST['userx']);
        $sqlComment = "SELECT blogid FROM comments WHERE sentiment = 'positive'";


        $result = $db->query($sqlComment);

        $usersList = "";

         if(mysqli_num_rows($result) > 0) {

        $usersList .= "<br>";
         $usersList .= "<table border='1'>";
         $usersList .= "<th>Blog ID </th><th> User who made the Post </th>";
         while ($row = mysqli_fetch_assoc($result)) { // Important line !!! Check summary get row on array ..
             $usersList .= "<tr>";
             foreach ($row as $value) {
                 $blogQuery = "SELECT blogid, postuser,pdate FROM blogs WHERE blogid = '$value' AND postuser = '$userX'";
                 $BlogResult = $db->query($blogQuery);
                 $blogRow = mysqli_fetch_array($BlogResult,MYSQLI_ASSOC);

                 $blogID =$blogRow['blogid'];
                 $blogPostuser = $blogRow['postuser'];


                 $usersList .= "<td>" . $blogID . "</td><td>". $blogPostuser . "</td>";
                  
             }
             $usersList .= "</tr>";
         }
         $usersList .= "</table>";

         } else {
         $usersList = "The user doesn't have any positive blogs!";
         }
    }

    // NUMBER 3
    if (isset($_POST['button4'])) {

        $sqlPart4 = "SELECT   postuser,
        COUNT(postuser) AS `value_occurrence`
        FROM blogs
        Where pdate In(
        Select pdate
        FROM blogs
        where pdate = '2022-05-09'
        )
        GROUP BY postuser
        ORDER BY `value_occurrence` DESC
        LIMIT 2;";

        $result = $db->query($sqlPart4);

        $usersList = "";

              if(mysqli_num_rows($result) > 0) {

              $usersList .= "<br>";
              $usersList .= "<table border='1'>";
              $usersList .= "<th>blogid </th><th>postuser</th>";
              while ($row = mysqli_fetch_assoc($result)) { // Important line !!! Check summary get row on array ..
                  $usersList .= "<tr>";
                  foreach ($row as $value) { // I you want you can right this line like this: foreach($row as $value) {
                      $blogQuery = "SELECT blogid, postuser FROM blogs WHERE postuser = '$value'";
                      $BlogResult = $db->query($blogQuery);
                      $blogRow = mysqli_fetch_array($BlogResult,MYSQLI_ASSOC);
                      $blogID =$blogRow['blogid'];
                      $blogPostuser = $blogRow['postuser'];

                      $usersList .= "<td>" . $blogID . "</td><td>". $blogPostuser . "</td>";
                      
                  }
                  $usersList .= "</tr>";
              }
              $usersList .= "</table>";

              } else {
              $usersList = "No blogs matched the tag!";

        }
    }

    // NUMBER 4
    if(isset($_POST['button5'])){
        $followerX = mysqli_real_escape_string($db, $_POST['followerx']);
        $followerY = mysqli_real_escape_string($db, $_POST['followery']);
        $leaderArray = array();

        $folXsql = "SELECT leader FROM Follows WHERE follower = '$followerX'";
        $folYsql = "SELECT leader FROM Follows WHERE follower = '$followerY'";

        $XsqlResult = mysqli_query($db,$folXsql);
        $YsqlResult = mysqli_query($db,$folYsql);

        $usersList = "";
        $usersList .= "<table border='1'>";

        if((mysqli_num_rows($XsqlResult) > 0) and (mysqli_num_rows($YsqlResult) > 0)){
            $leaderArray = array();
            while($rowx = mysqli_fetch_assoc($XsqlResult)) {
                while($rowy = mysqli_fetch_assoc($YsqlResult)) {
                    $leaderArray = array_intersect($rowx, $rowy);
                }
            }


            if(count($leaderArray) > 0){
                $usersList .= "<tr><td>" . $followerX . " and " . $followerY . " follow:</td></tr>";
                //$usersList .= "<p>" . $followerX . " and " . $followerY . "follow:</p><br />";
                foreach($leaderArray as $leaderElement){
                    $usersList .= "<tr><td>" . $leaderElement . "</td></tr>";
                    //$usersList .= "<p>" . $leaderElement . "</p><br />";
                }
            }
            else{
                $usersList .= "<tr><td>" . $followerX . " and " . $followerY . " do not follow any of the same people.</td></tr>";
                //$usersList .= "<p>" . $followerX . "and" . $followerY . "do not follow any of the same people.</p><br />";
            }
        }
        else if((mysqli_num_rows($XsqlResult) < 1) or (mysqli_num_rows($YsqlResult) < 1)){
            $usersList .= "<tr><td>" . $followerX . " and " . $followerY . " do not follow the same people.</td></tr>";
            //$usersList .= "<p>" . $followerX . "and" . $followerY . "do not follow the same people.</p><br />";
        }
        $usersList .= "</table>";
    }

    // NUMBER 5
    if(isset($_POST['button6'])){
        $noBlogsSql = "SELECT username FROM Users WHERE username NOT IN (SELECT postuser FROM blogs)";
        $blogsSqlResult = $db->query($noBlogsSql);
        $usersList = "";
        $usersList .= "<br><table border='1'>";
        if(mysqli_num_rows($blogsSqlResult) < 1){
            $usersList .= "<th>Every user has posted a blog</th>";
        }
        else{
            $usersList .= "<th>Users that have not posted a blog:</th>";

            while($row = mysqli_fetch_assoc($blogsSqlResult)) {
                foreach($row as $user){
                    $usersList .= "<tr><td>" . $user . "</td></tr>";
                }
            }
        }
        $usersList .= "</table>";
    }

    // NUMBER 6
    if(isset($_POST['button7'])){
        $noCommentsSql = "SELECT username FROM Users WHERE username NOT IN (SELECT author FROM comments)";

        $commentsSqlResult = mysqli_query($db,$noCommentsSql);
        $usersList = "";
        $usersList .= "<table border='1'>";

        if(mysqli_num_rows($commentsSqlResult) < 1){
            $usersList .= "<th>Every user has posted a comment</th>";
        }
        else{
            $usersList .= "<th>Users that have not posted a comment:</th>";
            while($row = mysqli_fetch_assoc($commentsSqlResult)) {
            foreach($row as $user){
                $usersList .= "<tr><td>" . $user . "</td></tr>";
                //print_r($row);
            }
        }
        }
    }

    // if "Number 7" btn is pressed - users who ONLY posted neg comments
    if (isset($_POST['num8'])) {
        $usersList = '';
        // Make NegUsersArray
        $NegUsersArr = array();
        // FIND ALL USERS
        $num8_FindUsers = "SELECT username FROM Users";
        $num8_FindUsersResult = mysqli_query($db,$num8_FindUsers);
        if(mysqli_num_rows($num8_FindUsersResult) > 0) { // more than one user
            while ($num8_UserRow = mysqli_fetch_assoc($num8_FindUsersResult)) {
                foreach ($num8_UserRow as $thisUser) {
                    //$usersList .= $thisUser . '<br>';

                    // Find sentiments from each comment by user
                    $num8_FindSentiments = "SELECT sentiment FROM Comments WHERE author='$thisUser'";
                    $num8_FindSentimentsResult = mysqli_query($db,$num8_FindSentiments);

                    if(mysqli_num_rows($num8_FindSentimentsResult) > 0) {
                        // bool var for confirming whether each comment is negative
                        $allNeg = 1;

                        while ($num8_sentiRow = mysqli_fetch_assoc($num8_FindSentimentsResult)) {
                            // Check each result
                            foreach ($num8_sentiRow as $thisSenti) {

                                if($thisSenti == 'Positive') { // if one is positive
                                    //$usersList .= $thisSenti . "by " . $thisUser . "<br>";
                                    $allNeg = 0;
                                }
                            }

                        } // end of sentiRow while
                        if($allNeg == 1) {
                            array_push($NegUsersArr, $thisUser);
                        }

                    } // end of if checking num_rows of sentiment

                } // end of foreach user
            } // end of userRow while
            // check if array was filled or not
            if(empty($NegUsersArr)) {
                $usersList = "No Results Found!!";
            } else {
                $usersList .= "<table border='1'> <tr> <th>Users who only post negative</th> </tr>";
                foreach($NegUsersArr as $user) {
                    $usersList .= "<tr><td>". $user . "</td></tr>";
                }
                $usersList .= "</table>";
            }

        } // end of more than one user if-statemnt
        else { // no users
            $usersList = "No Users Found!";
        }
    }
    //Number 8
    if (isset($_POST['num9'])) {
        $usersList = '';
        // Make NegUsersArray
        $LikedUsersArr = array();
        // FIND ALL USERS
        $num9_FindUsers = "SELECT username FROM Users";
        $num9_FindUsersResult = mysqli_query($db,$num9_FindUsers);
        if(mysqli_num_rows($num9_FindUsersResult) > 0) { // more than zero user
            while ($num9_UserRow = mysqli_fetch_assoc($num9_FindUsersResult)) {
                foreach ($num9_UserRow as $thisUser) {
                    //$usersList .= $thisUser . '<br>';

                    $num9_FindBlogs = "SELECT blogid FROM blogs WHERE postuser = '$thisUser'";
                    $num9_FindBlogsResult = mysqli_query($db,$num9_FindBlogs);

                    if(mysqli_num_rows($num9_FindBlogsResult) > 0) {
                        $noNegative = TRUE;
                        while($num9_blogRow = mysqli_fetch_assoc($num9_FindBlogsResult)) {
                            foreach ($num9_blogRow as $thisBlog) {
                                // Find sentiments from each comment by user
                                $num9_FindSentiments = "SELECT sentiment FROM Comments WHERE blogid='$thisBlog'";
                                $num9_FindSentimentsResult = mysqli_query($db,$num9_FindSentiments);

                                if(mysqli_num_rows($num9_FindSentimentsResult) > 0) { // comments were posted in blog
                                    // bool var for confirming all comments given are not negative

                                    while ($num9_sentiRow = mysqli_fetch_assoc($num9_FindSentimentsResult)) {
                                        // Check each result
                                        foreach ($num9_sentiRow as $thisSenti) {

                                            if($thisSenti == 'Negative') { // if one is positive
                                                //$usersList .= $thisSenti . "by " . $thisUser . "<br>";
                                                $noNegative = FALSE;
                                            }
                                        }
                                    } // end of sentiRow while
                                }
                            } // end of for each blog
                        } // end of blogRow while
                        if($noNegative == TRUE) {
                            array_push($LikedUsersArr, $thisUser);
                        }
                    } // end of if checking num of blogs
                } // end of for each user
            } // end of userRow while
            // check if array was filled or not
            if(empty($LikedUsersArr)) {
                $usersList = "No Results Found!!";
            } else {
                $usersList .= "<table border='1'> <tr> <th>Liked Users</th> </tr>";
                foreach($LikedUsersArr as $user) {
                    $usersList .= "<tr><td>". $user . "</td></tr>";
                }
                $usersList .= "</table>";
            }
        } // end of if checking num of users
    }

   
    //test button 9
    if(isset($_POST['button9'])){
        $sqlHobby = 
        "SELECT hobbies, group_concat(username order by username) as persons
        from
        (
        select username, group_concat(hobby order by hobby) as hobbies
        from tbl_hobbies
        group by username
        ) persons
        group by hobbies
        having count(*) > 1
        order by hobbies";
     

        $result = $db->query($sqlHobby);

        $usersList = "";

        if(mysqli_num_rows($result) > 0) {

        $usersList .= "<br>";
        $usersList .= "<table border='1'>
        <tr><th> Users </th><th> Hobby </th><tr>";
        while ($row = mysqli_fetch_assoc($result)) { // Important line !!! Check summary get row on array ..
            foreach($row as $value) {

                $usersList .= "<table border='1'>
                <tr><td>" . $value . "</td>";
            $usersList .= "";
        $usersList .= "</table>";
                }
            }
        }
    }
        } else {
        $usersList = "No user has any hobbies in common!";
        }

    
       

    
?>

<html>

   <head>
      <title>Recent Blogs</title>
      <link href="style.css" rel="stylesheet" type="text/css">
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
      <style>
table, th, td {
    border: 1px solid rgb(226, 6, 6);
}
th, td {
  padding: 15px;
}
</style>
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
       <div class="content">
      <h2>View Blogs by Tag</h2>
      <div style = "margin:30px">

               <form action = "" method = "post">


                  <label>Search by Tags:</label><input type = "text" name = "searchtags" class = "box" size = "50" /><br/><br />


                  <input type = "submit" name = "submit" value = " Submit "/>
                  <!-- <input type="button" onclick="window.location.href = 'signup.php';" value="Sign Up"/><br />    -->

               </form>

        </div>
        <div id="BlogList" style="margin:30px">
                <?php echo ((isset($blogs) && $blogs != '') ? $blogs : ''); ?>
        </div>
        <table>
            <tr>
                <td>
                    <h2>Comment on a Blog</h2>
                    <div id="commentForm" style = "margin:30px; width: 250px;">
                        <form action = "" method = "post">
                           <label>Blog ID: </label> <br>
                           <input pattern="^[1-9].*" type = "text" size = "30" placeholder=" (i.e. 24)" name = "blog_ID" required class = "box"/><br />
                           <label>Comment: </label> <br>
                           <input pattern="^[a-zA-Z1-9].*" type = "text" size = "30" placeholder=" Write your comment here..." name = "comment_des" required class = "box" /><br/>
                           <label>Rating: </label> <br>
                           <select name="commSentiment">
                               <option value="Positive" name="sentiment">Positive 👍</option>
                               <option value="Negative" name="sentiment">Negative 👎</option>
                           </select> <br> <br>
                           <span style="font-size:20px"><strong><?php echo ((isset($error) && $error != '') ? $error : ''); ?> </strong></span> <br>
                           <input id="btn" type = "submit" name="comment_submit" value = " Post Comment "/>
                        </form>
                    </div>
                </td>
                <td>
                    <h2>Follow a User</h2>
                    <div id="followForm" style = "margin:30px; width: 250px;">
                        <form action = "" method = "post">
                           <label>Username: </label> <br>
                           <input pattern="^[a-zA-Z1-9].*" type = "text" size = "30" placeholder=" (i.e. johndoe)" name = "user_name" required class = "box"/><br />
                           <span style="font-size:20px"><strong><?php echo ((isset($followError) && $followError != '') ? $followError : ''); ?> </strong></span> <br>
                           <input id="btn" type = "submit" name="follow_submit" value = " Follow User "/>
                        </form>
                    </div>
                </td>
            </tr>
        </table>


        <h2>Queries</h2>
        <div id="find_users" style = "margin:30px; width: 250px;">
        <table>
            <tr>

                <form action = "" method = "post">
                <input type="submit" name="button2" value="1. List the users who post at least two blogs, one has a tag of “X,” and another has a tag of “Y”"/><br/>
                <label>Input tag X:</label><input type = "text" name = "tagx" class = "box" size = "50" /><br/><br/>
                <label>Input tag Y:</label><input type = "text" name = "tagy" class = "box" size = "50" /><br/><br/>
                </form>
                <!-- <input type = "text" name = "search2" class = "box" size = "10" /><br/><br /> -->
            </tr>
            <tr>
                <form action = "" method = "post">
                <input type="submit" name="button3" value="2. List all the blogs of user X, such that all the comments are positive for these blogs. "/><br/>
                <label>Input user X:</label><input type = "text" name = "userx" class = "box" size = "50" /><br/><br/>
                </form>
            </tr>
            <tr>
                <!-- <input type = "text" name = "search4" class = "box" size = "10" /><br/><br /> -->
                <form action = "" method = "post">
                <input type="submit" name="button4" value="3. List the users who posted the most number of blogs on 5/1/2022; if there is a tie,list all the users who have a tie."/><br/>
                </form>
            </tr>
            <tr>
                <!-- <input type = "text" name = "search4" class = "box" size = "10" /><br/><br /> -->
                <form action = "" method = "post">
                <input type="submit" name="button5" value="4. List the users who are followed by both X and Y. Usernames X and Y are inputs from the user"/><br/>
                <label>Search first follower:</label><input type = "text" name = "followerx" class = "box" size = "50" /><br/><br/>
                <label>Search second follower:</label><input type = "text" name = "followery" class = "box" size = "50" /><br/><br/>
                </form>
            </tr>
            <tr>
                <!-- <input type = "text" name = "search4" class = "box" size = "10" /><br/><br /> -->
                <form action = "" method = "post">
                <input type="submit" name="button6" value="5. Display all the users who never posted a blog"/><br/>
                </form>
            </tr>
            <tr>
                <!-- <input type = "text" name = "search4" class = "box" size = "10" /><br/><br /> -->
                <form action = "" method = "post">
                <input type="submit" name="button7" value="6. Display all the users who never posted a comment"/><br/>
                </form>
            </tr>
            <tr>
                <!-- Number 7 -->
                <form action = "" method = "post">
                    <input id="" type="submit" name="num8" value=" 7. Users who only posted negative comments "/>
                </form>
            </tr>
            <tr>
                <!-- Number 8 -->
                <form action = "" method = "post">
                    <input id="" type="submit" name="num9" value=" 8. Users who never recieved negative comments "/>
                </form>
            </tr>
            <tr>
                <!-- Number 9 -->
                <form action = "" method = "post">
                    <input id="" type="submit" name="button9" value=" 9. List a user pair (A, B) such that they have at least one common hobby. "/>
                </form>
            </tr>

        </table>

        </div>
        <div id="ShowUsers" style="margin:30px">
                <?php echo ((isset($usersList) && $usersList != '') ? $usersList : ''); ?>
        </div>
        </div>

   </body>

</html>
