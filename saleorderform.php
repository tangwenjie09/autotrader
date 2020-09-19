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

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['findcustomer_submission'])) {
    $customer_id = mysqli_real_escape_string($db, $_POST['customer_id']);
    $query = "SELECT customer_id FROM
                    (SELECT customer_id, null AS tax_id, driver_license_id FROM Individual
                        UNION
                    SELECT customer_id, tax_id, null AS driver_license_id FROM Business) AllCustomer
                    WHERE AllCustomer.tax_id = '$customer_id'
                    OR AllCustomer.driver_license_id = '$customer_id'";
    $result = mysqli_query($db, $query);
    $count = mysqli_num_rows($result); 

    $error_msg = [];
    if (!empty($result) && ($count > 0) ) {
      array_push($query_result, "Customer ".$customer_id." is found!");
      $_SESSION['customer_id'] = $customer_id;
    }else {
      array_push($error_msg, "Customer NOT found!");
    }
}

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['addindividual_submission'])) {
    $customer_id = mysqli_real_escape_string($db, $_POST['customer_id']);
    $first_name = mysqli_real_escape_string($db, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($db, $_POST['last_name']);
    $street = mysqli_real_escape_string($db, $_POST['street']);
    $city = mysqli_real_escape_string($db, $_POST['city']);
    $state = mysqli_real_escape_string($db, $_POST['state']);
    $postal_code = mysqli_real_escape_string($db, $_POST['postal_code']);
    $phone_number = mysqli_real_escape_string($db, $_POST['phone_number']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $driver_license_id = $customer_id;

    $query = "INSERT INTO Customer (customer_id, phone_number, city, street, state, postal_code, email)
              VALUES ('$customer_id', '$phone_number', '$city', '$street', '$state', '$postal_code', '$email');";
    $query.= "INSERT INTO Individual (driver_license_id, first_name, last_name, customer_id)
         VALUES ('$customer_id', '$first_name', '$last_name', '$customer_id')";
    $error_msg = [];
    $queryID = mysqli_multi_query($db, $query);
    if ($queryID  == false) {
      array_push($error_msg,  mysqli_error($db) );
    }else{
        $_SESSION['customer_id'] = $customer_id;
        array_push($query_result, "Customer ".$customer_id." is added and set as seller!");
    }
}

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['addbusiness_submission'])) {
    $customer_id = mysqli_real_escape_string($db, $_POST['customer_id']);
    $business_name = mysqli_real_escape_string($db, $_POST['business_name']);
    $contact_title = mysqli_real_escape_string($db, $_POST['contact_title']);
    $contact_first_name = mysqli_real_escape_string($db, $_POST['contact_first_name']);
    $contact_last_name = mysqli_real_escape_string($db, $_POST['contact_last_name']);
    $street = mysqli_real_escape_string($db, $_POST['street']);
    $city = mysqli_real_escape_string($db, $_POST['city']);
    $state = mysqli_real_escape_string($db, $_POST['state']);
    $postal_code = mysqli_real_escape_string($db, $_POST['postal_code']);
    $phone_number = mysqli_real_escape_string($db, $_POST['phone_number']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $tax_id = $customer_id;

    $query = "INSERT INTO Customer (customer_id, phone_number, city, street, state, postal_code, email)
              VALUES ('$customer_id', '$phone_number', '$city', '$street', '$state', '$postal_code', '$email');";
    $query.= "INSERT INTO Business (tax_id, business_name, contact_title, contact_first_name, contact_last_name, customer_id)
                VALUES ('$customer_id', '$business_name', '$contact_title', '$contact_first_name', '$contact_last_name', '$customer_id');";
    $error_msg = [];
    $queryID = mysqli_multi_query($db, $query);
    if ($queryID  == false) {
      array_push($error_msg,  mysqli_error($db) );
    }else{
        $_SESSION['customer_id'] = $customer_id;
        array_push($query_result, "Customer ".$customer_id." is added and set as seller!");
    }
}

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['saleorder_submission'])) {
    $error_msg = [];

    $sale_date = mysqli_real_escape_string($db, $_POST['sale_date']);
    $customer_id= $_SESSION['customer_id'];
    $salespeople= $_SESSION['username'];
    $vin =  $_SESSION['vin'];

    $run_query = true;

    if(!isset($customer_id)){
      array_push($error_msg, "Customer not specified!");
      $run_query = false;
    }else{
      $query = "SELECT customer_id FROM Customer where customer_id = '$customer_id'";
      $result = mysqli_query($db, $query);
      $count = mysqli_num_rows($result);
      if($count == 0){
        array_push($error_msg, "Customer ".$customer_id." not in database!");
        $run_query = false;
      }
    }

    if(!isset($salespeople)){
      array_push($error_msg, "salespeople not specified!");
    }
    if(!isset($vin)){
      array_push($error_msg, "VIN not specified!");
    }

    $query = "INSERT INTO Sell (vin, customer_id, username, sale_date)
                VALUES ('$vin', '$customer_id', '$salespeople', '$sale_date');";

    if($run_query ==true){
      $result = mysqli_query($db, $query);
      $error_msg = [];
      if ($result  == false) {
        array_push($error_msg,  mysqli_error($db) );
      }else{
        $_SESSION['vin'] = $vin;
        array_push($query_result, "Vehicle ".$vin." is sold!");
      }
    }
  }
?>

<?php include("lib/header.php"); include('lib/show_queries.php'); ?>
<title>Sell Vehicle</title>
</head>

<body>
<?php if($_SESSION['is_salespeople']){ ?>
<div class="main_container">
    <?php include("lib/topbar.php"); ?>

    <div class="login-btn">
      <button class="btn" id="find_customer_btn">Find Customer</button>
      <button class="btn" id="add_individual_btn">Add Individual Customer</button>
      <button class="btn" id="add_business_btn">Add Business Customer</button>
      <button class="btn" id="sell_vehicle_btn">Sell Vehicle</button>
      <a href="viewvehicledetail.php?vin=<?php echo $_SESSION['vin'] ?>" ><button class="btn">Back to Details</button></a>
    </div>  <!-- .login-btn -->

    <div><h3>VIN: <?php echo $_SESSION['vin']?></h3></div>

    <?php include("lib/error.php"); ?>

    <form name="findcustomer_form" id="findcustomer_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off"> 
      <h3>Find Customer</h3> 

      <div class="form-group">
        <label>Customer ID*</label>
        <input type="text" name="customer_id" class="form-control input-normal" required>
      </div><!-- .form-group -->

      <input type="hidden" name="findcustomer_submission" value="yes">
      <button type="submit" class="btn btn-default">Submit</button>
    </form>

    <form name="addindividual_form" id="addindividual_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">
      <h3>Add Individual Customer</h3> 

      <div class="form-group">
        <label>Driver's License ID*</label>
        <input class="form-control" type="text" name="customer_id" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>First Name*</label>
        <input class="form-control" type="text" name="first_name" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Last Name*</label>
        <input class="form-control" type="text" name="last_name" required>
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

      <div class="form-group">
        <label>Email</label>
        <input class="form-control" type="email" name="email">
      </div><!-- .form-group -->

      <input type="hidden" name="addindividual_submission" value="yes">
      <button type="submit" class="btn btn-default">Add</button>
    </form> 

    <form name="addbusiness_form" id="addbusiness_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">
      <h3>Add Business Customer</h3> 

      <div class="form-group">
        <label>Tax ID*</label>
        <input class="form-control" type="text" name="customer_id" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>business name*</label>
        <input class="form-control" type="text" name="business_name" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>contact title*</label>
        <input class="form-control" type="text" name="contact_title" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>contact first name*</label>
        <input class="form-control" type="text" name="contact_first_name" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>contact last name*</label>
        <input class="form-control" type="text" name="contact_last_name" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>street*</label>
        <input class="form-control" type="text" name="street" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>city*</label>
        <input class="form-control" type="text" name="city" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>state*</label>
        <input class="form-control" type="text" name="state" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>postal_code*</label>
        <input class="form-control" type="text" name="postal_code" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>phone_number*</label>
        <input class="form-control" type="text" name="phone_number" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>email</label>
        <input class="form-control" type="email" name="email">
      </div><!-- .form-group -->

      <input type="hidden" name="addbusiness_submission" value="yes">
      <button type="submit" class="btn btn-default">Add</button>
    </form> 

    <form name="saleorder_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data">
      <h3>Sale Order</h3> 

      <div class="form-group">
        <label>Customer ID*</label>
        <input class="form-control" type="text" name="customer_id" value="<?php echo $_SESSION['customer_id']; ?>" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Sale Date*</label>
        <input class="form-control" type="date" name="sale_date" required>
      </div><!-- .form-group -->

      <input type="hidden" name="saleorder_submission" value="yes">
      <input type="submit" class="btn btn-default" name="submit" value="Submit">
    </form>

</div> <!-- .main_container -->
<?php } ?>

<!-- scripts jquery-->
<script>
    $(document).ready(function(){
      var decodedCookie = decodeURIComponent(document.cookie);
      var cookie = decodedCookie.split(';');
        if(cookie[0]=='show_saleorder'){
          $('form').hide();
          $('form[name="saleorder_form"]').show();
        }else if(cookie[0]=='show_addindividual'){
          $('form').hide();
          $('form[name="addindividual_form"]').show();
        }else if(cookie[0]=='show_addbusiness'){
          $('form').hide();
          $('form[name="addbusiness_form"]').show();
        }else if(cookie[0]=='show_findcustomer'){
          $('form').hide();
          $('form[name="findcustomer_form"]').show();
        }

        $('#find_customer_btn').click(function(){
          $('form').hide();
          $('form[name="findcustomer_form"]').show();
        });

        $('#add_individual_btn').click(function(){
          $('form').hide();
          $('form[name="addindividual_form"]').show();
        });

        $('#add_business_btn').click(function(){
          $('form').hide();
          $('form[name="addbusiness_form"]').show();
        });

        $('#sell_vehicle_btn').click(function(){
          $('form').hide();
          $('form[name="saleorder_form"]').show();
        });
        $("#findcustomer_form").submit(function(e) {
            document.cookie = 'show_findcustomer';
        });
        $("#addindividual_form").submit(function(e) {
            document.cookie = 'show_addindividual';
        });
        $("#addbusiness_form").submit(function(e) {
            document.cookie = 'show_addbusiness';
        });
        $("#saleorder_form").submit(function(e) {
            document.cookie = 'show_saleorder';
        });

    });
</script>
</body>
</html>