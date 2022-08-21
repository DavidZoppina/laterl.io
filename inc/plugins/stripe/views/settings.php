<div class="m-b-40">
	<h5 class="fs-16 fw-4 text-info m-b-20"><i class="fas fa-caret-right"></i> <?php _e('Stripe one-time payment')?></h5>
  	<div class="form-group">
		<label for="status"><?php _e('Status')?></label>
		<div>
			<label class="i-radio i-radio--tick i-radio--brand m-r-10">
				<input type="radio" name="stripe_status" <?php _e( get_option('stripe_status', 0)  == 1?"checked":"" )?> value="1"> <?php _e('Enable')?>
				<span></span>
			</label>
			<label class="i-radio i-radio--tick i-radio--brand m-r-10">
				<input type="radio" name="stripe_status" <?php _e( get_option('stripe_status', 0)  == 0?"checked":"" )?> value="0"> <?php _e('Disable')?>
				<span></span>
			</label>
		</div>
	</div>
  	<div class="form-group">
        <label for="stripe_publishable_key"><?php _e('Publishable key')?></label>
        <input type="text" class="form-control" id="stripe_publishable_key" name="stripe_publishable_key" value="<?php _e( get_option('stripe_publishable_key', '') )?>">
  	</div>
  	<div class="form-group">
        <label for="stripe_secret_key"><?php _e('Secret key')?></label>
        <input type="text" class="form-control" id="stripe_secret_key" name="stripe_secret_key" value="<?php _e( get_option('stripe_secret_key', '') )?>">
  	</div>
</div>