<?php
include('lib/common.php');
// written by GTusername1

if (!isset($_SESSION['username'])) {
	header('Location: login.php');
	exit();
}

if($showQueries){
  array_push($query_msg, "showQueries currently turned ON, to disable change to 'false' in lib/common.php");
}

$username = $_SESSION['username'];
?>

<?php include("lib/header.php"); ?>
  <title>View Reports</title>
</head>
<body>
<div class="main_container">
	<?php if($_SESSION['is_manager']){ ?>
    <a href="sellerhistory.php"><button class="btn oneline">View Seller History</button></a>
    <a href="inventoryage.php"><button class="btn oneline">View Inventory Age</button> </a>
    <a href="avaragetimeininventory.php"><button class="btn oneline">View Average Time in Inventory</button> </a>
    <a href="pricepercondition.php"><button class="btn oneline">View Price Per Condition</button> </a>
    <a href="repairstatistics.php"><button class="btn oneline">View Repair Statistics</button> </a>
    <a href="monthlysales.php"><button class="btn oneline">View Monthly Sales </button></a>
<?php } ?>
</div> <!-- .main_container -->

</body>
</html>