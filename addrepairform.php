<?php

include('lib/common.php');
// written by GTusername4

if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit();
}

if(isset($_GET['vin'])){
  $_SESSION['vin'] =  $_GET['vin'];
}

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['find_recall_submission'])) {
    $nthsa = mysqli_real_escape_string($db, $_POST['nthsa']);
    $query = "SELECT nthsa 
              FROM Recall WHERE Recall.nthsa = '$nthsa';";
    $result = mysqli_query($db, $query);
    $count = mysqli_num_rows($result); 

    $error_msg = [];
    if (!empty($result) && ($count > 0) ) {
      // echo "customer is found!";
      array_push($query_result, "Recall ".$nthsa." is found!");
      $_SESSION['nthsa'] = $nthsa;
    }else {
      // echo "Customer NOT found!";
      array_push($error_msg, "Recall NOT found!");
    }
}

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_recall_submission'])) {
    $nthsa = mysqli_real_escape_string($db, $_POST['nthsa']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $manufacturer = mysqli_real_escape_string($db, $_POST['manufacturer']);

    $query = "INSERT INTO Recall (nthsa, description, manufacturer_name)
              VALUES ('$nthsa', '$description', '$manufacturer');";
    $queryID = mysqli_query($db, $query);

    $error_msg = [];
    if ($queryID  == false) {
      array_push($error_msg,  mysqli_error($db) );
    }else{
      $_SESSION['nthsa'] = $nthsa;
      array_push($query_result, "Recall ".$nthsa." is added!");
    }
}

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['find_vendor_submission'])) {
    $vendor_name = mysqli_real_escape_string($db, $_POST['vendor_name']);
    $query = "SELECT vendor_name FROM Vendor 
              WHERE vendor_name = '$vendor_name'";
    $result = mysqli_query($db, $query);
    $count = mysqli_num_rows($result); 

    $error_msg = [];
    if (!empty($result) && ($count > 0) ) {
      $_SESSION['vendor_name'] = $vendor_name;
      array_push($query_result, "Vendor ".$vendor_name." is found!");
    }else {
      array_push($error_msg, "Vendor NOT found!");
    }
}

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_vendor_submission'])) {
    $vendor_name = mysqli_real_escape_string($db, $_POST['vendor_name']);
    $street = mysqli_real_escape_string($db, $_POST['street']);
    $city = mysqli_real_escape_string($db, $_POST['city']);
    $state = mysqli_real_escape_string($db, $_POST['state']);
    $postal_code = mysqli_real_escape_string($db, $_POST['postal_code']);
    $phone_number = mysqli_real_escape_string($db, $_POST['phone_number']);

    $query = "INSERT INTO Vendor (vendor_name, phone_number, city, street, state, postal_code)
      VALUES ('$vendor_name', '$phone_number', '$city', '$street', '$state', '$postal_code');";
    $queryID = mysqli_query($db, $query);
    $error_msg = [];
    if ($queryID  == false) {
      array_push($error_msg,  mysqli_error($db) );
    }else{
      $_SESSION['vendor_name'] = $vendor_name;
      array_push($query_result, "Vendor ".$vendor_name." is added!");
    }
}

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['add_repair_submission'])) {
    $vin = mysqli_real_escape_string($db, $_POST['vin']);
    $vendor_name = mysqli_real_escape_string($db, $_POST['vendor_name']);
    $nthsa = mysqli_real_escape_string($db, $_POST['nthsa']);
    $start_date = mysqli_real_escape_string($db, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($db, $_POST['end_date']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $cost = mysqli_real_escape_string($db, $_POST['cost']);

    $run_query = true;
    if($cost < 0){
      array_push($error_msg, "Cost should be postive number!" );
      $run_query = false;
    }

    // check if vendor exist
    $query = "SELECT vendor_name FROM Vendor
              WHERE vendor_name = '$vendor_name'";
    $result = mysqli_query($db, $query);
    $count = mysqli_num_rows($result); 
    if($count == 0){
      array_push($error_msg, "Vendor is not in database!" );
      $run_query = false;
    }

    // check if recall exist
    if(!empty($nthsa)){
        $query = "SELECT nthsa FROM Recall
                  WHERE nthsa = '$nthsa'";
        $result = mysqli_query($db, $query);
        $count = mysqli_num_rows($result); 
        if($count == 0){
          array_push($error_msg, "Recall is not in database!" );
          $run_query = false;
        }
    }

    if( strtotime($end_date) < strtotime($start_date) ) {
      array_push($error_msg, "End date is earlier than start date!" );
      $run_query = false;
    }

    $query = "SELECT vin,start_date FROM Repair
              WHERE Repair.vin = '$vin'
              AND (('$start_date' between start_date and end_date) OR
                   ('$end_date' between start_date and end_date))";

    $result = mysqli_query($db, $query);
    $count = mysqli_num_rows($result); 

    if (!empty($result) && ($count > 0) ) {
      array_push($error_msg, "Repair date overlaps with others!" );
      $run_query = false;
    }

    if($run_query==true){
        $status="pending";
        $query = "INSERT INTO Repair (vin, start_date, end_date, status, cost, description, vendor_name, nthsa)
              VALUES ('$vin', '$start_date', '$end_date', '$status', '$cost', '$description', '$vendor_name', '$nthsa');";
        $queryID = mysqli_query($db, $query);
        if ($queryID  == false) {
          // echo "error!";
          array_push($error_msg,  mysqli_error($db) );
        }else{
          // echo "Customer is added!";
          array_push($query_result, "Repair is added!");
        }
    }
}
?>

<?php include("lib/header.php"); include('lib/show_queries.php'); ?>
<title>Add Vehicle</title>
</head>

<body>

<?php if($_SESSION['is_inventorycleck']){ ?>
<div class="main_container">
    <?php include("lib/topbar.php"); ?>

    <div class="login-btn">
      <button class="btn" id="find_recall_btn">Find Recall</button>
      <button class="btn" id="add_recall_btn">Add Recall</button>
      <button class="btn" id="find_vendor_btn">Find Vendor</button>
      <button class="btn" id="add_vendor_btn">Add Vendor</button>
      <button class="btn" id="add_repair_btn">Add Repair</button>
      <a href="viewvehicledetail.php?vin=<?php echo $_SESSION['vin'] ?>" ><button class="btn">Back to Details</button></a>
    </div>  <!-- .login-btn -->

    <div><h3>VIN: <?php echo $_SESSION['vin']?></h3></div>

    <?php include("lib/error.php"); ?>
    <form name="find_recall_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off"> 
      <h3>Find Recall</h3> 

      <div class="form-group">
        <label>NTHSA*</label>
        <input type="text" name="nthsa" class="form-control" required>
      </div><!-- .form-group -->

      <input type="hidden" name="find_recall_submission" value="yes">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>

    <form name="add_recall_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">
      <h3>Add Recall</h3> 

      <div class="form-group">
        <label>NTHSA*</label>
        <input type="text" name="nthsa" class="form-control" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Description*</label>
        <input class="form-control" type="text" name="description" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label for="manufacturer">Manufacturer:</label>
        <select name="manufacturer" class="form-control" id="manufacturer" required>
          <?php
          $query = "SELECT manufacturer_name FROM Manufacturer;";
          $result = mysqli_query($db, $query);
          $count = mysqli_num_rows($result); 
          if (!empty($result) && ($count > 0) ) {
              while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                  // print "<li>{$row['interest']}</li>";
                  echo '<option>' . $row['manufacturer_name'] . '</option>';
              }
          }                                  
          ?>
        </select>
      </div><!-- .form-group -->

      <input type="hidden" name="add_recall_submission" value="yes">
      <button type="submit" class="btn btn-default">Add</button>
    </form> 

    <form name="find_vendor_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"  enctype="multipart/form-data" autocomplete="off">
      <h3>Find Vendor</h3> 

      <div class="form-group">
        <label>Vendor Name*</label>
        <input class="form-control" type="text" name="vendor_name" required>
      </div><!-- .form-group -->

      <input type="hidden" name="find_vendor_submission" value="yes">
      <button type="submit" class="btn btn-default">Submit</button>
    </form> 

    <form name="add_vendor_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>"  enctype="multipart/form-data" autocomplete="off">
      <h3>Add Vendor</h3> 

      <div class="form-group">
        <label>Vendor Name*</label>
        <input class="form-control" type="text" name="vendor_name" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Street*</label>
        <input class="form-control" type="text" name="street" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>City*</label>
        <input class="form-control" type="text" name="city" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>State*</label>
        <input class="form-control" type="text" name="state" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Postal Code*</label>
        <input class="form-control" type="text" name="postal_code" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Phone*</label>
        <input class="form-control" type="text" name="phone_number" required>
      </div><!-- .form-group -->

      <input type="hidden" name="add_vendor_submission" value="yes">
      <button type="submit" class="btn btn-default">Add</button>
    </form> 

    <form name="add_repair_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
      <h3>Add Repair</h3> 

      <div class="form-group">
        <label>VIN*</label>
        <input class="form-control" type="text" name="vin" value="<?php echo $_SESSION['vin']; ?>" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Vendor Name*</label>
        <input class="form-control" type="text" name="vendor_name" value="<?php echo $_SESSION['vendor_name']; ?>" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Recall NTHSA</label>
        <input class="form-control" type="text" name="nthsa" value="<?php echo $_SESSION['nthsa']; ?>" >
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Start Date*</label>
        <input class="form-control" type="date" name="start_date" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>End Date*</label>
        <input class="form-control" type="date" name="end_date" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Description*</label>
        <input class="form-control" type="text" name="description" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Cost*</label>
        <input class="form-control" type="number" step="0.01" name="cost" required>
      </div><!-- .form-group -->

      <input type="hidden" name="add_repair_submission" value="yes">
      <input type="submit" class="btn btn-default" name="submit" value="Add">
    </form>

</div> <!-- .main_container -->
<?php } ?>

<!-- scripts jquery-->
<script>
    $(document).ready(function(){
      var decodedCookie = decodeURIComponent(document.cookie);
      var cookie = decodedCookie.split(';');
        if(cookie[0]=='show_findrecall'){
          $('form').hide();
          $('form[name="find_recall_form"]').show();
        }else if(cookie[0]=='show_addrecall'){
          $('form').hide();
          $('form[name="add_recall_form"]').show();
        }else if(cookie[0]=='show_findvendor'){
          $('form').hide();
          $('form[name="find_vendor_form"]').show();
        }else if(cookie[0]=='show_addvendor'){
          $('form').hide();
          $('form[name="add_vendor_form"]').show();
        }else if(cookie[0]=='show_addrepair'){
          $('form').hide();
          $('form[name="add_repair_form"]').show();
        }

        $('#find_recall_btn').click(function(){
          $('form').hide();
          $('form[name="find_recall_form"]').show();
        });

        $('#add_recall_btn').click(function(){
          $('form').hide();
          $('form[name="add_recall_form"]').show();
        });

        $('#find_vendor_btn').click(function(){
          $('form').hide();
          $('form[name="find_vendor_form"]').show();
        });

        $('#add_vendor_btn').click(function(){
          $('form').hide();
          $('form[name="add_vendor_form"]').show();
        });
        $('#add_repair_btn').click(function(){
          $('form').hide();
          $('form[name="add_repair_form"]').show();
        });

        $("form[name='find_recall_form']").submit(function(e) {
            document.cookie = 'show_findrecall';
        });
        $("form[name='add_recall_form']").submit(function(e) {
            document.cookie = 'show_addrecall';
        });
        $("form[name='find_vendor_form']").submit(function(e) {
            document.cookie = 'show_findvendor';
        });
        $("form[name='add_vendor_form']").submit(function(e) {
            document.cookie = 'show_addvendor';
        });
        $("form[name='add_repair_form']").submit(function(e) {
            document.cookie = 'show_addrepair';
        });

    });
</script>
</body>
</html>