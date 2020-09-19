<?php
include('lib/common.php');
// written by GTusername1

if($showQueries){
  array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

$username = $_SESSION['username'];

$query = "SELECT AllCustomer.full_name,
			      IFNULL(SoldSummary.vehicle_num, 0) AS vehicle_num,
			      IFNULL(SoldSummary.avg_purchase_price, 0) AS avg_purchase_price,
			      IFNULL(SoldSummary.avg_repair_num, 0) AS avg_repair_num
			FROM
		    (SELECT customer_id, CONCAT(first_name, ' ', last_name) AS full_name FROM Individual
		        UNION
		    SELECT customer_id, business_name AS full_name FROM Business) AllCustomer
			LEFT OUTER JOIN
		    (SELECT v1.seller as customer_id,
		            COUNT(DISTINCT v1.vin) as vehicle_num,
		            SUM(v1.purchase_price)/COUNT(DISTINCT v1.vin) as avg_purchase_price,
		            SUM(RepairSummary.repair_num)/COUNT(DISTINCT v1.vin) as avg_repair_num
		        FROM Vehicle v1 LEFT OUTER JOIN
		            (SELECT vin, COUNT(*) as repair_num
		                   FROM Repair  
		                   GROUP By vin) RepairSummary
		        ON v1.vin = RepairSummary.vin
		        GROUP BY v1.seller) SoldSummary
			ON AllCustomer.customer_id = SoldSummary.customer_id
			ORDER BY SoldSummary.vehicle_num DESC, SoldSummary.avg_purchase_price ASC";

$result = mysqli_query($db, $query);
include('lib/show_queries.php');
$count = mysqli_num_rows($result); 

if (!empty($result) && ($count > 0) ) {
	$tmp = "<div class='table-responsive'><table class='table'><tr><th> Customer Full Name </th><th> # Vehicles Sold </th><th> Avg Purchase Price </th><th> Avg # of Repairs per Vehicle </th></tr>";
	array_push($query_result, $tmp);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        $tmp = "<tr><td>".$row['full_name']."</td><td>".$row['vehicle_num']."</td><td>". $row['avg_purchase_price']."</td><td class='avg_repair_num'>".$row['avg_repair_num']."</td></tr>";
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
    <h3>Seller History</h3>
    <a href="viewreports.php"><button class="btn">Back to Reports Page</button></a>
    <?php include("lib/error.php"); ?>
</div> <!-- .main_container -->
<?php } ?>

<!-- scripts jquery-->
<script>
$(document).ready(function(){
  $('.avg_repair_num').each(function(){
    if(parseFloat($(this).text()) >= 5){
      var selected_tr = $(this).closest('tr')
      selected_tr.css("background-color","red");  
    }
  });
});
</script>
</body>
</html>