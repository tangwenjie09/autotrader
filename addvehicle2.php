
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
</head>

<body>
<div class="main_container">
    <div class="login-btn right">
      <?php if(!$_SESSION['username']) { ?>
      <button class="btn "><a href="login.php">Login</a></button>
      <?php } ?>
      <?php if($_SESSION['username']) { echo "<div class='label label-info'>".$_SESSION['username']."</div>";?>
        <button class="btn"><a href="logout.php">Logout</a></button>
      <?php } ?>
    </div>  <!-- .login-btn -->

    <div class="login-btn">
      <button class="btn" id="find_customer_btn">Find Customer</button>
      <button class="btn" id="add_individual_btn">Add Individual Customer</button>
      <button class="btn" id="add_business_btn">Add Business Customer</button>
      <button class="btn" id="add_vehicle_btn">Add Vehicle</button>
    </div>  <!-- .login-btn -->

    <p id = "message">this is message</p>

    <form name="findcustomer_form" id="findcustomer_form" method="post" enctype="multipart/form-data">
      <h3>Find Customer</h3> 

      <div class="form-group">
        <label>Customer ID*</label>
        <input class="form-control" type="text" name="customer_id" required>
      </div><!-- .form-group -->

      <input type="hidden" name="findcustomer_submission" value="yes">
      <button type="submit" class="btn btn-default" id="fc_submit">Submit</button>
    </form>  

</div> <!-- .main_container -->

<!-- scripts jquery-->
<script>
    $(document).ready(function(){
        $('#find_customer_btn').click(function(){
          $('form[name="findcustomer_form"]').show();
          $('form[name="addindividual_form"]').hide();
          $('form[name="addbusiness_form"]').hide();
          $('form[name="addvehicle_form"]').hide();
        });

        $('#add_individual_btn').click(function(){
          $('form[name="addindividual_form"]').show();
          $('form[name="findcustomer_form"]').hide();
          $('form[name="addbusiness_form"]').hide();
          $('form[name="addvehicle_form"]').hide();
        });

        $('#add_business_btn').click(function(){
          $('form[name="addbusiness_form"]').show();
          $('form[name="findcustomer_form"]').hide();
          $('form[name="addindividual_form"]').hide();
          $('form[name="addvehicle_form"]').hide();
        });

        $('#add_vehicle_btn').click(function(){
          $('form[name="addvehicle_form"]').show();
          $('form[name="findcustomer_form"]').hide();
          $('form[name="addbusiness_form"]').hide();
          $('form[name="addindividual_form"]').hide();
        });

        $('#findcustomer_form').submit(function(e){
          e.preventDefault();
          // console.log("lala");
          var customer_id = $("input[name=customer_id]").val();
          console.log(customer_id);
          $.post("./findcustomer.php", 
                  { customer_id: customer_id }, 
                  function(){
                    alert( "1st success" );
                  }).done(function(data) {
                    alert( "second success" );
                    $("#message").html(data.error);
                  })
                  .fail(function(res) {
                    alert( "my error" );
                  })
                  .always(function() {
                    alert( "finished" );
                  });
          // $.ajax({ method : "POST",  //type of method
          //          url  : "findcustomer.php",  //your page
          //          data : { customer_id : customer_id },// passing the values
          //          success: function(res){  
          //             console.log(res);
          //             if(res.status=="success"){
          //                 $("#message").html('Success, do something');
          //             }else{
          //                 $("#message").html(res.error);
          //             }     
          //         }
          //     });
          });

    });
</script>
</body>
</html>