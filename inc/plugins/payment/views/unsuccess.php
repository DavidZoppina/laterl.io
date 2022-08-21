<?php include 'header.php';?>

<div class="bg bg-danger">
	
</div>

<div class="wrapper">
	
	<div class="header">
		
		<div class="title"><i class="fas fa-sad-tear fs-70"></i></div>
		<div class="lable"><?php _e("Payment unsuccessfull")?></div>
	</div>

	<div class="payment-info p-t-50 p-b-50">
		<p class="title fs-20 fw-6 text-danger"><?php _e('The payment could not be completed')?>!</p>
		<p><?php _e("An error occurred during checkout. Please try again")?></p>

		<a href="<?php _e( get_url("pricing") )?>" class="btn btn-dark"><?php _e("Try to again")?></a>
	</div>
</div>
    
<?php include 'footer.php';?>
