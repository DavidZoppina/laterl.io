

<div class="m-b-40">
	<h5 class="fs-16 fw-4 text-info m-b-20"><i class="fas fa-caret-right"></i> <?php _e('Paypal recurring payment')?></h5>
  	<div class="form-group">
		<label for="status"><?php _e('Status')?></label>
		<div>
			<label class="i-radio i-radio--tick i-radio--brand m-r-10">
				<input type="radio" name="paypal_recurring_status" <?php _e( get_option('paypal_recurring_status', 0)  == 1?"checked":"" )?> value="1"> <?php _e('Enable')?>
				<span></span>
			</label>
			<label class="i-radio i-radio--tick i-radio--brand m-r-10">
				<input type="radio" name="paypal_recurring_status" <?php _e( get_option('paypal_recurring_status', 0)  == 0?"checked":"" )?> value="0"> <?php _e('Disable')?>
				<span></span>
			</label>
		</div>
	</div>
	<div class="alert alert-solid-brand">
		<span class="fw-6"><?php _e("Webhook URL:")?></span>
		<a href="<?php _e( get_url("paypal_recurring/webhook") )?>" target="_blank"><?php _e( get_url("paypal_recurring/webhook") )?></a><br/>
		<span class="fw-6"><?php _e("Required events:")?></span><span class="m-l-5">Payment sale completed, Billing subscription cancelled</span>
	</div>
	<div class="form-group">
        <label for="paypal_recurring_webhook_id"><?php _e('Webhook ID')?></label>
        <input type="text" class="form-control" id="paypal_recurring_webhook_id" name="paypal_recurring_webhook_id" value="<?php _e( get_option('paypal_recurring_webhook_id', '') )?>">
  	</div>
</div>