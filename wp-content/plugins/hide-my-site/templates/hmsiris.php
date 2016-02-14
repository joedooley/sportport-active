<?php
//to override this template, create a file named hmsiris.php within your active theme folder. you should probably copy and paste this file's code as a starting point.
?>

					<style>
						html, body {

							text-align: center;
							height: 100%;
						}
						body { background: url(<?php echo $this->plugin_dir ?>/images/iris_bg.jpg) center;font-family:Arial;margin:0px;}
						#form_wrap { background: url(<?php echo $this->plugin_dir ?>/images/login_bg_iris.png) no-repeat;display: block;margin: 0px auto;height: 300px;width: 403px; position: absolute;top: 50%;margin-top: -150px;left: 50%;margin-left: -201px; }
						#form_wrap input[type=text], .enter_password {background: url(<?php echo $this->plugin_dir ?>/images/input_back_iris.png) no-repeat; position: absolute;top: 92px;left: 26px;
							border: 0px;
							width: 365px;
							padding-left: 20px;
							font-size: 15px;
							line-height: 15px;
							padding-top: 0px;
							height:62px;
							color:rgb(85, 86, 90);
							opacity:.8;
						}

						#form_wrap input:active, #form_wrap input:focus {outline:0;opacity:1;}
						#form_wrap button {background: url(<?php echo $this->plugin_dir ?>/images/login_button_iris.png) no-repeat top; width: 117px;
							border: 0px;
							height: 46px;
							position: absolute;
							top: 164px;
							left: 20px;
							cursor:pointer; opacity:.9;
						}
						#form_wrap button:hover {opacity:1;}
						#form_wrap button:focus, #form_wrap button:active { opacity:1;}
						#the_hint_wrap {position: absolute;top: 165px;color: #999999;left: 152px;width: 231px;text-shadow: 1px 1px 1px white;font-weight: bold;font-family: Arial;text-align: left;font-size: 14px;overflow: hidden;max-height: 50px;}
						#the_hint_wrap div {display:inline-block;vertical-align: top;}
						#the_hint_title {padding-right:5px;}
						#the_hint {width: 235px;}
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