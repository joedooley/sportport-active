<?php
//to override this template, create a file named hmsclassic.php within your active theme folder. you should probably copy and paste this file's code as a starting point.
?>

					<style>
						html, body {

							text-align: center;
							height: 100%;
						}
						body { background: url(<?php echo $this->plugin_dir ?>/images/bg_dot.png) #d6d9e2; font-family:Arial;}
						#form_wrap { background: url(<?php echo $this->plugin_dir ?>/images/login_bg.png) no-repeat;display: block;margin: 0px auto;height: 450px;width: 403px; position: relative;top: 50%;margin-top: -225px; }
						#form_wrap input[type=text], .enter_password {background: url(<?php echo $this->plugin_dir ?>/images/input_back.png) no-repeat; position: absolute;top: 159px;left: 50px;
							border: 0px;
							width: 313px;
							padding-left: 50px;
							font-size: 15px;
							line-height: 15px;
							padding-top: 9px;
							height:62px;
							color:rgb(85, 86, 90);
							opacity:.8;
						}

						#form_wrap input:active, #form_wrap input:focus {outline:0;opacity:1;}
						#form_wrap button {background: url(<?php echo $this->plugin_dir ?>/images/login_button.png) no-repeat top; width: 316px;
							border: 0px;
							height: 85px;
							position: absolute;
							top: 257px;
							left: 43px;
							cursor:pointer; opacity:.7;
						}
						#form_wrap button:hover {opacity:.8}
						#form_wrap button:focus, #form_wrap button:active { opacity:1;}
						#the_hint_wrap {position: absolute;top: 225px;color: white;left: 63px;width: 291px;text-shadow: 1px 1px 1px black;font-weight: normal;font-family: Arial;text-align: left;font-size: 14px;overflow: hidden;max-height: 33px;}
						#the_hint_wrap div {display:inline-block;vertical-align: top;}
						#the_hint_title {padding-right:5px;}
						#the_hint {width: 183px;}
					</style>
						<!--[if IE]>
						<style>
						#form_wrap input[type=text], .enter_password {
						  line-height:50px;    /* adjust value */
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