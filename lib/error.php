 
 	<?php if($error_msg) {  ?>
		<div class='error'>
			 <div class='error_msg alert alert-error' role="alert">
				<?php
					foreach ($error_msg as $error) {
						echo $error . NEWLINE;
					 }
				?>
			</div>
		</div>
	<?php  } ?>
	
    <?php if($query_msg) {  ?>
    <div class='query' >
         <div class='query_msg'>
            <?php
                foreach ($query_msg as $query) {
                    echo $query . NEWLINE;
                 }
            ?>
        </div>
    </div>
	<?php } ?>

	<?php if($query_result) {  ?>
    <div class='result'>
         <div class='query_result'>
            <?php
                foreach ($query_result as $query) {
                    echo $query;
                 }
            ?>
        </div>
    </div>
	<?php } ?>

    <?php if($drilldown_result) {  ?>
    <div class='drilldown'>
         <div class='drilldown_result'>
            <?php
                foreach ($drilldown_result as $query) {
                    echo $query;
                 }
            ?>
        </div>
    </div>
    <?php } ?>