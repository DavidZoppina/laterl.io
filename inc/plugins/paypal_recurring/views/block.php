<?php if( get_option("paypal_recurring_status") ){?>
<a href="<?php _e( get_url( "paypal_recurring/index/".segment(3)."/".segment(4) ) )?>" class="payment-method-item">
	<div class="payment-logo"><img src="<?php _e( get_module_path($this, "../paypal_recurring/assets/img/logo.png") )?>"></div>
	<div class="payment-detail">
		<div class="title"><?php _e("Paypal")?></div>
		<div class="desc"><?php _e("Recurring payment")?></div>
	</div>
	<div class="payment-go"><i class="fas fa-chevron-right"></i></div>
</a>
<?php }?>