<div id="headerpic">
  <img id="logo" src="images/logo.png"/>
</div>
<nav>
  <ul id="menu">
    <li><a id='index' href='index.php'>Home</a></li>
    <li><a id='about' href='about.php'>About Us</a></li>
    <li><a id='calendar' href='calendar.php'>Appointments</a></li>
    <li><a id='reviews' href='reviews.php'>Reviews</a></li>
    <li><a id='merchandise' href='merchandise.php'>Merchandise</a></li>
    <?php

    $current_user = check_login();
    if($current_user == NULL){
      echo "<li><a id='signup' href='signup.php'>Sign Up</a></li>";
    }else{
      echo "<li><a id='account' href='account.php'>My Account</a></li>";
    }
    // //Header log in
    // echo '<li>';
    // if($current_user == NULL){
    // echo '<form id="login" method="post" action ="' . $current_page_id .'.php"> <fieldset>
    //      Username: <input type="email" name="login_email" required>
    //      <br/>
    //      Password: <input type="password" name="login_password" required>
    //      <button name="login" type="submit">Log In</button>
    //      </form>';
    // }else{
    // echo '<form id="logout" method="post" action ="' . $current_page_id .'.php"> <fieldset>
    //       Currently logged in as: ' .
    //       $current_user["first_name"] . ' ' . $current_user["last_name"] . '   ' .
    //       '<button name="logout" type="submit">Log Out</button>
    //        </form>';
    // }
    // echo '</li>';
    ?>
  </ul>
</nav>
