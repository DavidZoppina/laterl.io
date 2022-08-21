<div class="m-b-40">
	<h5 class="fs-16 fw-4 text-info m-b-20"><i class="fas fa-caret-right"></i> <?php _e('Stripe recurring payment')?></h5>
  	<div class="form-group">
		<label for="status"><?php _e('Status')?></label>
		<div>
			<label class="i-radio i-radio--tick i-radio--brand m-r-10">
				<input type="radio" name="stripe_recurring_status" <?php _e( get_option('stripe_recurring_status', 0)  == 1?"checked":"" )?> value="1"> <?php _e('Enable')?>
				<span></span>
			</label>
			<label class="i-radio i-radio--tick i-radio--brand m-r-10">
				<input type="radio" name="stripe_recurring_status" <?php _e( get_option('stripe_recurring_status', 0)  == 0?"checked":"" )?> value="0"> <?php _e('Disable')?>
				<span></span>
			</label>
		</div>
	</div>
	<div class="alert alert-solid-brand">
		<span class="fw-6"><?php _e("Webhook URL:")?></span>
		<a href="<?php _e( get_url("paypal/webhook") )?>" target="_blank"><?php _e( get_url("stripe_recurring/webhook") )?></a><br/>
		<span class="fw-6"><?php _e("Required events:")?></span><span class="m-l-5">invoice.payment_succeeded, customer.subscription.deleted</span>
	</div>
	<div class="form-group">
        <label for="stripe_recurring_webhook_id"><?php _e('Signing secret')?></label>
        <input type="text" class="form-control" id="stripe_recurring_webhook_id" name="stripe_recurring_webhook_id" value="<?php _e( get_option('stripe_recurring_webhook_id', '') )?>">
  	</div>
</div>