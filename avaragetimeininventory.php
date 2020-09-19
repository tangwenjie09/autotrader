<?php
include('lib/common.php');
// written by GTusername1

if($showQueries){
  array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

$username = $_SESSION['username'];

$query = "SELECT type_name,
			    IFNULL(AVG(DATEDIFF(sale_date,purchase_date))+1,'N/A') AS avg_time_in_inventory
			FROM VehicleType LEFT OUTER JOIN
			  (SELECT vtype, purchase_date, sale_date
			    FROM Vehicle, Sell
			    WHERE Sell.vin = Vehicle.vin) SoldVehicle
			ON VehicleType.type_name = SoldVehicle.vtype
			GROUP BY VehicleType.type_name;";

$result = mysqli_query($db, $query);
include('lib/show_queries.php');
$count = mysqli_num_rows($result); 

if (!empty($result) && ($count > 0) ) {
	$tmp = '<div class="table-responsive"><table class="table table-striped"><tr><th> Type Name</th><th> Avg Time in Inventory (days) </th></tr>';
	array_push($query_result, $tmp);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        $tmp = "<tr><td>".$row['type_name']."</td><td>".$row['avg_time_in_inventory']."</td></tr>";
        // print "<p>{$tmp}</p>";
        array_push($query_result, $tmp);
    }
    array_push($query_result, '</table></div> <!--.table-responsive-->');
}

?>

<?php include("lib/header.php"); ?>
  <title>View Reports</title>
</head>
<body>
<?php if($_SESSION['is_manager']){ ?>
<div class="main_container">
    <?php include("lib/topbar.php"); ?>
    <h3>Average Time in Inventory</h3>
    <a href="viewreports.php"><button class="btn">Back to Reports Page</button></a>
    <?php include("lib/error.php"); ?>
</div> <!-- .main_container -->
<?php } ?>
</body>
</html>