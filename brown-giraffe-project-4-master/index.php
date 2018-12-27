<?php
//---------------------------------Includes/User Set Up-------------------------
include('includes/init.php');
include('includes/header.php');
//Check for user and initialize them
$current_user ="";
if(isset($_SESSION['current_user'])){
$current_user = $_SESSION['current_user'];
}else{ $current_user == NULL;}

//admin privileges
if($current_user != NULL){
$admin = admin_validate($current_user);
}else{
$admin = FALSE;
}

$current_page_id="index";


//---------------------------Gather Data For This Page--------------------------
//Gather content elements
$content = gather_content($db);

//----------------------Element Selects-----------------------------------------
//Select chosen element
if(isset($_POST["element_select"])){
  $element = filter_input(INPUT_POST, 'element_select', FILTER_SANITIZE_STRING);
  $selected_content = $element;
}else{
  $selected_content = "";
}

//------------------------------Element Protocols-----------------------------
//Process edit for selected element
if(isset($_POST["content_edit"])){
    $selected_content = $_POST['content_edit'];
    $updated_content = filter_input(INPUT_POST, 'edit', FILTER_SANITIZE_STRING);
    $sql = "UPDATE cms SET content = :content WHERE name = :element";
    $params =array(':content' => $updated_content, ':element'=>$selected_content);
    $results = exec_sql_query($db, $sql, $params);
    if($results){
      record_message("Edit successful!");
      $content = gather_content($db);
    }else{
      record_message("Edit failure. Please try again.");
    }
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <link rel="stylesheet" type="text/css" href="styles/all.css" media="all" />

  <title>Cayuga Strength and Conditioning </title>
</head>
<body>
  <?php
  if(count($messages) > 0){print_messages();}

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

  <!-- <h2>Fitness Tip of the Week</h2>-->

  <!-- <img id="banner" src = "images/lake.jpg"> -->

  <h2>Fitness Tip of the Week</h2> <!--consider putting this elsewhere-->
  <p id= "tip"><?php echo $content['fitness_tip']; ?></p>

    <?php if($admin){ ?>
      <div class="container1">
        <div class="flex1">
          <h2 class="flexinfo">Location</h2>
            <p class="homeinfo"><?php display_content($admin, $current_page_id, 'street_addr', $content);?></p>
            <p class="homeinfo"><?php display_content($admin, $current_page_id, 'city_state', $content);?> </p>
        </div>

        <div class="flex1">
          <h2 class="flexinfo">Hours of Operation</h2>
            <!--temporary-->
            <p class="homeinfo"><?php display_content($admin, $current_page_id, 'hours_of_operation', $content);?></p>
            <p class="homeinfo">Come Workout!</p>
        </div>
      </div>
    <?php } else { ?>
      <div class="container">
        <div class="flex">
          <h2 class="flexinfo">Location</h2>
            <p class="homeinfo"><?php display_content($admin, $current_page_id, 'street_addr', $content);?></p>
            <p class="homeinfo"><?php display_content($admin, $current_page_id, 'city_state', $content);?> </p>
        </div>

        <div class="flex">
          <h2 class="flexinfo">Hours of Operation</h2>
            <!--temporary-->
            <p class="homeinfo"><?php display_content($admin, $current_page_id, 'hours_of_operation', $content);?></p>
            <p class="homeinfo">Come Workout!</p>
        </div>
      </div>
    <?php } ?>

    <br/>

    <!-- <img id="mapimg" src="images/map.png"/> -->
    <div id="mapapi">
      <img src="images/map.png"/>
      <!--Google Map API-->
      <!-- <iframe
        width="600"
        height="450"
        frameborder="0" style="border:0"
        src="https://www.google.com/maps/embed/v1/place?key=AIzaSyCAiIL6kv0lekOo4LqetremrNFo1chRSfw
        &q=145+Yaple+Rd+Ithaca+NY+14850" allowfullscreen>
      </iframe> -->
    </div>

    <?php if($admin){ ?>
      <h2> Admin Terminal</h2>
      <?php content_edit_pane($current_page_id, $selected_content);?>
  <?php } ?>
<?php include('includes/footer.php');?>
</body>
</html>
