<?php

//---------------------------------includes/user set up-------------------------
$current_page_id="account";

include('includes/init.php');
include('includes/header.php');
//Check for current user and initialize them
$current_user ="";
if(isset($_SESSION['current_user'])){
$current_user = $_SESSION['current_user'];
}else{
$current_user==NULL;
header("location: http://".SITE_URL.'/signup.php');
}




//-------------------------Account Edit Protocols-------------------------------

//Process account edits
if(isset($_POST['edit_account'])){
  //keep a record of the users current email and password settings

  $original_email = $current_user['email'];
  $original_password = $current_user['password'];

  if($_POST['edit_account'] == "edit_email"){
  // User wants to edit their email adddress
     $edit_email = filter_input(INPUT_POST, 'edit_email', FILTER_VALIDATE_EMAIL);

     if($edit_email){
       //Entered email is valid
       $sql = "SELECT * FROM accounts WHERE email = :email";
       $params = array(':email' => $edit_email);
       $results = exec_sql_query($db, $sql, $params)->fetchAll();
       if(count($results) > 0){
         //account already exists
         record_message("An account with this email already exists! Try again. ");
       }else{
         //Update good to go
         $sql = "UPDATE accounts SET email = :new_email WHERE email = :old_email";
         $params = array(':new_email'=> $edit_email, ':old_email'=>$current_user['email']);
         $results = exec_sql_query($db, $sql, $params);
         if($results){
          record_message("Changes recorded.Your new email is $edit_email. Your account will be updated on next log in.");
         }else{

          record_message("Oops! Changes not recorded. Please try again. ");
         }
       }

     }

   }else if($_POST['edit_account'] == "edit_password"){
       $edit_password = trim(filter_input(INPUT_POST, 'edit_password', FILTER_SANITIZE_STRING));
       $edit_password_confirm= trim(filter_input(INPUT_POST, 'edit_password_confirm', FILTER_SANITIZE_STRING));
       if($edit_password == $edit_password_confirm){
         //password confirmed
         $hashed_password = password_hash($edit_password, PASSWORD_DEFAULT);

         $sql = "UPDATE accounts SET password = :edit_password WHERE email = :email";
         $params = array(':edit_password'=> $hashed_password, ':email'=>$current_user['email']);
         $results = exec_sql_query($db, $sql, $params);
         if($results){
          record_message("Password modified. ");
         }else{
          record_message("Oops! Changes not recorded. Please try again. ");
         }
       }else{
          record_message("Passwords do not match! Try again.");
       }

     }else if($_POST['edit_account'] == "edit_first"){
       $edit_first  = trim(filter_input(INPUT_POST, 'edit_first', FILTER_SANITIZE_STRING));
       $sql = "UPDATE accounts SET first_name = :edit_first WHERE email = :email";
       $params = array(':edit_first'=> $edit_first, ':email'=>$current_user['email']);
       $results = exec_sql_query($db, $sql, $params);
       if($results){
         record_message("Changes recorded. Your account will be updated on next log in.");
        }else{
        record_message("Oops! Changes not recorded. Please try again. ");
        }
     }else{

       //edit last name
       $edit_last  = trim(filter_input(INPUT_POST, 'edit_last', FILTER_SANITIZE_STRING));
       $sql = "UPDATE accounts SET last_name = :edit_last WHERE email = :email";
       $params = array(':edit_last'=> $edit_last, ':email'=>$current_user['email']);
       $results = exec_sql_query($db, $sql, $params);
       if($results){

         record_message("Changes recorded. Your account will be updated on next log in.");
        }else{
          record_message("Oops! Changes not recorded. Please try again. ");
        }
     }

  }//End
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
  echo '<div id="log-out"><form id="logout" method="post" action ="' . $current_page_id .'.php"> <fieldset>
        Currently logged in as: ' .
        $current_user["first_name"] . ' ' . $current_user["last_name"] . '   ' .
        '<button name="logout" type="submit">Log Out</button>
         </form></div>';
  ?>

  <?php if($current_user){ ?>
    <br id="log-inbreak1"/>
  <?php } else { ?>
    <br id="log-inbreak"/>
  <?php }?>


  <h1><?php echo $pages[$current_page_id];?></h1>
    <?php if(count($messages)>0) print_messages();?>
    <h2>Logged in as <?php echo $current_user['first_name'] . ' ' . $current_user['last_name']?></h2>

    <div id="editform">
    <form id="edit" method = "post" action = "account.php"><fieldset>
      <legend>Edit Account Information</legend>
      <ul>
        <li class = "form"><p> Please edit one field at a time</p></li>
        <li class="form">
          Current Email: <?php echo $current_user['email']; ?>
          <input type = "email" name = "edit_email"></li>
          <button type = "submit" name = "edit_account" value = "edit_email">Edit Account</button>
        <li class="form">
          Current First Name: <?php echo $current_user['first_name']; ?>
          <input type = "text" name = "edit_first"></li>
          <button type = "submit" name = "edit_account" value = "edit_first">Edit First Name </button>
        <li class="form">
          Current Last Name: <?php echo $current_user['last_name']; ?>
          <input type = "text" name = "edit_last"></li>
          <button type = "submit" name = "edit_account" value = "edit_last">Edit Last Name</button>
        <li class="form">
          Change Password:
          <input type = "password" name = "edit_password"></li>
        <li class="form">
          Confirm Password:
          <input type = "password" name = "edit_password_confirm"></li>
          <button type = "submit" name = "edit_account" value = "edit_password">Edit Password</button>
      </ul>
    </form>
    </div>



<?php include('includes/footer.php');?>
</body>
</html>
