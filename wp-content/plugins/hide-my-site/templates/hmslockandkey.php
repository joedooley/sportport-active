<?php
//to override this template, create a file named hmslockandkey.php within your active theme folder. you should probably copy and paste this file's code as a starting point.
?>

					<style>
						html, body {

							text-align: center;
							height: 100%;
						}
						body { background: url(<?php echo $this->plugin_dir ?>/images/lockandkey_bg.jpg); font-family:Arial;}
						#form_wrap { background: url(<?php echo $this->plugin_dir ?>/images/login_bg_lockandkey.png) no-repeat;display: block;margin: 0px auto;height: 282px;width: 501px;position: relative;top: 50%;margin-top: -141px; }
						#form_wrap input[type=text], .enter_password {background: url(<?php echo $this->plugin_dir ?>/images/input_back_lockandkey.png) no-repeat; position: absolute;
top: 115px;
left: 180px;
border: 0px;
width: 313px;
padding-left: 11px;
font-size: 15px;
line-height: 15px;
padding-top: 9px;
height: 35px;
color: rgb(85, 86, 90);
opacity: .9;
						}

						#form_wrap input:active, #form_wrap input:focus {outline:0;opacity:1;}
						#form_wrap button {background: url(<?php echo $this->plugin_dir ?>/images/login_button_lockandkey.png) no-repeat top; width: 144px;
border: 0px;
height: 39px;
position: absolute;
top: 217px;
left: 318px;
cursor: pointer;
opacity: 0.8;
}
						#form_wrap button:hover {opacity:.9}
						#form_wrap button:focus, #form_wrap button:active { outline:0;}
						#form_wrap button:active { opacity:1;}
						#the_hint_wrap {position: absolute;top: 225px;color: #c17c22;left: 54px;width: 264px;font-weight: normal;font-family: Arial;text-align: left;font-size: 13px;overflow: hidden;max-height: 33px;}
						#the_hint_wrap div {display:inline-block;vertical-align: top;}
						#the_hint_title {padding-right:5px;}
						#the_hint {width: 169px;}
					</style>
						<!--[if IE]>
						<style>
						#form_wrap input[type=text], .enter_password {
						  line-height:25px;    /* adjust value */
						}
						</style>
						<![endif]-->
					<body>
                    	<?php echo $messagehtml ?>
						<div id='form_wrap'>
							<form method=post>
								<input type=password name='hwsp_motech' placeholder='Password' class='enter_password'>
								<?php echo $hinthtml ?>
								<button type=submit></button>
							</form>
						</div>
					</body>