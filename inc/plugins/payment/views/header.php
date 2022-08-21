<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php _e( get_option('website_title', 'Stackposts - Social Marketing Tool') )?></title>
    <meta name="description" content="<?php _e( get_option('website_desc', '#1 Marketing Platform for Social Network') )?>">
    <meta name="keywords" content="<?php _e( get_option('website_keywords', 'social network, marketing, brands, businesses, agencies, individuals') )?>">
    <link rel="icon" type="image/png" href="<?php _e( get_option('website_favicon', get_url("inc/themes/backend/default/assets/img/favicon.png")) )?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">

	<!--Css-->
    <link rel="stylesheet" type="text/css" href="<?php _e( get_module_path($this, 'assets/fonts/awesome/awesome.css') )?>">
    <link rel="stylesheet" type="text/css" href="<?php _e( get_module_path($this, 'assets/fonts/flags/flag-icon.css') )?>">
    <link rel="stylesheet" type="text/css" href="<?php _e( get_module_path($this, 'assets/plugins/bootstrap/css/bootstrap.min.css') )?>">
    <link rel="stylesheet" type="text/css" href="<?php _e( get_module_path($this, 'assets/plugins/izitoast/css/izitoast.css') )?>">
    <link rel="stylesheet" type="text/css" href="<?php _e( get_module_path($this, 'assets/css/reset.css') )?>">
	<link rel="stylesheet" type="text/css" href="<?php _e( get_module_path($this, 'assets/css/style.css') )?>">
	<!--End Css-->

	<!--Jquery-->
	<script type="text/javascript" src="<?php _e( get_module_path($this, 'assets/plugins/jquery/jquery.min.js') )?>"></script>
	<!---End Jquery-->

	<script type="text/javascript">
        var token = '<?php _e( $this->security->get_csrf_hash() )?>',
            PATH  = '<?php _e(PATH)?>',
            BASE  = '<?php _e(BASE)?>';

        document.onreadystatechange = function () {
            var state = document.readyState
            if (state == 'complete') {
                setTimeout(function(){
                    document.getElementById('interactive');
                    document.getElementById('loading-overplay').style.opacity ="0";
                },500);

                setTimeout(function(){
                    document.getElementById('loading-overplay').style.display ="none";
                    document.getElementById('loading-overplay').style.opacity ="1";
                },1000);
            }
        }
    </script>

</head>

<body class="<?php _e( segment(1) )?>">
    <div class="loading-overplay" id="loading-overplay"><div class='loader loader1'><div><div><div><div></div></div></div></div></div></div>