<?php
include('lib/common.php');
// written by GTusername4

// if (!isset($_SESSION['username'])) {
// 	header('Location: login.php');
// 	exit();
// }

$username = $_SESSION['username'];
// echo $_SESSION['username']." ".$_SESSION['is_inventorycleck']." ".$_SESSION['is_salspeople']." ".$_SESSION['is_manager'];


if( $_SERVER['REQUEST_METHOD'] == 'POST') {

    $vtype = mysqli_real_escape_string($db, $_POST['vtype']);
    $manufacturer = mysqli_real_escape_string($db, $_POST['manufacturer']);
    $model_year = mysqli_real_escape_string($db, $_POST['model_year']);
    $color = mysqli_real_escape_string($db, $_POST['color']);
    $keyword = mysqli_real_escape_string($db, $_POST['keyword']);
    $vin = mysqli_real_escape_string($db, $_POST['vin']);
    $filter = mysqli_real_escape_string($db, $_POST['filter']);
    // echo $filter;

    $dispaly_result = [];

    $query =  "SELECT v1.vin, v1.vtype, v1.model_year, v1.manufacturer, v1.model_name,
                    v1.colors, v1.mileage,
                    1.25*v1.purchase_price+1.1*RepairSummary.total_cost AS sale_price
                FROM Vehicle v1
                LEFT OUTER JOIN
                  (SELECT v2.vin, IFNULL(SUM(cost),0) AS total_cost
                    FROM Vehicle v2
                    LEFT OUTER JOIN Repair
                    ON v2.vin = Repair.vin
                    group by v2.vin) RepairSummary
                ON v1.vin = RepairSummary.vin
                WHERE v1.vin NOT IN
                    (SELECT vin FROM Sell)
                AND v1.vin NOT IN
                  (SELECT vin FROM Repair
                    WHERE Repair.status = 'pending'
                    OR Repair.status = 'in progress')";

    if(isset($_SESSION['is_inventorycleck'])){
      $query =
          "SELECT v1.vin, v1.vtype, v1.model_year, v1.manufacturer, v1.model_name,
              v1.colors, v1.mileage,
              1.25*v1.purchase_price+1.1*RepairSummary.total_cost AS sale_price
          FROM Vehicle v1
          LEFT OUTER JOIN
               (SELECT v2.vin, IFNULL(SUM(cost),0) AS total_cost
                    FROM Vehicle v2
                    LEFT OUTER JOIN Repair
                    ON v2.vin = Repair.vin
                    group by v2.vin) RepairSummary
          ON v1.vin = RepairSummary.vin
          WHERE v1.vin NOT IN
              (SELECT vin FROM Sell)";
    }

    if(isset($_SESSION['is_manager'])){
        $query = "SELECT v1.vin, v1.vtype, v1.model_year, v1.manufacturer, v1.model_name,v1.colors, v1.mileage,
                    1.25*v1.purchase_price+1.1*RepairSummary.total_cost AS sale_price
                FROM Vehicle v1
                LEFT OUTER JOIN
                  (SELECT v2.vin, IFNULL(SUM(cost),0) as total_cost
                    FROM Vehicle v2
                    LEFT OUTER JOIN Repair
                    ON v2.vin = Repair.vin
                    group by v2.vin) RepairSummary
                ON v1.vin = RepairSummary.vin
                WHERE v1.vin IN
                    (SELECT vin FROM Vehicle)";
        if($filter == "sold"){
            $query = "SELECT v1.vin, v1.vtype, v1.model_year, v1.manufacturer, v1.model_name,v1.colors, v1.mileage,
                        1.25*v1.purchase_price+1.1*RepairSummary.total_cost AS sale_price
                    FROM Vehicle v1
                    LEFT OUTER JOIN
                      (SELECT v2.vin, IFNULL(SUM(cost),0) as total_cost
                        FROM Vehicle v2
                        LEFT OUTER JOIN Repair
                        ON v2.vin = Repair.vin
                        group by v2.vin) RepairSummary
                    ON v1.vin = RepairSummary.vin
                    WHERE v1.vin IN
                        (SELECT vin FROM Sell)";
        }
        if($filter == "unsold"){
            $query = "SELECT v1.vin, v1.vtype, v1.model_year, v1.manufacturer, v1.model_name, v1.colors, v1.mileage,
                            1.25*v1.purchase_price+1.1*RepairSummary.total_cost AS sale_price
                        FROM Vehicle v1
                        LEFT OUTER JOIN
                          (SELECT v2.vin, IFNULL(SUM(cost),0) as total_cost
                            FROM Vehicle v2
                            LEFT OUTER JOIN Repair
                            ON v2.vin = Repair.vin
                            group by v2.vin) RepairSummary
                        ON v1.vin = RepairSummary.vin
                        WHERE v1.vin NOT IN
                            (SELECT vin FROM Sell)";
        }

    }

    // if(strlen($keyword)==0&&strlen($vtype)==0&&strlen($model_year)==0&&strlen($manufacturer)==0&&strlen($color)==0&&strlen($vin)==0){
    //     array_push($error_msg, "Please specify search criteria. " . NEWLINE);
    // }else{
    if(!empty($keyword)) $query .= " AND (v1.manufacturer LIKE '%$keyword%'
                                    OR v1.model_year LIKE '%$keyword%'
                                    OR v1.model_name LIKE '%$keyword%'
                                    OR v1.description LIKE '%$keyword%')";
    if(!empty($vtype)) $query .= " AND v1.vtype = '$vtype' ";
    if(!empty($model_year)) $query .= " AND v1.model_year = '$model_year' ";
    if(!empty($manufacturer)) $query .= " AND v1.manufacturer = '$manufacturer' ";
    if(!empty($color)) $query .= " AND v1.colors LIKE '%$color%' ";
    if(!empty($vin)) $query .= " AND v1.vin = '$vin' ";
    $query .= " ORDER BY v1.vin";

    $result = mysqli_query($db, $query);
    $count = mysqli_num_rows($result); 

    if (!empty($result) && ($count > 0) ) {
      array_push($query_result, "<div class='table-responsive'><table class='table data_table table-striped'><tr><th> VIN </th><th> Vehicle Tyep </th><th> Model </th><th> Model Year </th><th> Manufacturer </th><th> Colors</th><th> Mileage </th><th> Sale Price </th><th> Details</th></tr>");

      while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
          // $tmp = "VIN: ".$row['vin']."; Vehicle Tyep: ".$row['vtype']."; Model Year: ".$row['model_year']. "; Manufacturer: ".$row['manufacturer']."; Model: ".$row['model_name']."; Colors: ". $row['colors']."; Mileage: ". $row['mileage']."; Sale Price: ".$row['sale_price'];
          // echo "<a href='viewvehicledetail.php?vin=".$row['vin']."'>".$tmp."</a><br>";
          // array_push($query_result, "<a href='viewvehicledetail.php?vin=".$row['vin']."'>".$tmp."</a><hr>");
        $tmp = "<tr><td>".$row['vin']."</td><td>".$row['vtype']."</td><td>".$row['model_name']. "</td><td>".$row['model_year']."</td><td>".$row['manufacturer']."</td><td>". $row['colors']."</td><td>". $row['mileage']."</td><td>".$row['sale_price']."<td><a href='viewvehicledetail.php?vin=".$row['vin']."'>View</a></td></tr>";
        array_push($query_result, $tmp);
      }
      array_push($query_result, '</table></div> <!--.table-responsive-->');
    }else{
        array_push($error_msg, "Sorry, it looks like we don’t have that in stock!" . NEWLINE);
    }         
} 
?>

<?php include("lib/header.php"); include('lib/show_queries.php'); ?>
<title>Search</title>
</head>

<body>
<div class="main_container">
    <?php include("lib/topbar.php"); ?>

    <div class="login-btn">
      <?php if($_SESSION['is_inventorycleck']) { ?>
      <button class="btn"><a href="addvehicle.php">Add Vehicle</a></button>
      <?php } ?>
      <?php if($_SESSION['is_manager']) { ?>
      <button class="btn"><a href="viewreports.php">View Reports</a></button>
      <?php } ?>
    </div>  <!-- .login-btn -->

    <div class='alert alert-block top'>
        <?php
            $query = "SELECT COUNT(*) AS avail_car_num FROM Vehicle
                        WHERE vin NOT IN
                            (SELECT vin FROM Sell)
                        AND vin NOT IN
                            (SELECT vin FROM Repair
                                WHERE Repair.status = 'pending'
                                OR Repair.status = 'in progress');";
            $result = mysqli_query($db, $query);
            $count = mysqli_num_rows($result); 
            if (!empty($result) && ($count > 0) ) {
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                    print "<h4>Number of Available Vehicle for Sale:{$row['avail_car_num']}</h4>";
                }
            } 
            if($_SESSION['is_inventorycleck']||$_SESSION['is_manager']){
                $query = "SELECT COUNT(*) as unsold_car_num FROM Vehicle
                            WHERE vin NOT IN
                                (SELECT vin FROM Sell);";
                $result = mysqli_query($db, $query);
                $count = mysqli_num_rows($result); 
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                    print "<h4>Number of Unsold Vehicle:{$row['unsold_car_num']}</h4>";
                }
            }  
        ?>
    </div><!-- . jumbotron -->
    
    <?php include("lib/error.php"); ?>
    <form name="search_form" method="post" action="index.php" enctype="multipart/form-data">
      <h3>Search Vehicle</h3> 

      <div class="form-group">
        <label for="vtype">Vehicle Type:</label>
        <select name="vtype" class="form-control" id="vtype">
            <option></option>
            <?php
            $query = "SELECT type_name FROM VehicleType;";
            $result = mysqli_query($db, $query);
            $count = mysqli_num_rows($result); 
            // include('lib/show_queries.php');    
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
        <label for="manufacturer">Manufacturer:</label>
        <select name="manufacturer" class="form-control" id="manufacturer">
          <option></option>
          <?php
          $query = "SELECT manufacturer_name FROM Manufacturer;";
          $result = mysqli_query($db, $query);
          $count = mysqli_num_rows($result); 
          // include('lib/show_queries.php');    
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
        <label for="model_year">Model Year:</label>
        <select name="model_year" class="form-control" id="model_year">
          <option></option>
          <?php
          define('MAX_OPTIONS', date("Y")+1);
          for($optionIndex=1901; $optionIndex <= MAX_OPTIONS; $optionIndex++){
          echo '<option>' . $optionIndex . '</option>';
          }
          ?>
        </select>
      </div><!-- .form-group -->

      <div class="form-group">
          <label for="color">Color:</label>
          <select name="color" class="form-control" id="color">
            <option></option>
            <?php
            $query = "SELECT color_name FROM Color;";
            $result = mysqli_query($db, $query);
            $count = mysqli_num_rows($result); 
            // include('lib/show_queries.php');    
            if (!empty($result) && ($count > 0) ) {
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
                    // print "<li>{$row['interest']}</li>";
                    echo '<option>' . $row['color_name'] . '</option>';
                }
            }                                  
            ?>
          </select>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Keyword</label>
        <input class="form-control" type="text" name="keyword">
      </div><!-- .form-group -->

      <?php if($_SESSION['is_inventorycleck'] or $_SESSION['is_manager'] or $_SESSION['is_salespeople']) { ?>
      <div class="form-group">
        <label>VIN</label>
        <input class="form-control" type="text" name="vin">
      </div><!-- .form-group -->
      <?php } ?>

      <?php if($_SESSION['is_manager']){ ?>
      <div class="form-group">
         <label>Filter</label>
         <div class="radio">
          <label><input type="radio" name="filter" value="all" checked>All</label>
          <label><input type="radio" name="filter" value="sold">Sold</label>
          <label><input type="radio" name="filter" value="unsold">Unsold</label>
        </div> 
      </div><!-- .form-group -->
      <?php } ?>

      <input type="submit" class="btn btn-default" name="submit" value="Search">
    </form>

</div> <!-- .main_container -->
</body>
</html>