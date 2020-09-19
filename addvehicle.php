<?php

include('lib/common.php');
// written by GTusername4

if (!isset($_SESSION['username'])) {
  header('Location: login.php');
  exit();
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

if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['addvehicle_submission'])) {
    $error_msg = [];

    $customer_id= mysqli_real_escape_string($db, $_POST['customer_id']);
    $vin = mysqli_real_escape_string($db, $_POST['vin']);
    $vtype = mysqli_real_escape_string($db, $_POST['vtype']);
    $vcondition = mysqli_real_escape_string($db, $_POST['vcondition']);
    $manufacturer = mysqli_real_escape_string($db, $_POST['manufacturer']);
    $model_name = mysqli_real_escape_string($db, $_POST['model_name']);
    $model_year = mysqli_real_escape_string($db, $_POST['model_year']);
    $mileage = mysqli_real_escape_string($db, $_POST['mileage']);
    $description = mysqli_real_escape_string($db, $_POST['description']);
    $purchase_date = mysqli_real_escape_string($db, $_POST['purchase_date']);
    $purchase_price = mysqli_real_escape_string($db, $_POST['purchase_price']);
    $inventory_clerk = $_SESSION['username'];
    $color_num = mysqli_real_escape_string($db, $_POST['color_num']);

    $run_query = true;

    if(!isset($customer_id)){
      array_push($error_msg, "Customer not specified!");
      $run_query = false;
    }else{
      $query = "SELECT customer_id FROM Customer where customer_id = '$customer_id'";
      $result = mysqli_query($db, $query);
      $count = mysqli_num_rows($result);
      if($count == 0){
        array_push($error_msg, "Customer not in database!");
        $run_query = false;
      }
    }

    if(!isset($inventory_clerk)){
      array_push($error_msg, "inventory clerk not specified!");
      $run_query = false;
    }
    if($mileage < 0){
      array_push($error_msg, "Mileage should be positive number!");
      $run_query = false;
    }
    if($purchase_price < 0){
      array_push($error_msg, "Purchase price should be positive number!");
      $run_query = false;
    }

    $colors=mysqli_real_escape_string($db, $_POST["color1"]);
    for ($i = 2; $i <= $color_num; $i++) {
      $input_name = "color".$i;
      $colors.=";".mysqli_real_escape_string($db, $_POST["$input_name"]);
    }

    // if( strtotime($purchase_date) > strtotime('now') ) {
    //     array_push($error_msg, "purchase date should not be later than today.");
    // }

    $query = "INSERT INTO Vehicle (vin, model_name, model_year, description, mileage, vcondition, vtype, manufacturer, seller, inventory_clerk, purchase_price, purchase_date, colors)
        VALUES ('$vin', '$model_name', '$model_year', '$description', '$mileage', '$vcondition', '$vtype', '$manufacturer','$customer_id', '$inventory_clerk', '$purchase_price', '$purchase_date', '$colors')";
    if($run_query ==true){
      $result = mysqli_query($db, $query);
      $error_msg = [];
      if ($result  == false) {
        array_push($error_msg,  mysqli_error($db) );
      }else{
        $_SESSION['vin'] = $vin;
        array_push($query_result, "Vehicle ".$vin." is added!");
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
      <button class="btn" id="find_customer_btn">Find Customer</button>
      <button class="btn" id="add_individual_btn">Add Individual Customer</button>
      <button class="btn" id="add_business_btn">Add Business Customer</button>
      <button class="btn" id="add_vehicle_btn">Add Vehicle</button>
    </div>  <!-- .login-btn -->

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

    <form name="addvehicle_form" id="addvehicle_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" enctype="multipart/form-data" autocomplete="off">
      <h3>Add Vehicle</h3> 

      <div class="form-group">
        <label>Seller ID*</label>
        <input class="form-control" type="text" name="customer_id" value="<?php echo $_SESSION['customer_id']; ?>" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>VIN*</label>
        <input type="text" name="vin" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Vehicle Type*:</label>
        <select name="vtype" class="form-control" required>
            <?php
            $query = "SELECT type_name FROM VehicleType;";
            $result = mysqli_query($db, $query);
            $count = mysqli_num_rows($result); 

            if (!empty($result) && ($count > 0) ) {
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                    // print "<li>{$row['interest']}</li>";
                    echo '<option>' . $row['type_name'] . '</option>';
                }
            }                                  
            ?>
        </select>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Manufacturer*:</label>
        <select name="manufacturer" class="form-control" required>
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

      <div class="form-group">
        <label>model name*</label>
        <input type="text" name="model_name" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Model Year:</label>
        <select name="model_year" class="form-control" required>
          <?php
          define('MAX_OPTIONS', date("Y")+1);
          for($optionIndex=1901; $optionIndex <= MAX_OPTIONS; $optionIndex++){
          echo '<option>'.$optionIndex.'</option>';
          }
          ?>
        </select>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>condition*</label>
        <select name="vcondition" class="form-control" required>
          <option>Excellent</option>
          <option>Very Good</option>
          <option>Good</option>
          <option>Fair</option>
        </select>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>mileage*</label>
        <input class="form-control" type="number" name="mileage" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>description</label>
        <input class="form-control" type="text" name="description" >
      </div><!-- .form-group -->

      <div class="form-group">
        <label>purchase date*</label>
        <input class="form-control" type="date" name="purchase_date" required>
      </div><!-- .form-group -->

     <div class="form-group">
        <label>purchase price*</label>
        <input class="form-control" type="number" step="0.01" name="purchase_price" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>color*:</label>
        <select name="color1" class="form-control" required>
          <option>Aluminum</option>
          <option>Beige</option>
          <option>Black</option>
          <option>Blue</option>
          <option>Brown</option>
          <option>Bronze</option>
          <option>Claret</option>
          <option>Copper</option>
          <option>Cream</option>
          <option>Gold</option>
          <option>Gray</option>
          <option>Green</option>
          <option>Maroon</option>
          <option>Metallic</option>
          <option>Navy</option>
          <option>Orange</option>
          <option>Pink</option>
          <option>Purple</option>
          <option>Red</option>
          <option>Rose</option>
          <option>Rust</option>
          <option>Silver</option>
          <option>Tan</option>
          <option>Turquoise</option>
          <option>White</option>
          <option>Yellow</option>
        </select>
      </div><!-- .form-group -->

      <div id="addinput">
      </div><!-- .form-group -->

      <div class="form-group">
        <button class="btn btn-default" id="addcolor">Add Color</button>
      </div><!-- .form-group -->
      <hr>
      <input type="hidden" name="color_num" id="color_num">
      <input type="hidden" name="addvehicle_submission" value="yes">
      <button type="submit" class="btn btn-default">Add Vehicle</button>
    </form>  

</div> <!-- .main_container -->
<?php } ?>

<!-- scripts jquery-->
<script>
    $(document).ready(function(){
      var decodedCookie = decodeURIComponent(document.cookie);
      var cookie = decodedCookie.split(';');
        if(cookie[0]=='show_addvehicle'){
          $('form').hide();
          $('form[name="addvehicle_form"]').show();
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

        $('#add_vehicle_btn').click(function(){
          $('form').hide();
          $('form[name="addvehicle_form"]').show();
        });
        $("#findcustomer_form").submit(function(e) {
            // e.preventDefault();
            document.cookie = 'show_findcustomer';
        });
        $("#addindividual_form").submit(function(e) {
            document.cookie = 'show_addindividual';
        });
        $("#addbusiness_form").submit(function(e) {
            document.cookie = 'show_addbusiness';
        });
        $("#addvehicle_form").submit(function(e) {
            document.cookie = 'show_addvehicle';
        });

        var counter = 2;
        $("#addcolor").click(function(e){
          e.preventDefault();
          var fieldHTML ='<label>color'+counter+':</label><select name="color'+ counter + '" class="form-control"><option></option><option>Aluminum</option><option>Beige</option><option>Black</option><option>Blue</option><option>Brown</option><option>Bronze</option><option>Claret</option><option>Copper</option><option>Cream</option><option>Gold</option><option>Gray</option><option>Green</option><option>Maroon</option><option>Metallic</option><option>Navy</option><option>Orange</option><option>Pink</option><option>Purple</option><option>Red</option><option>Rose</option><option>Rust</option><option>Silver</option><option>Tan</option><option>Turquoise</option><option>White</option><option>Yellow</option></select>';
          $('#addinput').append(fieldHTML);
          $('#color_num').val(counter);
          counter++;
        });
    });
</script>
</body>
</html>