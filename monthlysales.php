<?php
include('lib/common.php');
// written by GTusername1

if($showQueries){
  array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

$username = $_SESSION['username'];

$query = "SELECT
		    Year(sale_date) AS year, Month(sale_date) AS month,
		    Count(DISTINCT Sell.vin) AS sold_vehicle_number,
		    1.25*SUM(v1.purchase_price)+1.1*SUM(RepairSummary.repair_cost) as sales_income,
		    0.25*SUM(v1.purchase_price)+0.1*SUM(RepairSummary.repair_cost) as net_income
		FROM Vehicle v1
		LEFT OUTER JOIN
		    (SELECT v2.vin, IFNULL(SUM(cost),0) AS repair_cost
	            FROM Vehicle v2
	            LEFT OUTER JOIN Repair
	            ON v2.vin = Repair.vin
	            group by v2.vin) RepairSummary
		ON v1.vin = RepairSummary.vin
		INNER JOIN Sell
		ON v1.vin = Sell.vin
		GROUP By Year(sale_date), Month(sale_date)
		ORDER BY Year(sale_date) DESC, Month(sale_date) DESC;";

$result = mysqli_query($db, $query);
include('lib/show_queries.php');
$count = mysqli_num_rows($result); 

if (!empty($result) && ($count > 0) ) {
	$tmp = '<div class="table-responsive"><table class="table table-striped"><tr><th> Year</th><th> Month </th><th> total sales income </th><th> net income </th></tr>';
	array_push($query_result, $tmp);

    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        $tmp = "<tr class='month_row'><td class='year'>".$row['year']."</td><td class='month'>".$row['month']."</td><td>". $row['sales_income']."</td><td>".$row['net_income']."</td></tr>";
        // print "<p>{$tmp}</p>";
        array_push($query_result, $tmp);
    }
    array_push($query_result, '</table></div> <!--.table-responsive-->');
}

if($_SERVER['REQUEST_METHOD']=='POST') {
    $year = mysqli_real_escape_string($db, $_POST['year']);
    $month = mysqli_real_escape_string($db, $_POST['month']);
    $query = "SELECT LoginUser.first_name,LoginUser.last_name,
				    Count(DISTINCT Sell.vin) as num_of_sale,
				    1.25*SUM(purchase_price)+1.1*SUM(repair_cost) as total_sales
				FROM Sell INNER JOIN Vehicle
				ON Sell.vin = Vehicle.vin
				INNER JOIN LoginUser
				ON Sell.username = LoginUser.username
				LEFT OUTER JOIN
				    (SELECT v2.vin, IFNULL(SUM(cost),0) AS repair_cost
			            FROM Vehicle v2
			            LEFT OUTER JOIN Repair
			            ON v2.vin = Repair.vin
			            group by v2.vin) RepairSummary
				ON Sell.vin = RepairSummary.vin
				WHERE Year(sale_date) = '$year'
				    AND Month(sale_date) = '$month'
				GROUP By Sell.username
				ORDER BY num_of_sale DESC, total_sales DESC";
    $result = mysqli_query($db, $query);
    $count = mysqli_num_rows($result); 
    // echo $year;echo $month;

	if (!empty($result) && ($count > 0) ) {
		$tmp = "<h3>".$year."/".$month."</h3><div class='table-responsive'><table class='table'><tr><th> Salespeople First Name </th><th> Salespeople Last Name </th><th> # Vehicle Sold </th><th> Total Sales </th></tr>";
		array_push($drilldown_result, $tmp);

	    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
	        $tmp = "<tr><td>".$row['first_name']."</td><td>".$row['last_name']."</td><td>". $row['num_of_sale']."</td><td>".$row['total_sales']."</td></tr>";
	        // print "<p>{$tmp}</p>";
	        array_push($drilldown_result, $tmp);
	    }
	    array_push($drilldown_result, '</table></div> <!--.table-responsive--><button class="btn close_modal">Close</button>');
	}
}

?>

<?php include("lib/header.php"); ?>
  <title>View Reports</title>
</head>
<body>
<?php if($_SESSION['is_manager']){ ?>
<div class="main_container">
    <?php include("lib/topbar.php"); ?>
    <h3>Monthly Sales</h3>
    <a href="viewreports.php"><button class="btn">Back to Reports Page</button></a>
    <?php include("lib/error.php"); ?>

    <form name="monthly_sale_form" class="hide" method="post" action="monthlysales.php" enctype="multipart/form-data" autocomplete="off"> 
      <div class="form-group">
        <label>Year*</label>
        <input type="text" name="year" class="form-control" required>
      </div><!-- .form-group -->

      <div class="form-group">
        <label>Month*</label>
        <input type="text" name="month" class="form-control" required>
      </div><!-- .form-group -->

      <button type="submit" class="btn btn-default">Submit</button>
    </form>


</div> <!-- .main_container -->
<?php } ?>

<!-- scripts jquery-->
<script>
$(document).ready(function(){
	$(".month_row").click(function(){
		var year = $(this).find(".year").text();
		var month = $(this).find(".month").text();
		$('input[name="year"]').val(year);
		$('input[name="month"]').val(month);
		$('form[name="monthly_sale_form"]').submit();
	});

	$(".close_modal").click(function(){
		$(".drilldown_result").hide();
	});
});
</script>
</body>
</html>