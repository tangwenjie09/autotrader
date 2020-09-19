<?php
include('lib/common.php');
// written by GTusername1

if($showQueries){
  array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

$username = $_SESSION['username'];

$query = "SELECT
			  VehicleType.type_name, 
			  IFNULL(AVG(ExcellentPrice.purchase_price),'0') AS avg_excellent_purchase,
			  IFNULL(AVG(VeryGoodPrice.purchase_price),'0') AS avg_verygood_purchase,
			  IFNULL(AVG(GoodPrice.purchase_price),'0') AS avg_good_purchase,
			  IFNULL(AVG(FairPrice.purchase_price),'0') AS avg_fair_purchase
			FROM
			VehicleType LEFT OUTER JOIN
			    (SELECT
			      vtype, purchase_price
			      FROM Vehicle
			      WHERE vcondition = 'Excellent') ExcellentPrice
			ON VehicleType.type_name = ExcellentPrice.vtype
			LEFT OUTER JOIN
			    (SELECT
			      vtype, purchase_price
			      FROM Vehicle
			      WHERE vcondition = 'Very Good') VeryGoodPrice
			ON VehicleType.type_name = VeryGoodPrice.vtype
			LEFT OUTER JOIN
			    (SELECT
			      vtype, purchase_price
			      FROM Vehicle
			      WHERE vcondition = 'Good') GoodPrice
			ON VehicleType.type_name = GoodPrice.vtype
			LEFT OUTER JOIN
			    (SELECT
			      vtype, purchase_price
			      FROM Vehicle
			      WHERE vcondition = 'Fair') FairPrice
			ON VehicleType.type_name = FairPrice.vtype
			GROUP BY VehicleType.type_name; ";

$result = mysqli_query($db, $query);
include('lib/show_queries.php');
$count = mysqli_num_rows($result); 

if (!empty($result) && ($count > 0) ) {
	$tmp = '<div class="table-responsive"><table class="table table-striped"><tr><th> Type Name</th><th> Excellent Avg purchase price</th><th> Very Good Avg purchase price </th><th> Good Avg purchase price </th><th> Fair Avg purchase price </th></tr>';
	array_push($query_result, $tmp);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        $tmp = "<tr><td>".$row['type_name']."</td><td>$".$row['avg_excellent_purchase']."</td><td>$". $row['avg_verygood_purchase']."</td><td>$".$row['avg_good_purchase']."</td><td>$".$row['avg_fair_purchase']."</td></tr>";
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
    <h3>Price per Condition</h3>
    <a href="viewreports.php"><button class="btn">Back to Reports Page</button></a>
    <?php include("lib/error.php"); ?>
</div> <!-- .main_container -->
<?php } ?>
</body>
</html>