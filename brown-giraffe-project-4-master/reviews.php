<?php

//---------------------------------includes/user set up-------------------------
include('includes/init.php');
include('includes/header.php');
$current_page_id="reviews";
$content = array('fitness_tip' => "");

//Establish current user
$current_user ="";
if(isset($_SESSION['current_user'])){
$current_user = $_SESSION['current_user'];
}else{ $current_user == NULL;}


//Check for admin privileges
if($current_user != NULL){
$admin  = admin_validate($current_user);
}else{
$admin = FALSE;
}

//------------gather content for this page--------------------------------------
//gather reviews
  $reviews = gather_reviews($db);

//---------------------------Review  Select-------------------------------------
//Select chosen review
  if(isset($_POST["review_select"])){
    $selected_review = $_POST["review_select"];
  }else{ $selected_review = 1;}

//----------------------Review Edit, Add, Delete Protocols------------------------
//Delete review
if(isset($_POST['review_delete'])){
 $review = $_POST['review_delete'];
  $sql = "DELETE FROM reviews WHERE reviews_id = :id";
  $params = array(':id' => $review);
  if(exec_sql_query($db, $sql, $params)){
    record_message("Review removal successful.");
  }else{
    record_message("Failed to remove review. Please try again.");
  }
  $reviews = gather_reviews($db);
}

// Insert new Review
if (isset($_POST["submit_insert"])) {
  $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
  $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
  $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
  if ($rating < 1 or $rating > 5) {
      $invalid_review = TRUE;
  } else {
    $invalid_review = FALSE;
  }
  if ($invalid_review) {
    record_message("Failed to add review. Invalid rating. Please try again.");
  } else {
    $sql = "INSERT INTO reviews (first_name, content, rating) VALUES (:first_name, :content, :rating)";
    $params = array(
          ':first_name' => $first_name,
          ':rating' => $rating,
          ':content' => $content
        );
    $result = exec_sql_query($db, $sql, $params);
    if ($result) {
      record_message("Your review has been record. Thank you!");
    } else {
      record_message("Failed to add review. Please try again.");
    }
  }
    $reviews = gather_reviews($db);
}


?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="stylesheet" type="text/css" href="styles/all.css" media="all" />

  <title>Cayuga Strength and Conditioning</title>
</head>

<body>
  
  <?php
  if(count($messages) > 0){print_messages();}
  ?>

  <?php


  //log in
  if($current_user == NULL){
  echo '<div id="log-in"><form id="login" method="post" action ="' . $current_page_id .'.php"> <fieldset>
       Username: <input type="email" name="login_email" required>
       <br/>
       Password: <input type="password" name="login_password" required>
       <button name="login" type="submit">Log In</button>
       </form></div>';
  }else{
  echo '<div id="log-out"><form id="logout" method="post" action ="' . $current_page_id .'.php"> <fieldset>
        Currently logged in as: ' .
        $current_user["first_name"] . ' ' . $current_user["last_name"] . '   ' .
        '<button name="logout" type="submit">Log Out</button>
         </form></div>';
  }
  echo '</li>';
  ?>

  <?php if($current_user){ ?>
    <br id="log-inbreak1"/>
  <?php } else { ?>
    <br id="log-inbreak"/>
  <?php }?>


  <h1><?php echo $pages[$current_page_id];?></h1>

    <!-- <h2 id="reviews">Reviews</h2> -->

    <?php
    display_reviews($admin,$reviews);
    ?>

    <h2>Add a review!</h2>

  <div id="reviewform">
    <div id="reviewflex">
    <form action="reviews.php" method="post">
       <ul>
         <li class="reviewli">
           <label>First Name:</label>
           <input type="text" name="first_name" required/>
         </li>
         <li class="reviewli">
           <label>Rating:</label>
           <input type="radio" name="rating" value="5" checked/>5
           <input type="radio" name="rating" value="4"/>4
           <input type="radio" name="rating" value="3"/>3
           <input type="radio" name="rating" value="2"/>2
           <input type="radio" name="rating" value="1"/>1
         </li>
         <li class="reviewli">
           <label>Comment:</label>
         </li>
         <li class="reviewli">
           <textarea name="content" cols="40" rows="5"></textarea>
         </li>
         <li class="reviewli">
           <button name="submit_insert" type="submit">Add Review</button>
         </li>
       </ul>
     </form>
   </div>
 </div>

      <?php if($admin){ ?>
        <h2> Admin Terminal</h2>


        <?php
        reviews_edit_pane($db, $selected_review);
        ?>
      <?php } ?>
<?php include('includes/footer.php');?>
</body>
</html>
