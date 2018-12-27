<?php
//---------------------------------includes/user set up-------------------------
include('includes/init.php');
include('includes/header.php');
$current_page_id="merchandise";


//Check for admin privileges
if($current_user != NULL){
$admin  = admin_validate($current_user);
}else{
$admin = FALSE;
}
//------------gather content for this page--------------------------------------
//retrieve items
$items = gather_items($db);

//----------------------Item Select---------------------------------------------
//Select chosen item
if(isset($_POST["item_select"])){
  $item = filter_input(INPUT_POST, 'item_select', FILTER_SANITIZE_STRING);
  $sql  = "SELECT * FROM items WHERE items_id = :id";
  $params = array(':id'=>$item);
  $select = exec_sql_query($db, $sql, $params)->fetchAll();
  $selected_item = $select[0];
}else{$selected_item=array('item_name'=>"", 'price' => "", 'items_id' => "");}
//----------------------Item Add, Edit, Delete Protocols------------------------

//Perform edit item
if(isset($_POST['edit_item'])){
  //Gather inputs
  $item_name = filter_input(INPUT_POST, 'item_name', FILTER_SANITIZE_STRING);
  $price  = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
  $item_picture  = $_FILES["item_picture"];

  if(isset($_POST['items_id'])){

    $edit_item = $_POST['items_id'];
    if($item_name != ""|| $price){
    //ready sql
    $sql = "UPDATE items SET ";
    $params =array();

    if($item_name != ""){
      //Edit item name
      $sql = $sql . "item_name = :name ";
      $params[':name'] = $item_name;

      if($price){
        //edit item price
        $sql = $sql . ",price = :price ";
        $params[':price'] = $price;
      }

    }else if($price){
      //Edit just price
      $sql = $sql . "price = :price ";
      $params[':price'] = $price;
    }

    $sql = $sql."WHERE items_id = :id";
    $params[':id'] = $edit_item;
    if(exec_sql_query($db, $sql, $params)){
      record_message("Item edit successful");
    }else{
      record_message("Item edit failure. Please try again.");
    }
    $items = gather_items($db);
  }

  //Change item picture TODO move before
  if($_FILES['item_picture']['error'] == UPLOAD_ERR_OK ){

    $sql = "SELECT * FROM items WHERE items_id = $edit_item";
    $query = exec_sql_query($db, $sql, $params=array())->fetchAll();
    $item = $query[0];
    $old_name = $item['file_name'];
    $old_ext = $item['file_ext'];

    //unlink old photo
    if(unlink(UPLOAD_ITEMS_PATH . $old_name .'.'.$old_ext)){

    $db->beginTransaction();
    $sql = "UPDATE items SET file_name = :upload_name, file_ext = :ext
               WHERE items_id = :id";
    $params = array(':id'=>$edit_item,
                    ':upload_name'=>$upload_name, ':ext'=>$ext);
    if(exec_sql_query($db, $sql, $params)){

      // upload new image
      $upload_name = $item_name;
      $upload_basename = basename($_FILES["item_picture"]["name"]);
      $ext = strtolower(pathinfo($upload_basename, PATHINFO_EXTENSION));
      $upload_path = UPLOAD_ITEMS_PATH . $upload_name. '.' . $ext;
      if(move_uploaded_file($item_picture["tmp_name"], $upload_path)){
      record_message("New item picture upload successful!");
      $db->commit();
      }else{
      record_message("New item picture upload failure. Please try again.");
      $db->rollBack();
      }
    }else{
      record_message("New item picture upload failure. Please try again.");
      $db->rollBack();
    }
  }
  }

  $items = gather_items($db);
}
  record_message("No element selected.");
  unset($_POST['item_name']);
  unset($_POST['price']);
  unset($_FILES['item_picture']);
}

//Performs item add
if(isset($_POST['add_item'])){
  //ensure all data has been entered
  if(isset($_POST['item_name']) && isset($_POST['price']) && isset($_FILES['item_picture'])){
    $item_name = filter_input(INPUT_POST, 'item_name', FILTER_SANITIZE_STRING);
    $price  = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $item_picture  = $_FILES["item_picture"];

    $db->beginTransaction();
    $sql = "INSERT INTO items(item_name,file_name,file_ext, price)
              VALUES(:name, :upload_name, :ext, :price)";
    $params = array(':name'=>$item_name,
                    ':upload_name'=>$next_id, ':ext'=>$ext,
                    ':price'=>$price);

    if(exec_sql_query($db, $sql, $params)){

      $sql = 'SELECT MAX(items_id) from items';
      $records = exec_sql_query($db, $sql, $params=array())->fetchAll();
      $record = $records[0];
      $next_id = $record['MAX(id)'] + 1;
      $upload_basename = basename($_FILES["item_picture"]["name"]);
      $ext = strtolower(pathinfo($upload_basename, PATHINFO_EXTENSION));

      $upload_path = UPLOAD_ITEMS_PATH . $next_id. '.' . $ext;
      if(move_uploaded_file($item_picture["tmp_name"], $upload_path)){
      record_message("New item addition successful!");
      $db->commit();
      }else{
      $db->rollBack();
      record_message("New item addition failed. Please try again.");
      }
    }else{
      record_message("New item addition failed. Please try again.");
      $db->rollBack();
    }
  }else{
      record_message("Inputs insufficient. Make sure all fields are entered correctly");
  }
  $items = gather_items($db);
  unset($_POST['item_name']);
  unset($_POST['price']);
  unset($_FILES['item_picture']);
}


//item delete
if(isset($_POST['item_delete'])){
  $item_id = $_POST['items_id'];

  $sql = "SELECT * FROM items WHERE items_id = :id";
  $item = exec_sql_query($db, $sql, $params =array(':id'=>$item_id))->fetchAll();
  $ext = $item[0]['file_ext'];


  $db->beginTransaction();
  $sql = "DELETE FROM items WHERE items_id = :id";
  $params = array(':id'=>$item_id);
  if(exec_sql_query($db, $sql, $params)){
    record_message("Deletion from database successful.");
    $result = unlink(UPLOAD_ITEMS_PATH . $item_id . '.'.$ext);
    if($result){
      record_message("Item picture unlink successful.");
      $db->commit();
      header("location: http://".SITE_URL.'/merchandise.php');
    }else{
      record_message("Item picture unlink failure. Please try again.");
      $db->rollBack();
    }
  }else{
    record_message("Item deletion from database failure. Please try again.");
    $db->rollBack();
  }
   $items = gather_items($db);
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

    <!--gallery of products available for purchase-->
    <div id="gallery">
    <?php
    display_items($admin, $items)
     ?>
   </div>

   <?php if($admin){ ?>
     <h2> Admin Terminal</h2>
     <?php
     merch_edit_pane($selected_item);
     ?>

   <?php } ?>

<?php include('includes/footer.php');?>
</body>
</html>
