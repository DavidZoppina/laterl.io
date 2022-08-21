<div class="m-b-40">
	<h5 class="fs-16 fw-4 text-info m-b-20"><i class="fas fa-caret-right"></i> <?php _e('Paypal one-time payment')?></h5>
  	<div class="form-group">
		<label for="status"><?php _e('Status')?></label>
		<div>
			<label class="i-radio i-radio--tick i-radio--brand m-r-10">
				<input type="radio" name="paypal_status" <?php _e( get_option('paypal_status', 0)  == 1?"checked":"" )?> value="1"> <?php _e('Enable')?>
				<span></span>
			</label>
			<label class="i-radio i-radio--tick i-radio--brand m-r-10">
				<input type="radio" name="paypal_status" <?php _e( get_option('paypal_status', 0)  == 0?"checked":"" )?> value="0"> <?php _e('Disable')?>
				<span></span>
			</label>
		</div>
	</div>
  	<div class="form-group">
        <label for="paypal_client_id"><?php _e('Client ID')?></label>
        <input type="text" class="form-control" id="paypal_client_id" name="paypal_client_id" value="<?php _e( get_option('paypal_client_id', '') )?>">
  	</div>
  	<div class="form-group">
        <label for="paypal_client_secret"><?php _e('Client secret key')?></label>
        <input type="text" class="form-control" id="paypal_client_secret" name="paypal_client_secret" value="<?php _e( get_option('paypal_client_secret', '') )?>">
  	</div>
</div>