<?php include 'header.php';?>
<div class="bg">
	
</div>

<div class="wrapper">
	
	<div class="header">
		
		<div class="lable"><?php _e("Make a payment")?></div>
		<div class="title"><?php _e( sprintf(__("%s Plan"), $result->name) )?></div>
		<div class="desc"><?php _e($result->description)?></div>

	</div>

	<div class="payment-info">
		<div class="payment-change">
			<span class="name p-r-10"><?php _e("Monthly")?></span>
			<label class="i-switch i-switch--outline i-switch--info">
				<input type="checkbox" class="input-payment-change" data-url="<?php _e( get_url("payment/index/".segment(3)) )?>" <?php _e(segment(4)==2?"checked":"")?> value="1">
				<span></span>
			</label>
			<span class="name p-l-10"><?php _e("Annually")?></span>
		</div>
		<div class="desc"><?php _e("Total Payment")?></div>
		<div class="price m-b-10"><?php _e( get_option('payment_symbol', '$') )?><?php _e( $result->amount )?></div>
		<div class="clearfix"></div>
	</div>
	
	<?php _e( $counpon_view, false )?>

	<div class="payment-method">
		<div class="headline"><i class="fas fa-money-check"></i> <?php _e("Payment method")?></div>

		<?php 
        $CI = &get_instance();
        if(!empty($CI->payment_views)){?>

            <?php foreach ($CI->payment_views as $key => $value): ?>
                
                <?php _e( $value['content'], false )?>

            <?php endforeach ?>

        <?php }?>
	</div>

</div>
    
<?php include 'footer.php';?>
