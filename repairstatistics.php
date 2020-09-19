<?php
include('lib/common.php');
// written by GTusername1

if($showQueries){
  array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

$username = $_SESSION['username'];

$query = "SELECT vendor_name, COUNT(*) AS repair_num,
			  SUM(cost) AS repair_cost,
			  COUNT(*)/COUNT(DISTINCT vin) AS avg_repair_per_vehicle,
			  AVG(DATEDIFF(end_date,start_date)+1) AS avg_repair_length
			FROM Repair
			WHERE status = 'completed'
			GROUP BY vendor_name";

$result = mysqli_query($db, $query);
include('lib/show_queries.php');
$count = mysqli_num_rows($result); 

if (!empty($result) && ($count > 0) ) {
	$tmp = "<div class='table-responsive'><table class='table table-striped'><tr><th> Vendor name</th><th> # Repairs Completed </th><th> Total Cost of Completed Repairs </th><th> Avg # of Repairs per Vehicle Completed </th><th> Avg Length of Completed Repairs(days)</th></tr>";
	array_push($query_result, $tmp);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        $tmp = "<tr><td>".$row['vendor_name']."</td><td>".$row['repair_num']."</td><td>$".$row['repair_cost']."</td><td>".$row['avg_repair_per_vehicle']."</td><td>".$row['avg_repair_length']."</td></tr>";
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
    <h3>Repair Statistics</h3>
    <a href="viewreports.php"><button class="btn">Back to Reports Page</button></a>
    <?php include("lib/error.php"); ?>
</div> <!-- .main_container -->
<?php } ?>
</body>
</html>