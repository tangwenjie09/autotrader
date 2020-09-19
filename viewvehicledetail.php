<?php

include('lib/common.php');
// written by GTusername4

// if (!isset($_SESSION['username'])) {
// 	header('Location: login.php');
// 	exit();
// }
if(isset($_GET['vin'])){$_SESSION[$vin]=$_GET['vin'];}

// if(isset($_SESSION['msg']))echo $_SESSION['msg'];
// if(isset($_SESSION['run_query']))echo "session:".$_SESSION['run_query'];

if( $_SERVER['REQUEST_METHOD'] == 'POST') {
    $vin = mysqli_real_escape_string($db, $_POST['vin']);
    $start_date = mysqli_real_escape_string($db, $_POST['start_date']);
    $status = mysqli_real_escape_string($db, $_POST['status']);

    $run_query = true;
    if(!isset($vin)){
      array_push($error_msg, "VIN not specified!");
      $run_query = false;
    }
    if(!isset($start_date)){
      array_push($error_msg, "start_date not specified!");
      $run_query = false;
    }
    $query =  "UPDATE Repair SET status = '$status'
                WHERE vin = '$vin'
                AND start_date = '$start_date'";

    if($run_query ==ture){
      $result = mysqli_query($db, $query);
      $error_msg = [];
      if ($result == false) {
        $_SESSION['msg'] = "error";
        array_push($error_msg,  mysqli_error($db) );
      }else{
        $_SESSION['msg'] = "correct update";
        array_push($query_result, "Repair is updated!");
        header("Location:viewvehicledetail.php?vin=$vin");
      }
    }
}

?>

<?php include("lib/header.php"); include('lib/show_queries.php'); ?>
<title>View Vehicle Details</title>
</head>

<body>
<div class="main_container">
    <?php include("lib/topbar.php");include("lib/error.php");?>

    <div class="basic_info alert alert-success">
        <h3>Basci Information</h3>
        <?php 
            if(isset($_GET['vin'])){$_SESSION[$vin]=$_GET['vin'];} 
            $vin =  $_SESSION[$vin];
            $query = "SELECT v1.vin, v1.vtype, v1.model_name,v1.model_year, 
                            v1.manufacturer, v1.colors, v1.mileage,
                            1.25*v1.purchase_price+1.1*RepairSummary.total_cost AS sale_price,
                            v1.description
                        FROM Vehicle v1
                        LEFT OUTER JOIN
                          (SELECT v2.vin, IFNULL(SUM(cost),0) AS total_cost
                            FROM Vehicle v2
                            LEFT OUTER JOIN Repair
                            ON v2.vin = Repair.vin
                            group by v2.vin) RepairSummary
                        ON v1.vin = RepairSummary.vin
                        WHERE v1.vin = '$vin'";

            $result = mysqli_query($db, $query);
            include('lib/show_queries.php');
            $count = mysqli_num_rows($result); 
            if (!empty($result) && ($count > 0) ) {
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                    $tmp = "VIN: ".$row['vin']."<br>Vehicle Tyep: ".$row['vtype']."<br> Model Year: ".$row['model_year']. "<br> Manufacturer: ".$row['manufacturer']."<br> Model: ".$row['model_name']."<br> Colors: ". $row['colors']."<br> Mileage: ". $row['mileage']."<br> Sale Price: ".$row['sale_price']."<br> Description: ".$row['description'];
                    print "<p>{$tmp}</p>";
                }
            }else{
                array_push($error_msg, "Error, no such vehicle." . NEWLINE);
            }       
        ?>
    </div><!--. basic_info-->

    <div class="cost_info alert alert-warning">
      <?php 
        if(isset($_GET['vin'])){$_SESSION[$vin]=$_GET['vin'];} 
        $vin =  $_SESSION[$vin];
        if($_SESSION['is_inventorycleck']||$_SESSION['is_manager']){
          $cost_query = "SELECT  v1.purchase_price, IFNULL(RepairSummary.total_cost, 0) AS repair_cost
                      FROM Vehicle v1
                      LEFT OUTER JOIN
                        (SELECT v2.vin, IFNULL(SUM(cost),0) AS total_cost
                          FROM Vehicle v2
                          LEFT OUTER JOIN Repair
                          ON v2.vin = Repair.vin
                          group by v2.vin) RepairSummary
                      ON v1.vin = RepairSummary.vin
                      WHERE v1.vin = '$vin'";
          $cost_result = mysqli_query($db, $cost_query);
          $count = mysqli_num_rows($cost_result); 
          if (!empty($cost_result) && ($count > 0) ) {
              while ($row = mysqli_fetch_array($cost_result, MYSQLI_ASSOC)){
                  echo "<p>purchase_price: ".$row['purchase_price']."</p>";
                  echo "<p>total_repair_cost: ".$row['repair_cost']."</p>";
              }
          }
        }
      ?>
    </div><!--.cost_info-->

    <div class="btn_group">
      <?php 
          $vin = $_SESSION[$vin];
          $query = "SELECT v1.vin FROM ((SELECT vin FROM Sell) UNION (SELECT vin FROM Repair WHERE status = 'pending' OR status = 'in progress'))v1 WHERE v1.vin ='$vin'";
          $result = mysqli_query($db, $query);
          $count = mysqli_num_rows($result);
          $show_sell = true;
          if (!empty($result) && ($count > 0) ) {$show_sell = false;}
          if($_SESSION['is_salespeople']&&$show_sell==true){ ?>
        <button class="btn"><a href='saleorderform.php?vin=<?php echo $_GET['vin']; ?>'>Sell Vehicle</a></button>
      <?php } ?>
      <?php if($_SESSION['is_inventorycleck']){ ?>
          <button class="btn"><a href='addrepairform.php?vin=<?php echo $_GET['vin']; ?>'>Add Repair</a></button>
      <?php } ?>
    </div><!--. btn_group-->

    <div class="repairs">
      <?php if($_SESSION['is_inventorycleck']||$_SESSION['is_manager']){ ?>
      <h3>Repairs</h3>
      <div class="table-responsive"><table class="table data_table .table-striped">
          <tr>
            <th> Vendor Name </th>
            <th> Start Date </th>
            <th> End Date </th>
            <th> Status </th>
            <th> Cost </th>
            <th> Recall NTHSA</th>
            <th> Description</th>
            <?php if($_SESSION['is_inventorycleck']){ ?>
            <th> Update Repair</th>
            <?php } ?>
          </tr>
          <?php 
              if(isset($_GET['vin'])){$_SESSION[$vin]=$_GET['vin'];} 
              $vin =  $_SESSION[$vin];
              $query = "SELECT vendor_name, start_date, end_date,
                             status, cost, Repair.description AS des1,
                             IFNULL(Repair.nthsa,'') AS nthsa, 
                             IFNULL(Recall.description,'') AS des2
                        FROM  Repair LEFT OUTER JOIN Vehicle
                        ON Repair.vin= Vehicle.vin
                        LEFT OUTER JOIN Recall
                        ON Recall.nthsa = Repair.nthsa
                        WHERE Vehicle.vin = '$vin' 
                        ORDER BY start_date";
              $result = mysqli_query($db, $query);
              $count = mysqli_num_rows($result);

              if (!empty($result) && ($count > 0) ) {
                  while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                      // $tmp = $row['vendor_name'] . " " . $row['start_date'] . " " . $row['end_date'] . " " . $row['status'] . " " . $row['cost'] . " " . $row['nhtsa_recall_number'];
                      // echo "<p>".$tmp."</p>";
                    $des = "Repair Description: ".$row[des1]."\n";
                    if(!empty($row[des2]))$des.="Recall Description: ".$row[des2]."\n";
                    $tmp = "<tr><td>".$row['vendor_name']."</td><td>".$row['start_date']."</td><td>".$row['end_date'] ."</td><td>".$row['status']."</td><td>".$row['cost']."</td><td>".$row['nthsa']."</td>";
                    $tmp.= "<td class='view_description'><button>view</button><p class='hide'>".$des."</p></td>";
                    if(!isset($_SESSION['is_inventorycleck'])) {
                      $tmp.="</tr>";
                    }
                    else {
                        if($row['status']=="completed"){ 
                          $tmp.="<td>--</td></tr>";
                        }else {
                          $tmp.= "<td class='update_repair'><button>update</button><p class='hide'>".$_SESSION[$vin].";".$row['start_date']."</p></td></tr>";
                        }
                    }
                    echo $tmp;
                  }
              }
          ?>
        </table>
      </div> <!--.table-responsive-->
      <hr>
    <?php } ?>
    </div><!--. repairs inventory_clerk-->

    <div class="modal hide" role="dialog">
        <form  name="update_repair_form" action="viewvehicledetail.php" method="post" enctype="multipart/form-data">
          <h3>Update Repair Status</h3>
          <div class="form-group">
            <label class="form_vin">VIN</label>
            <input class="hide" type="text" name="vin" required>
          </div><!-- .form-group -->
          <div class="form-group">
            <label class="form_start_date">Start Date</label>
            <input class="hide" type="date" name="start_date" required>
          </div><!-- .form-group -->
          <div class="form-group">
            <label>status</label>
            <select name="status" class="form-control" id="status" required>
                <option value="completed">completed</option>
                <option value="in progress">in progress</option>
                <option value="pending">pending</option>
            </select>
          </div><!-- .form-group -->
          <input class="btn btn-default" type="submit" value="Submit">
        </form>
        <button class="btn close_modal">Close</button>
    </div><!--.modal-->

    <?php if($_SESSION['is_manager']){ ?>
    <div class="purechaseinfo manager">
      <h3>Purchase Info</h3>
      <div class="alert alert-info">
      <?php 
        if(isset($_GET['vin'])){$_SESSION[$vin]=$_GET['vin'];} 
        $vin = $_SESSION[$vin];
        $purchase_info_query = "SELECT purchase_price, purchase_date,
           LoginUser.first_name as inventory_clerk_first,
           LoginUser.last_name as inventory_clerk_last,
             IFNULL(AllCustomerInfo.business_name, '') AS business_name,
             IFNULL(AllCustomerInfo.contact_title, '') AS contact_title,
             IFNULL(AllCustomerInfo.contact_first_name, '') AS contact_first_name,
             IFNULL(AllCustomerInfo.contact_last_name, '') AS contact_last_name,
             IFNULL(AllCustomerInfo.first_name, '') AS customer_first,
             IFNULL(AllCustomerInfo.last_name, '') AS customer_last,
             AllCustomerInfo.city, AllCustomerInfo.street,
             AllCustomerInfo.state, AllCustomerInfo.postal_code,
             AllCustomerInfo.email
          FROM Vehicle, LoginUser,
               (SELECT c1.customer_id,
                   c1.city, c1.street, c1.state, c1.postal_code, c1.email,
                   b1.business_name, b1.contact_title, b1.contact_first_name,
                   b1.contact_last_name, i1.first_name, i1.last_name
                FROM Customer c1 LEFT OUTER JOIN Business b1
                ON c1.customer_id = b1.customer_id
                LEFT OUTER JOIN Individual i1
                ON c1.customer_id = i1.customer_id
                   ) AllCustomerInfo
          WHERE Vehicle.vin = '$vin'
          AND AllCustomerInfo.customer_id = Vehicle.seller
          AND LoginUser.username = Vehicle.inventory_clerk";
        $purchase_info = mysqli_query($db, $purchase_info_query);
        $count = mysqli_num_rows($purchase_info); 
        array_push($error_msg,  mysqli_error($db));

        if (!empty($purchase_info) && ($count > 0) ) {
            while ($row = mysqli_fetch_array($purchase_info, MYSQLI_ASSOC)){
                $tmp = "purchase_price: ".$row['purchase_price']."<br>purchase_date: ".$row['purchase_date'];
                if(!empty($row['customer_last'])){
                    $tmp.="<br>Seller first name: ".$row['customer_first']."<br>Seller last name: ".$row['customer_last'];
                }
                if(!empty($row['business_name'])){
                  $tmp.="<br>Seller business_name: ".$row['business_name']."<br>contact_title:".$row['contact_title']."<br>contact_first_name:".$row['contact_first_name']."<br>contact_last_name:".$row['contact_last_name'];
                }
                $tmp.="<br>Address:".$row['street'].", ".$row['city'].", ".$row['state'].", ".$row['postal_code']."<br>email:".$row['email'];
                $tmp.="<br>inventory_clerk first name:".$row['inventory_clerk_first']."<br>inventory_clerk last name:".$row['inventory_clerk_last'];
                echo "<p>".$tmp."</p>";
            }
        }
      ?>
      </div><!-- .alert alert-info-->
    </div><!--. purechaseinfo manager-->
    <?php } ?>

    <div class="saleinfo manager">
      <?php if($_SESSION['is_manager']){ 
          if(isset($_GET['vin'])){$_SESSION[$vin]=$_GET['vin'];} 
          $vin =  $_SESSION[$vin];
          $sale_info_query = "SELECT sale_date, 
                LoginUser.first_name AS salespeople_first, 
                LoginUser.last_name AS salespeople_last,
               IFNULL(AllCustomerInfo.business_name, ''),
               IFNULL(AllCustomerInfo.contact_title, ''),
               IFNULL(AllCustomerInfo.contact_first_name, '') AS contact_first_name,
               IFNULL(AllCustomerInfo.contact_last_name, '') AS contact_last_name,
               IFNULL(AllCustomerInfo.first_name, '') AS customer_first,
               IFNULL(AllCustomerInfo.last_name, '') AS customer_last,
               AllCustomerInfo.city, AllCustomerInfo.street,
               AllCustomerInfo.state, AllCustomerInfo.postal_code,
               AllCustomerInfo.email
                FROM Sell, LoginUser,
                 (SELECT c1.customer_id,
                     c1.city, c1.street, c1.state, c1.postal_code, c1.email,
                     b1.business_name, b1.contact_title, b1.contact_first_name,
                     b1.contact_last_name,i1.first_name, i1.last_name
                  FROM Customer c1 LEFT OUTER JOIN Business b1
                  ON c1.customer_id = b1.customer_id
                  LEFT OUTER JOIN Individual i1
                  ON c1.customer_id = i1.customer_id
                     ) AllCustomerInfo
                WHERE Sell.vin = '$vin'
                AND AllCustomerInfo.customer_id = Sell.customer_id
                AND LoginUser.username = Sell.username;";
        $sale_info = mysqli_query($db, $sale_info_query);
        $count = mysqli_num_rows($sale_info); 
        array_push($error_msg,  mysqli_error($db));

        if (!empty($sale_info) && ($count > 0) ) {
          echo "<h3>Sale Information</h3>";
          echo "<div class='alert alert-info'>";
            while ($row = mysqli_fetch_array($sale_info, MYSQLI_ASSOC)){
                $tmp = "Sale date: ".$row['sale_date'];;
                if(!empty($row['customer_last'])){
                    $tmp.="<br>Customer first name: ".$row['customer_first']."<br>Customer last name: ".$row['customer_last'];
                }
                if(!empty($row['business_name'])){
                  $tmp.="<br>Customer business_name: ".$row['business_name']."<br>contact_title:".$row['contact_title']."<br>contact_first_name:".$row['contact_first_name']."<br>contact_last_name:".$row['contact_last_name'];
                }
                $tmp.="<br>Address:".$row['street'].", ".$row['city'].", ".$row['state'].", ".$row['postal_code']."<br>email:".$row['email'];
                $tmp.="<br>salespeople first name:".$row['salespeople_first']."<br>salespeople last name:".$row['salespeople_last'];
                echo "<p>".$tmp."</p>";
            }
        }
      echo "</div><!-- .alert alert-info-->";
      } ?>
    </div><!--. saleinfo manager-->

</div> <!-- .main_container -->

<!-- scripts jquery-->
<script>
    $(document).ready(function(){
      $(".view_description").click(function(){
        var msg = $(this).find( ".hide" ).text();
        alert(msg);
      });

      $(".update_repair").click(function(){
        $(".modal").show();
        var msg = $(this).find( ".hide" ).text();
        var data = msg.split(';');
        var vin = data[0];
        var start_date =data[1];
        // console.log(vin);
        // console.log(start_date);
        $(".form_vin").text("VIN: " + vin);
        $(".form_start_date").text("start date: " + start_date);
        $('input[name=vin]').val(vin);
        $('input[name=start_date]').val(start_date);
      });

      $(".close_modal").click(function(){
        $(".modal").hide();
      });

    });
</script>
</body>
</html>