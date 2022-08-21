<?php include 'header.php';?>

<div class="bg bg-success">
	
</div>

<div class="wrapper">
	
	<div class="header">
		
		<div class="title"><i class="fas fa-check-circle fs-70"></i></div>
		<div class="lable"><?php _e("Payment successfull")?></div>
	</div>

	<div class="payment-info p-t-50 p-b-50">
		<p class="title fs-20 fw-6 text-success"><?php _e('Thank you for your payment')?>!</p>
		<p><?php _e("Now everything is ready for you to use. Enjoy and start your plan.")?></p>

		<a href="<?php _e( get_url("dashboard") )?>" class="btn btn-dark"><?php _e("Go to Dashboard")?></a>
	</div>
</div>
    
<?php include 'footer.php';?>
