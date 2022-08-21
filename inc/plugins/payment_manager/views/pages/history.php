<form class="" action="<?php _e( get_module_url("index/history") )?>">
	<div class="subheadline wrap-m">
		
		<div class="sh-main wrap-c">
			<div class="sh-title text-info fs-18 fw-5"><i class="fas fa-history"></i> <?php _e('Payment history')?></div>
		</div>
		<div class="sh-toolbar wrap-c">
			<div class="input-group box-search-one">
			  	<input type="text" class="form-control" name="k" placeholder="<?php _e('Search')?>" value="<?php _e( post("k") )?>">
			  	<div class="input-group-append" id="button-addon4">
			  		<button class="btn" type="submit"><i class="fa fa-search"></i></button>
			  	</div>
			</div>
		</div>

	</div>

	<div class="m-t-50 table-mod table-responsive">
		
		<table class="table">
			<thead>
				<tr>
					<th scope="col">
						<a href="javascript:void(0);"><?php _e('Type')?></a>
					</th>
					<th scope="col">
						<a href="javascript:void(0);"><?php _e('Email')?></a>
					</th>
					<th scope="col">
						<a href="javascript:void(0);"><?php _e('Fullname')?></a>
					</th>
					<th scope="col">
						<a href="javascript:void(0);"><?php _e('Package name')?></a>
					</th>
					<th scope="col">
						<a href="javascript:void(0);"><?php _e('Transaction ID')?></a>
					</th>
					<th scope="col">
						<a href="javascript:void(0);"><?php _e('Plan')?></a>
					</th>
					<th scope="col">
						<a href="javascript:void(0);"><?php _e('Amount')?> (<?php _e( get_option('payment_symbol', '$') )?>)</a>
					</th>
					<th scope="col">
						<a href="javascript:void(0);"><?php _e('Created')?></a>
					</th>
				</tr>
			</thead>
			<tbody>
			<?php if(!empty($result)){?>

				<?php foreach ($result as $key => $row): ?>
				<tr class="item">
					<td><?php _e( $row->type )?></td>
					<td><?php _e( $row->email )?></td>
					<td><?php _e( $row->fullname )?></td>
					<td><?php _e( $row->name )?></td>
					<td><?php _e( $row->transaction_id )?></td>
					<td><?php _e( $row->plan==2?"Anually":"Monthy" )?></td>
					<td><?php _e( $row->amount)?></td>
					<td><?php _e( datetime_show($row->created) )?></td>
				</tr>
				<tr class="spacer"></tr>
				<?php endforeach ?>

			<?php }else{?>
				<tr>
					<td colspan="10">
						<div class="empty m-t-50">
							<div class="icon"></div><br/>
						</div>	
					</td>
				</tr>
			<?php }?>
			</tbody>
		</table>

	</div>
</form>

<nav class="m-t-30">
<?php _e( $pagination, false)?>
</nav>