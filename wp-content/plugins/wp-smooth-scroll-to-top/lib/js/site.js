jQuery().ready(function(){jQuery(".wpsstt_btn").on("click",function(){jQuery("html,body").animate({scrollTop:0},"slow")})});jQuery(window).scroll(function(){if(jQuery(this).scrollTop()>50){jQuery(".wpsstt_autojs").fadeIn()}else{jQuery(".wpsstt_autojs").fadeOut()}})