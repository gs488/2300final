<?php
//---------------------------------Includes/User Set Up-------------------------
include('includes/init.php');

$current_page_id="calendar";

$current_user = "";
if(isset($_SESSION['current_user'])){
$current_user = $_SESSION['current_user'];
}else{$current_user==NULL;}

include('includes/header.php');

//-----------------------------------Calendar Functions-------------------------

/*
function create_appointment($user_name, $user_email, $date, $time_start, $time_end, $description){
$event = new Google_Service_Calendar_Event(array(
  'summary' => 'Session with '.$user_name,
  'location' => '145 Yaple Rd, Ithaca, NY 14850',
  'description' => $description,
  'start' => array(
    'dateTime' => '2015-05-28T09:00:00-07:00',
    'timeZone' => 'America/New_York',
  ),
  'end' => array(
    'dateTime' => '2015-05-28T17:00:00-07:00',
    'timeZone' => 'America/New_York',
  ),
  'recurrence' => array(
    'RRULE:FREQ=DAILY;COUNT=1'
  ),
  'attendees' => array(
    array('email' => $user_email),
  ),
  'reminders' => array(
    'useDefault' => FALSE,
    'overrides' => array(
      array('method' => 'email', 'minutes' => 24 * 60),
      array('method' => 'popup', 'minutes' => 10),
    ),
  ),
));


$calendarId = 'Cayuga Strength';
$event = $service->events->insert($calendarId, $event);
printf('Event created: %s\n', $event->htmlLink);
}
*/
//---------------------------Gather data for this page--------------------------
//Gather the current users appointments
$appointments = gather_appointments($db, $current_user);


//-----------------Appointment Creation and Deletion----------------------------

//create appointment
if(isset($_POST['create_app']) && $_POST['create_app'] == "once"){
  $date = filter_input(INPUT_POST,'app_date',FILTER_SANITIZE_STRING);
  $start = filter_input(INPUT_POST, 'app_time_start',FILTER_SANITIZE_STRING);
  $details =filter_input(INPUT_POST, 'app_details',FILTER_SANITIZE_STRING);
  $user_id = $current_user['accounts_id'];

  $date_time = "Beginning at $start on $date";
  $sql = "INSERT INTO appointments(user_id,	date_time, details)
          VALUES(:id, :date_time,:details)";
  $params = array(':id'=>$user_id, ':date_time'=>$date_time,':details'=>$details);
  if(exec_sql_query($db, $sql, $params)){
    $_POST["create_app"] = "twice";
    record_message("Appointment recorded! A staff member will be with you shortly. Thanky you!");
  }else{
    record_message("Appointment creation failed. Please try again." );
  }

}

//Delete Appointment
if(isset($_POST['app_delete'])){
  $id = $_POST['app_delete'];
  $sql = "DELETE FROM  appointments WHERE appointments_id = :id";
  $params = array(':id'=>$id);
  if(exec_sql_query($db, $sql, $params)){
    record_message("Appointment successfully deleted");
  }else{record_message("Appointment deletion failed. Please try again.");

  }
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

  <?php if($current_user){ ?>
    <br id="log-inbreak1"/>
  <?php } else { ?>
    <br id="log-inbreak"/>
  <?php }?>


  <h1><?php echo $pages[$current_page_id];?></h1>

    <div id="calendarapi">
      <!--Google Calendar API-->
      <!-- <iframe src="https://calendar.google.com/calendar/embed?src=csaccalender%40gmail.com&ctz=America%2FNew_York"
      style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe> -->
      <!--Using screenshot of calendar for project b/c hotlinks are not allowed-->
      <img id="calendarimg" src="images/maycalendar.png"/>
    </div>

    <h2>Appointments</h2>
    <?php
    if($current_user == NULL){
       echo  '<p id="bookappt">To book an appointment, please create an account!</p>';
    }else{

    $today = date('Y-m-d');
    ?>
    <div id="book">
      <form method = "post" action = "calendar.php"><fieldset>
        <legend>Appointment Request Form</legend>
        <ul>
        <li class="form">
          Appointment Date:
          <input type = "date" name = "app_date" min = <?php echo $today; ?> required>
        </li>
        <li class="form">
          Appointment Time Start:
          <input type = "time" name = "app_time_start" min = "06:00" max = "19:00" minstep = "900" required>
        </li>
        <li class="form">
          Appointment Details:
          <input type = "Text" name = "app_details" required>
        </li>
        <li class="form">
          <button name = "create_app" type = "submit" value = "once"> Create Appointment</button>
        </li>
      </form>
    </div>

    <h2 id="myappts">My Appointments</h2>
      <!--appointments database-->
    <?php
    $appointments = gather_appointments($db, $current_user);
    display_appointments($db,$appointments) ?>
    <?php } ?>
    <?php include('includes/footer.php');?>
</body>
</html>
