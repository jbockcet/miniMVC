<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<title><?php echo SITE_NAME ?> <?php if( defined('SITE_TAG') ) echo ": " . SITE_TAG; ?> </title> 

		<?php if( defined('META_DESCRIPTION') ): ?>
		<meta name="description" content="<?php echo META_DESCRIPTION?>"/>
		<?php endif; ?>

		<?php if( defined('META_KEYWORDS') ): ?>
		<meta name="keywords" content="<?php echo META_KEYWORDS ?>"/>
		<?php endif; ?>

		<meta name="author" content="">

		<?php if( defined('DEFAULT_CSS')  ): ?>
		<?php foreach( explode( ",", DEFAULT_CSS ) as $src ): ?>
		<link type="text/css" rel="stylesheet" href="<?php echo DEFAULT_MEDIA_PATH . 'css/' . $src . '.css'; ?>"/>
		<?php endforeach; ?>
		<?php endif; ?>

		<?php if( defined('DEFAULT_JAVASCRIPT')  ): ?>
		<?php foreach( explode( ",", DEFAULT_JAVASCRIPT ) as $src ): ?>
		<script type = "text/javascript" src ='<?php echo DEFAULT_MEDIA_PATH . 'js/' . $src . '.js'; ?>'> 
		</script>
		<?php endforeach; ?>
		<?php endif; ?>

		<?php if( isset( $this -> analytics) )  echo ($this -> analytics -> track()); ?>

		<!-- Le HTML5 shim, for IE6-8 support of HTML elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<style type="text/css">
			/* Override some defaults */
			html, body {
				background-color: #eee;
			}
			body {
				padding-top: 40px; /* 40px to make the container go all the way to the bottom of the topbar */
			}
			.container > footer p {
				text-align: center; /* center align it with the container */
			}
			.container {
				width: 820px; /* downsize our container to make the content feel a bit tighter and more cohesive. NOTE: this removes two full columns from the grid, meaning you only go to 14 columns and not 16. */
			}

			/* The white background content wrapper */
			.content {
				background-color: #fff;
				padding: 20px;
				margin: 0 -20px; /* negative indent the amount of the padding to maintain the grid system */
				-webkit-border-radius: 0 0 6px 6px;
				-moz-border-radius: 0 0 6px 6px;
				border-radius: 0 0 6px 6px;
				-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
				-moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
				box-shadow: 0 1px 2px rgba(0,0,0,.15);
			}

			/* Page header tweaks */
			.page-header {
				background-color: #f5f5f5;
				padding: 20px 20px 10px;
				margin: -20px -20px 20px;
			}

			/* Give a quick and non-cross-browser friendly divider */
			.content .span4 {
				margin-left: 0;
				padding-left: 19px;
				border-left: 1px solid #eee;
			}

			.topbar .btn {
				border: 0;
			}

		</style>

		<!-- Le fav and touch icons -->
		<link rel="shortcut icon" href="media/img/favicon.ico">
		<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
		<link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
		<link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
	</head>
	<body>
		<div class="topbar">
			<div class="fill">
				<div class="container">
					<?php if ( defined('DEFAULT_LOGO_PATH') ): ?>
					<img class="brand" src="<?php echo DEFAULT_LOGO_PATH?>"/>
					<?php endif; ?>
					<a class="brand" href="?<?php echo DEFAULT_CONTROLLER?>"><?php echo SITE_NAME; ?></a>
					<ul class="nav">
						<?php if ( isset( $this -> menu -> nav ) ): ?>
						<?php foreach( $this -> menu -> nav as $name => $href): ?>
						<li class ="<?php if($href == URI) echo 'active';?>">
						<a href="?<?php echo $href ?>"><?php echo $name ?></a>
						</li>
						<?php endforeach; ?>
						<?php endif; ?>
					</ul>

					<?php if ( !isset( $_SESSION['logged_in'] ) ): ?>
					<form action="?user/login" method = "post" class="pull-right">
						<input name = "username" class="input-small" type="text" placeholder="Username">
						<input name = "password" class="input-small" type="password" placeholder="Password">
						<button class="btn" type="submit">Sign in</button>
					</form>
					<?php else: ?>
					<a class="pull-right btn danger" href="?user/logout">Log Out</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="container">

			<div class="content">
				<div class="page-header">
					<!--
					<h1><?php echo SITE_NAME; ?> <small><?php echo SITE_TAG; ?></small></h1>
					-->
				</div>
				<div class="row">

					<div class="<?php echo(isset($this -> menu -> sidebar)) ? 'span10' : 'span16' ?>">
						<?php require_once( SERVER_ROOT . DEFAULT_VIEW_PATH . $view . '.php'); ?>
					</div>

					<?php if((isset($this -> menu -> sidebar) )): ?>
					<div class="span4">
						<h5>NAVIGATION</h5>
						<?php foreach( $this -> menu -> sidebar as $name => $href): ?>
						<a href="?<?php echo $href ?>"><?php echo $name ?></a> <br/>
						<?php endforeach; ?>
					</div>
					<? endif;?>

				</div>
			</div>

			<footer>
			<p>(c) <a href='<?php echo COMPANY_WEBSITE; ?>'><?php echo COMPANY . ' - ' . date("Y"); ?></a></p>
			</footer>

		</div> <!-- /container -->
	</body>
<html>
