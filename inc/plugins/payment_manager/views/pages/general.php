<?php 
$total_by_day = $result->total_by_day;
$count_by_day = $result->count_by_day;
$recently_payments = $result->recently_payments;
$chart = $result->chart;
?>
<div class="subheadline wrap-m m-b-30">
    
    <div class="sh-main wrap-c">
        <div class="sh-title text-info fs-18 fw-5"><i class="far fa-chart-bar"></i> <?php _e('Payment report')?></div>
    </div>
    
</div>

<div class="m-t-10">
    
    <div class="row no-gutters widget-main m-b-30">
        <div class="col">
            <div class="widget-card p-20">
                <div class="widget-details m-b-0 wrap-m">
                    <div class="widget-info wrap-c">
                        <div class="widget-title"><?php _e("Earning today")?></div>
                        <div class="widget-desc"><?php _e("Total earning today")?></div>
                    </div>
                    <div class="widget-stats wrap-c text-success"><?php _e( get_option('payment_symbol', '$') )?><?php _e( $total_by_day->today )?></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="widget-card p-20">
                <div class="widget-details m-b-0 wrap-m">
                    <div class="widget-info wrap-c">
                        <div class="widget-title"><?php _e("Earning this month")?></div>
                        <div class="widget-desc"><?php _e("Total earning of this month")?></div>
                    </div>
                    <div class="widget-stats wrap-c text-warning"><?php _e( get_option('payment_symbol', '$') )?><?php _e( $total_by_day->month )?></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="widget-card p-20">
                <div class="widget-details m-b-0 wrap-m">
                    <div class="widget-info wrap-c">
                        <div class="widget-title"><?php _e("Earning this year")?></div>
                        <div class="widget-desc"><?php _e("Total earning of this year")?></div>
                    </div>
                    <div class="widget-stats wrap-c text-danger"><?php _e( get_option('payment_symbol', '$') )?><?php _e( $total_by_day->year )?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-md-4 m-b-25">
            <div class="card widget-chart-activity">
                <div class="card-body p-0">
                    <div class="chart-bg bg-info">
                        <div class="card-top wrap-m">
                            <h6 class="card-title wrap-c p-20"><i class="fas fa-caret-right p-r-5"></i> <?php _e('Payment history')?></h6>
                        </div>
                    </div>
                    
                    <div class="card-box p-l-20 p-r-20">
                        <div class="row">
                            <div class="col-6">
                                <div class="box-item">
                                    <span class="icon text-solid-info"><i class="fas fa-file-invoice"></i></span>
                                    <span class="title"><?php _e("Today")?></span>
                                    <span class="number"><?php _e( sprintf( __("%s payments") , $count_by_day->today) )?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="box-item">
                                    <span class="icon text-solid-success"><i class="fas fa-file-invoice"></i></span>
                                    <span class="title"><?php _e("This week")?></span>
                                    <span class="number"><?php _e( sprintf( __("%s payments") , $count_by_day->week) )?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="box-item">
                                    <span class="icon text-solid-warning"><i class="fas fa-file-invoice"></i></span>
                                    <span class="title"><?php _e("This month")?></span>
                                    <span class="number"><?php _e( sprintf( __("%s payments") , $count_by_day->month) )?></span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="box-item">
                                    <span class="icon text-solid-danger"><i class="fas fa-file-invoice"></i></span>
                                    <span class="title"><?php _e("This year")?></span>
                                    <span class="number"><?php _e( sprintf( __("%s payments") , $count_by_day->year) )?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8 m-b-25">
            <div class="card widget-payment-box">
                <div class="card-header wrap-m">
                    <div class="card-title wrap-c m-b-0 text-info"><i class="fas fa-caret-right p-r-5"></i> <?php _e('Recently payments')?></div>
                </div>
                <div class="card-body nicescroll overflow-hidden no-update">

                    <div class="widget-list">
                        <?php
                        if($recently_payments){
                            foreach ($recently_payments as $row) {?>
                            <div class="widget-item widget-item-3">
                                 <a href="#">
                                    <div class="icon"><img src="<?php _e( get_avatar($row->name) )?>"></div>
                                    <div class="content content-2">
                                        <div class="title"><?php _e( $row->name )?> <span class="small"><?php _e($row->email)?></span></div>
                                        <div class="desc"><?php _e( $row->transaction_id )?></div>
                                    </div>
                                </a>
                                    
                                <div class="widget-option">
                                    <span class="badge badge-info"><?php _e( get_option('payment_symbol', '$') )?><?php _e( $row->amount )?></span>
                                </div>
                            </div>
                        <?php }}?>
                    </div>

                </div>
            </div>
        </div>

    </div>

    <div class="card rounded">
        <div class="card-header wrap-m">
            <div class="card-title wrap-c text-info"><i class="fas fa-caret-right p-r-5"></i> <?php _e("Last 30 days")?></div>
        </div>
        <div class="card-body h-350">
            <canvas id="line-stacked-area" height="350"></canvas>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        setTimeout(function(){
            Core.lineChart(
                "line-stacked-area",
                <?php _e($chart->date)?>, 
                [
                    <?php _e($chart->value)?>
                ],
                [
                    "<?php _e('New payment')?>"
                ]
            );
        }, 300);
    });
</script>