<div class="input-group box-search-one">
  	<input class="form-control search-input" type="text" value="" autocomplete="off" placeholder="<?php _e('Search')?>">
  	<span class="input-group-append">
	    <button class="btn" type="button">
	        <i class="fa fa-search"></i>
	    </button>
	</span>
</div>

<div class="widget">
	
	<div class="widget-items search-list">
		<div class="widget-item search-item wrap-m <?php _e( segment(2) == ""?"active":"" ) ?> ">
			<a 
				class="actionItem" 
				data-result="html" 
				data-content="column-two" 
				href="<?php _e( get_module_url() )?>" 
				data-history="<?php _e( get_module_url() )?>"
			>
				<span class="widget-section">
					<span class="widget-icon"><i class="far fa-chart-bar"></i></span>
					<span class="widget-desc"><?php _e("Payment report")?></span>
				</span>
			</a>
		</div>
		<div class="widget-item search-item wrap-m ">
			<a 
				class="actionItem" 
				data-result="html" 
				data-content="column-two" 
				href="<?php _e( get_module_url('index/history') )?>" 
				data-history="<?php _e( get_module_url('index/history') )?>"
			>
				<span class="widget-section">
					<span class="widget-icon"><i class="fas fa-history"></i></span>
					<span class="widget-desc"><?php _e("Payment history")?></span>
				</span>
			</a>
		</div>
	</div>

</div>