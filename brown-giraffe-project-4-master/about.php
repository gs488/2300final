<?php include('includes/init.php');



/* script */
//---------------------------------includes/user set up-------------------------
$current_page_id="about";

//Establish current user
$current_user = "";
if(isset($_SESSION['current_user'])){
$current_user = $_SESSION['current_user'];
}else{ $current_user==NULL;}

//Check for admin privileges
if($current_user != NULL){
$admin  = admin_validate($current_user);
}else{
$admin = FALSE;
}


include('includes/header.php');

//------------gather content for this page--------------------------------------
$content = gather_content($db);
$staff = gather_staff($db);


//----------------------Element and Staff selects-------------------------------
//Select an element
if(isset($_POST["element_select"])){
  $element = filter_input(INPUT_POST, 'element_select', FILTER_SANITIZE_STRING);
  $selected_content = $element;
}else{
  $selected_content = "";
}

//Select a staff member to edit
if(isset($_POST['staff_select'])){
  $selected_staff= filter_input(INPUT_POST, 'staff_select', FILTER_SANITIZE_STRING);
}else{$selected_staff = "";}

//----------------Element and Staff Edit, Add, Delete Protocols-----------------

//Process content edit
if(isset($_POST["content_edit"])){
    $selected_content = $_POST['content_edit'];
    $updated_content = filter_input(INPUT_POST, 'edit', FILTER_SANITIZE_STRING);
    $sql = "UPDATE cms SET content = :content WHERE name = :element";
    $params =array(':content' => $updated_content, ':element'=>$selected_content);
    $results = exec_sql_query($db, $sql, $params);
    if($results){
      record_message("Content edit successful!");
      $content = gather_content($db);
    }else{
      record_message("Content edit failed. Please try again.");
    }
}

//Process staff edit
if(isset($_POST['staff_edit']) && ($_POST['staff_edit'] != "staff_add")){
  $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
  $bio  = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
  $staff_picture = $_FILES["staff_picture"];
  $edit_staff = $_POST['staff_edit'];
    if($name != ""|| $bio != ""){
    $sql = "UPDATE staff SET ";
    $params =array();
    if($name != ""){
      $sql = $sql . "staff_name = :name";
      $params[':name'] = $name;
      if(isset($_POST['bio'])){
        $sql = $sql . ",staff_bio = :bio ";
        $params[':bio'] = $bio;
      }
    }else if($bio != ""){
      $sql = $sql . "staff_bio = :bio ";
      $params[':bio'] = $bio;
    }
    $sql = $sql."WHERE staff_name = :edit_staff";
    $params[':edit_staff'] = $edit_staff;
    if(exec_sql_query($db, $sql, $params)){
      //edit successful
    }else{
      //edit failed
    }

  }

  if($_FILES['staff_picture']['error'] == UPLOAD_ERR_OK ){
    //upload photo, update Db. set limiter
    $upload_name = trim(strtolower($name));
    $upload_name = str_replace(' ','_',$upload_name);
    $upload_basename = basename($_FILES["staff_picture"]["name"]);
    $ext = strtolower(pathinfo($upload_basename, PATHINFO_EXTENSION));
    $upload_path = UPLOAD_PICTURE_PATH . $upload_name. '.' . $ext;
    move_uploaded_file($staff_picture["tmp_name"], $upload_path);
    $sql = "UPDATE staff SET staff_picture_name = :upload_name, staff_picture_ext = :ext
               WHERE staff_name = :edit_staff";
    $params = array(':edit_staff'=>$edit_staff,
                    ':upload_name'=>$upload_name, ':ext'=>$ext);
    if(exec_sql_query($db, $sql, $params)){
      //TODO Success
    }else{
      //TODO insertion failure
    }
  }
  $staff = gather_staff($db);
  unset($_POST['name']);
  unset($_POST['bio']);
  unset($_FILES['staff_picture']);
}

//Performs staff add TODO make sure pic uplaod happens after
if(isset($_POST['staff_edit']) && ($_POST['staff_edit'] == "add_staff")){
  if(isset($_POST['name']) && isset($_POST['bio']) && isset($_FILES['staff_picture'])){
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $bio  = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
    $staff_picture  = $_FILES["staff_picture"];

    $db->beginTransaction();
    $sql = "INSERT INTO staff(staff_name,staff_picture_name,staff_picture_ext, staff_bio)
              VALUES(:name, :upload_name, :ext, :bio)";
    $params = array(':name'=>$name,
                    ':upload_name'=>$upload_name, ':ext'=>$ext,
                    ':bio'=>$bio);
    if(exec_sql_query($db, $sql, $params)){
      record_message("Staff addition successful!");

      //upload photo
      $upload_name = trim(strtolower($name));
      $upload_name = str_replace(' ','_',$upload_name);
      $upload_basename = basename($_FILES["staff_picture"]["name"]);
      $ext = strtolower(pathinfo($upload_basename, PATHINFO_EXTENSION));
      $upload_path = UPLOAD_PICTURE_PATH . $upload_name. '.' . $ext;

      if(move_uploaded_file($staff_picture["tmp_name"], $upload_path)){
        record_message("Staff picture upload successful!");
        $db->commit();
      }else{
        record_message("Staff picture upload failure. Please try again. ");
        $db->rollBack();
      }
    }else{
      record_message("Staff insertion into database unsuccessful. Try again!");
    }
  }else{
    record_message("Staff addition failure. Missing inputs.");
  }
  $db->rollBack();
  $staff = gather_staff($db);
  unset($_POST['name']);
  unset($_POST['bio']);
  unset($_FILES['staff_picture']);
}


//Process staff deletion
if(isset($_POST['staff_delete'])){
  $staff_name = $_POST['staff_delete'];

  $sql = "SELECT * FROM staff WHERE staff_name = $staff";
  $staff = exec_sql_query($db, $sql, $params =array());
  $upload_name = $staff['staff_picture_name'];
  $ext = $staff['staff_picture_ext'];

  //Delte from database
  $db->beginTransaction();
  $sql = "DELETE FROM staff WHERE staff_id = :id";
  $params = array(':id'=>$staff['id']);
  if(exec_sql_query($db, $sql, $params)){
    record_message("Staff deletion from database successful!");

    //unlink related photo
    $result = unlink(UPLOAD_ITEMS_PATH . $upload_name . '.'.$ext);
    if($result){
    $db->commit();
    record_message("Staff photo unlink successful!");
    }else{
    $db->rollBack();
    record_message("Staff photo unlink failure. Please try again.");
    }
   }else{
     record_message("Staff deletion from database failure. Please try again.");
   }
   $db->rollBack();
   $staff = gather_staff($db);
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

  <?php if($admin){ ?>
    <br id="log-inbreak1"/>
  <?php } else { ?>
    <br id="log-inbreak"/>
  <?php }?>

  <h1><?php echo $pages[$current_page_id];?></h1>

  <h2>Gym Philosophy</h2>
  <p id="philosophy"><?php echo $content['philosophy'];?></p>

    <h2 class="aboutus">Who We Are</h2>
      <?php display_staff($admin,$staff); ?>
    <br/>

    <h2 class="aboutus">Contact Us</h2>

      <p class="contactinfo">Email: <?php display_content($admin, $current_page_id, 'csac_email', $content); ?></p>
      <p class="contactinfo">Phone Number: <?php display_content($admin, $current_page_id, 'csac_phone', $content); ?></p>
      <p class="contactinfo">Facebook: <?php display_content($admin, $current_page_id, 'csac_facebook', $content); ?></p>

      <?php if($admin){ ?>
        <h2> Admin Terminal</h2>
        <?php
        content_edit_pane($current_page_id, $selected_content);
        staff_edit_pane($current_page_id,$selected_staff);
        ?>
      <?php } ?>
<?php include('includes/footer.php');?>
</body>
</html>
