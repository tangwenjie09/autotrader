<?php
include('lib/common.php');
// written by GTusername1

if($showQueries){
  array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

$username = $_SESSION['username'];

$query = "SELECT type_name,
		    IFNULL(MIN(DATEDIFF(CURDATE(),purchase_date))+1,'N/A') AS min_age,
		    IFNULL(MAX(DATEDIFF(CURDATE(),purchase_date))+1,'N/A') AS max_age,
		    IFNULL(AVG(DATEDIFF(CURDATE(),purchase_date))+1,'N/A') AS avg_age
		FROM VehicleType LEFT OUTER JOIN
		  (SELECT vtype, purchase_date FROM Vehicle
		    WHERE vin NOT IN
		    (SELECT Sell.vin from Sell)) UnsoldVehicle
		ON VehicleType.type_name = UnsoldVehicle.vtype
		GROUP BY VehicleType.type_name;";

$result = mysqli_query($db, $query);
include('lib/show_queries.php');
$count = mysqli_num_rows($result); 

if (!empty($result) && ($count > 0) ) {
	$tmp = '<div class="table-responsive"><table class="table table-striped"><tr><th> Type Name</th><th> Min Inventory Age (days) </th><th> Max Inventory Age (days) </th><th> Avg Inventory Age (days) </th></tr>';
	array_push($query_result, $tmp);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        $tmp = "<tr><td>".$row['type_name']."</td><td>".$row['min_age']."</td><td>". $row['max_age']."</td><td>".$row['avg_age']."</td></tr>";
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
    <h3>Inventory Age</h3>
    <a href="viewreports.php"><button class="btn">Back to Reports Page</button></a>
    <?php include("lib/error.php"); ?>
</div> <!-- .main_container -->
<?php } ?>
</body>
</html>