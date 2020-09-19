    <div class="top-btn right">
      <?php if(!$_SESSION['username']) { ?>
      <button class="btn "><a href="login.php">Login</a></button>
      <?php } ?>
      <?php if($_SESSION['is_inventorycleck']&&$_SESSION['is_salespeople']&&$_SESSION['is_manager']) { 
        echo "<div class='badge badge-warning'>Owner</div>";
      }else if($_SESSION['is_inventorycleck']) { 
        echo "<div class='badge badge-warning'>Inventory Clerk</div>";
      } else if($_SESSION['is_salespeople']) { 
        echo "<div class='badge badge-warning'>Salespeople</div>";
      } else if($_SESSION['is_manager']) { 
        echo "<div class='badge badge-warning'>Manager</div>";
      }
      ?>
      <?php if($_SESSION['username']) { echo "<div class='label label-info'>".$_SESSION['username']."</div>";?>
        <button class="btn"><a href="logout.php">Logout</a></button>
      <?php } ?>
      <button class="btn"><a href="index.php">Home</a></button>
    </div>  <!-- .login-btn -->

