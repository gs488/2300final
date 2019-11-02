<?php
//---------------------------------includes/user set up-------------------------
include('includes/init.php');
include('includes/header.php');

$current_page_id="signup";


//Establish current user
$current_user = "";
if(isset($_SESSION['current_user'])){
$current_user = $_SESSION['current_user'];
header("location: http://".SITE_URL.'/account.php');
}else{ $current_user==NULL;}


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

  <h5><?php echo $pages[$current_page_id];?></h5>

  <?php
  if(count($messages) > 0){print_messages();}
   ?>

  <div id="twoforms">
    <div class="eachform">
    <h2>Login</h2>
    <!--Point users to log in if already have account-->
    <div id = "signupform">
      <form id="signup1" method="post" action ="<?php echo $current_page_id . '.php'?>"> <fieldset>
        <li class="form">
          Username: <input type="email" name="login_email" required>
        </li>
        <li class="form">
          Password: <input type="password" name="login_password" required>
          <button name="login" type="submit">Log In</button>
        </li>
      </form>
    </div>
  </div>

<div class="eachform">
    <h2>Sign Up</h2>
    <!-- Sign up form -->
    <div id="signupform">
     <form id="signup1" method="post" action ="creation.php"> <fieldset>
       <li class="form">
          First Name:
          <input type="text" name="new_first" required>
       </li>
       <li class="form">
          Last Name:
          <input type="text" name="new_last" required>
       </li>
       <li class="form">
          Email:
          <input type="email" name="new_email" required>
       </li>
       <li class="form">
          Password:
          <input type="password" name="new_password" minlength="6" maxlength="15" required>
       </li>
       <li class="form">
          Password Confirmation:
        <input type="password" name="new_password_confirm" minlength="6" maxlength="15" required>
       </li>
       <button name="creation" type="submit">Create Account</button>
      </form>
     </div>
   </div>
   </div>

   <br id="separate"/>

<?php include('includes/footer.php');?>
</body>
</html>
