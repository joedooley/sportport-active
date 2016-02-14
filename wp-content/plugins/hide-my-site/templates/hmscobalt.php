<?php
//to override this template, create a file named hmscobalt.php within your active theme folder. you should probably copy and paste this file's code as a starting point.
?>

					<style>
						html, body {

							text-align: center;
							height: 100%;
						}
						body { background: url(<?php echo $this->plugin_dir ?>/images/cobalt_bg.jpg); font-family:Arial;}
						#form_wrap { background: url(<?php echo $this->plugin_dir ?>/images/login_bg_cobalt.png) no-repeat;display: block;
margin: 0px auto;
height: 600px;
width: 584px;
position: relative;
top: 0px;
margin-top: -51px;
}
						#form_wrap input[type=text], .enter_password {background: url(<?php echo $this->plugin_dir ?>/images/input_back_cobalt.png) no-repeat; position: absolute;
top: 297px;
left: 160px;
border: 0px;
width: 277px;
padding-left: 18px;
font-size: 20px;
line-height: 21px;
padding-top: 9px;
height: 52px;
color: rgb(85, 86, 90);
opacity: .8;
						}

						#form_wrap input:active, #form_wrap input:focus {outline:0;opacity:1;}
						#form_wrap button {background: url(<?php echo $this->plugin_dir ?>/images/login_button_cobalt.png) no-repeat top; width: 316px;
border: 0px;
height: 50px;
position: absolute;
top: 408px;
left: 140px;
cursor: pointer;
opacity: .9;
						}
						#form_wrap button:hover {opacity:1}
						#form_wrap button:focus, #form_wrap button:active { outline: 0;}
						#form_wrap button:active {margin-top: 1px;margin-left: 1px;opacity:1;}
						#the_hint_wrap {position: absolute;top: 360px;color: #1e4168;left: 168px;width: 291px;font-weight: normal;font-family: Arial;text-align: left;font-size: 12px;overflow: hidden;max-height: 33px;}
						#the_hint_wrap div {display:inline-block;vertical-align: top;}
						#the_hint_title {padding-right:5px;}
						#the_hint {width: 172px;}
					</style>
						<!--[if IE]>
						<style>
						#form_wrap input[type=text], .enter_password {
						  line-height:40px;    /* adjust value */
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