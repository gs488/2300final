<?php
session_start();
//--------------------------DATABASE FUNCTIONS----------------------------------
$db = open_or_init_sqlite_db("website.sqlite", "init/init.sql");
function exec_sql_query($db, $sql, $params = array()) {
  $query = $db->prepare($sql);
  if ($query and $query->execute($params)) {
    return $query;
  }
  return NULL;
}

// YOU MAY COPY & PASTE THIS FUNCTION WITHOUT ATTRIBUTION.
// open connection to database
function open_or_init_sqlite_db($db_filename, $init_sql_filename) {
  if (!file_exists($db_filename)) {
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db_init_sql = file_get_contents($init_sql_filename);
    if ($db_init_sql) {
      try {
        $result = $db->exec($db_init_sql);
        if ($result) {
          return $db;
        }
      } catch (PDOException $exception) {
        // If we had an error, then the DB did not initialize properly,
        // so let's delete it!
        unlink($db_filename);
        throw $exception;
      }
    }
  } else {
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  }
  return NULL;
}

//------------------------GLOBAL INITIALIZATION---------------------------------
$pages = array(
  "index" => "Home",
  "about" => "About Us",
  "calendar" => "Appointments",
  "reviews" => "Reviews",
  "merchandise" => "Merchandise",
  "account" => "Account",
  "signup" => "Sign Up"
);

//Initialize messages array
$messages = array();
//Constants
const MAX_FILE_SIZE = 10000000;
const UPLOAD_ITEMS_PATH = "uploads/items/";
const UPLOAD_PICTURE_PATH = "images/staff/";

//If website isnt deployed, use localhost as URL. This allows for page nav and
//testing in a local enviroment.
if(!defined('SITE_URL')) define('SITE_URL', 'localhost:8000');

//--------------------------------LOGIN/LOGOUT----------------------------------

function log_in($login_email, $login_password) {
   global $db;
   global $user_id;
     $sql = "SELECT * FROM accounts WHERE email = :email";
     $params = array(
       ':email' => $login_email
     );

     $records = exec_sql_query($db, $sql, $params)->fetchAll();

     if ($records) {
       // Username is UNIQUE, so there should only be 1 record.
       $login_account = $records[0];
       // Check password against hash in DB
       if (password_verify($login_password,$login_account['password'])){
        // Success, we are logged in.
         $sql = 'SELECT * FROM accounts WHERE email = :email';
         $accounts = exec_sql_query($db, $sql, $params = array(':email' => $login_email))->fetchAll();
         $_SESSION['current_user'] = $accounts[0];
         record_message("Log in successful!");
        return TRUE;

      } else {
        echo ('Log in unsuccessful.');
      }
    }
    record_message("Log in unsuccessful, please try again.");
   return NULL;
 }


function log_out(){
 global $db;
 unset($_SESSION['current_user']);
 record_message("Log out successful!");
 session_destroy();
}


function check_login(){

 if(isset($_SESSION['current_user'])){
  return $_SESSION['current_user'];
 }
 return NULL;
}




function admin_validate($user){
  if($user['email']== "csaccalendar@gmail.com") return TRUE;
  return FALSE;
}

//----------------------------------------------------CONTENT----------------------------------
//Retrieves website content from content management system
function gather_content($db){
$sql = "SELECT * FROM cms";
$query = exec_sql_query($db, $sql, $params=array());
$content = array();
foreach ($query as $element) {
  $content[$element['name']] = $element['content'];
}

return $content;
}

//Display content with admin select
function display_content($admin_validation,$current_page_id,
                         $element_name, $element_array){
  echo $element_array["$element_name"];
  if($admin_validation){
    echo '<div class="adminselect"><form method = "post" action = "' .$current_page_id . '.php">
          <button type = "submit" name = "element_select" value ="'.$element_name.'">SELECT</button>
          </form></div>';
  }
}


//Display admin terminal content edit pane
function content_edit_pane($current_page_id, $selected_content){
echo '<div id="admincontentedit"><form method = "post" action = "' . $current_page_id .'.php"><fieldset>
      <legend>Selected Content</legend><ul>';
echo '<li class ="form">Element: '.$selected_content .'</li>';
echo '<li class ="form"><input type = "text" name = "edit">
      <button type = "submit" name = "content_edit" value ="'
      . $selected_content .'" >Edit</button></li></ul></form></div>';

}
//-----------------------------------------ITEMS--------------------------------

function gather_items($db){
  $sql = "SELECT * FROM items";
  $query = exec_sql_query($db, $sql, $params=array())->fetchAll();
  if($query == NULL){
    return array();
  }else{
    return $query;
  }
}

//Display items with admin select
function display_items($admin_validation, $items){
  if(count($items) == 0){
    echo '<strong id="noitems"> No items to display </strong>';
  }else{
    if($admin_validation){
      foreach($items as $item){
        echo '<figure><img id="adminmerchimg" src= "' . UPLOAD_ITEMS_PATH . $item['file_name'] . '.' . $item['file_ext'] .'"' . '>
              <figcaption>'. $item['item_name'].': $'. $item['price']. '</figcaption></figure>';
              if($admin_validation){
                echo '<form method = "post" action = "merchandise.php">
                     <button type = "submit" name = "item_select" value ="'. $item["items_id"] .'">SELECT</button></form>';
              }
        }
    }else{
      foreach($items as $item){
        echo '<figure><img id="merchimg" src= "' . UPLOAD_ITEMS_PATH . $item['file_name'] . '.' . $item['file_ext'] .'"' . '>
              <figcaption>'. $item['item_name'].': $'. $item['price']. '</figcaption></figure>';
              if($admin_validation){
                echo '<form method = "post" action = "merchandise.php">
                     <button type = "submit" name = "item_select" value ="'. $item["items_id"] .'">SELECT</button></form>';
              }
        }
    }
  }
}

//Display admin terminal merch edit pane
function merch_edit_pane($selected_item){
  echo '<div id="adminmerch"><form method = "post" action = "merchandise.php" enctype="multipart/form-data"><fieldset>
        <legend>Selected Item</legend><ul>';
  echo '<li class ="form">Item Name: '.$selected_item['item_name'] .
        '<input type = "text" name = "item_name"</li>
        <li class ="form">Price:'. $selected_item['price'] .
        '<input type = "number" step =".01" name = "price"></li>
        <input type="hidden" name="MAX_FILE_SIZE" value="'. MAX_FILE_SIZE .'" />
        <li class ="form">Picture: <input type = "file" name = "item_picture" accept = "image/*"></li>
        <input type = "hidden" name = "items_id" value ="'.$selected_item['items_id'].'">
        <button type = "submit" name = "edit_item">Edit</button>
        <button type = "submit" name = "add_item"> Add</button>
        <button type = "submit" name = "item_delete" value ="delete_item" >Delete</button></li></li></ul></form></div>';

}
//---------------------------------REVIEWS--------------------------------------
function gather_reviews($db){
  $sql = "SELECT * FROM reviews";
  $query = exec_sql_query($db, $sql, $params = array());
  if($query == NULL){
    return array();
  }else{
    return $query;
  }
}


function display_reviews($admin_validation, $reviews){
  if(count($reviews) == 0){
    echo '<strong> No items to display </strong>';
  }else{
  foreach($reviews as $review){
    echo '<h3 id="reviewer">'. $review['first_name'] . '</h3>';
    $rating = intval( $review["rating"] );
    $stars = "";
     for ($i = 1; $i <= 5; $i++) {
       if ($i <= $rating) {
         $stars = $stars . "★";
       } else {
         $stars = $stars . "☆";
       }
     }
     echo '<div id="stars">'.$stars.'</div>';
    echo '<p id=review>' . $review['content'] . '</p>';
    if($admin_validation){
      echo '<div class="adminselect"><form method = "post" action = "reviews.php">
            <button type = "submit" name = "review_select"
            value ="'. $review['reviews_id'] .'">SELECT</button></form></div>';
    }
  }
}
}

function reviews_edit_pane($db, $selected_review){
  $sql = "SELECT * FROM reviews WHERE reviews_id = :id";
  $params = array(':id'=>$selected_review);
  $query = exec_sql_query($db, $sql, $params)->fetchAll();
  $review = $query[0];
  echo '<div id="adminreviews"><form method = "post" action = "reviews.php"><fieldset>
        <legend>Select Review</legend><ul>';
  echo '<li class ="form">Review by: '.$review['first_name'] .'</li>
        <button type = "submit" name = "review_delete" value ="'.
         $selected_review.'">DELETE</button></li></ul></form></div>';
}

//---------------------------------STAFF--------------------------------------
function gather_staff($db){
  $sql = "SELECT * FROM staff";
  $query = exec_sql_query($db, $sql, $params = array())->fetchAll();
  if($query == NULL){
    return array();
  }else{return $query;}
}

function display_staff($admin_validation, $staff){
  if(count($staff) ==0){
   echo "No staff to display";
  }else{
    foreach ($staff as $member) {
      echo '<p class="staff">' . $member['staff_name'] .'</p>';
      echo '<div id="bios"><div class="staffstuff"><img class="staffpic" src = "images/staff/' . $member['staff_picture_name'].'.'.$member['staff_picture_ext'].'"></div>';
      echo '<div class="staffstuff"><p class="bio">' . $member['staff_bio'] .'</p></div></div>';
      if($admin_validation){
        echo '<div class="adminselect"><form method = "post" action = "about.php">
              <button type = "submit" name = "staff_select" value ="'
              .$member['staff_name'].'">SELECT</button></form></div>';
      }
    }
  }
}

//Display admin terminal staff edit pane
function staff_edit_pane($current_page_id, $staff){
echo '<div id="adminstaffedit"><form method = "post" id = "staff_edit_pane" action = "' . $current_page_id .'.php"
      enctype="multipart/form-data"><fieldset>
      <legend>Selected Staff</legend><ul>';
echo '<li class ="form">Staff Member: '.$staff .'</li>
      <li class ="form">Name: <input type = "text" name = "name"></li>
      <li class ="form">Bio: <textarea form = "staff_edit_pane" name = "bio"></textarea></li>
      <input type="hidden" name="MAX_FILE_SIZE" value="'. MAX_FILE_SIZE .'" />
      <li class ="form">Picture: <input type = "file" name = "staff_picture" accept = "image/*"></li>
      <button type = "submit" name = "staff_edit" value ="'
      . $staff .'" >EDIT</button></li>
      <button type = "submit" name = "staff_edit" value ="add_staff">
      ADD</button>
      <button type = "submit" name = "staff_delete" value ="'
      . $staff .'" >DELETE</button></li></li></ul></form></div>';
}
//------------------------------Appointments------------------------------------
function gather_appointments($db, $current_user){
  $sql = "SELECT * FROM appointments WHERE user_id = :id";
  $params = array(':id'=>$current_user['accounts_id']);
  $query = exec_sql_query($db,$sql, $params);
  if($query){
    return $query;
  }
  return NULL;
}

function display_appointments($db, $appointments){
  $count = 1;
  if($appointments){
    // echo '<ul id="fixfloat">';
    foreach($appointments as $appointment){
          echo '<p id="appointmentlist">Appointment '.$count.":" . ' '.$appointment['details']
          . ' '. $appointment['date_time']. '</p>
          <div id="deletebttn"><form method = "post" action = "calendar.php">
          <button type = "submit" name = "app_delete" value = "' .$appointment['appointments_id'].'">
          Delete Appointment</button></form></div>';
          $count = $count + 1;
    }
    echo '</div>';
  }else{
    echo '<strong>No appointments to display</strong>';
  }
}
//--------------------------------MISC------------------------------------------
$messages = array();

// Record a message to display to the user.
function record_message($message) {
  global $messages;
  array_push($messages, $message);
}

// Write out any messages to the user.
function print_messages() {
  global $messages;
  echo "<p id='loginfail'><strong>";
  while(count($messages)>0){
    echo htmlspecialchars(array_pop($messages)) ." \n";
  }
  echo "</strong></p>";
}
//--------------------------------SCRIPT----------------------------------------

// open connection to database

/*check and see if user is logged in */
if(isset($_POST['login'])){
 $login_email= filter_input(INPUT_POST, 'login_email', FILTER_VALIDATE_EMAIL);
 $login_email = trim($login_email);
 $login_password = filter_input(INPUT_POST, 'login_password', FILTER_SANITIZE_STRING);
 log_in($login_email,   $login_password);

}

if(isset($_POST['logout'])){
 log_out();
}



?>
