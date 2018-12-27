<?php
  include("includes/init.php");
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
  <h1>
    <?php
  //-----------------------Process Account Creation-----------------------------

  if(isset($_POST['creation'])){
    //Gather inputs
    $new_email = filter_input(INPUT_POST, 'new_email', FILTER_VALIDATE_EMAIL);
    $new_password = trim(filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING));
    $new_password_confirm= trim(filter_input(INPUT_POST, 'new_password_confirm', FILTER_SANITIZE_STRING));
    $new_first  = filter_input(INPUT_POST, 'new_first', FILTER_SANITIZE_STRING);
    $new_last  = filter_input(INPUT_POST, 'new_last', FILTER_SANITIZE_STRING);

if($new_email){
  //Email is of valid form
  if($new_password == $new_password_confirm){
    //Passwords match

    //Check and see if email is already in the database
    $sql = "SELECT * FROM accounts WHERE email = :email";
    $params = array(':email' => $new_email);
    $results = exec_sql_query($db, $sql, $params);
    if($results){
      record_message("An account with this email already exists! Redirecting you to the Sign Up page. ");
      header("location: http://".SITE_URL.'/signup.php');
      exit;
    }else{

      //email is free, continue on

      //hash password for security
      $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
      if($hashed_password == FALSE){
        record_message("Critical Error. Try again!");
        header("location: http://".SITE_URL.'/index.php');
        exit;
      }

      //Insert user into the database
      $sql = "INSERT INTO accounts(first_name, last_name, email, password)
              VALUES (:first, :last, :email, :password)";
      $params = array(':first' => $new_first,
                      ':last' => $new_last,
                      ':email' =>$new_email,
                      ':password'=>$hashed_password);
      $results = exec_sql_query($db, $sql, $params);
      if($results == NULL){
        record_message( "Account creation failed. Redirecting you to the Sign Up page. ");
        header("location: http://".SITE_URL.'/signup.php');
        exit;
      }else{
        record_message("Account creation successful! Redirecting you to the Account page.");
        log_in($new_email, $new_password);
        header("location: http://".SITE_URL.'/account.php');
        exit;
      }
    }
  }else{
    record_message("Passwords do not match! Try again.");
    header("location: http://".SITE_URL.'/signup.php');
    exit;
  }
}else{
  record_message("Email Invalid! Try again.");
  header("location: http://".SITE_URL.'/signup.php');
  exit;
}

}else{
  echo "Critical Error. Try again!";
  header("location: http://".SITE_URL.'/index.php');
  exit;
}
 ?>
</h1>
</body>
</html>
