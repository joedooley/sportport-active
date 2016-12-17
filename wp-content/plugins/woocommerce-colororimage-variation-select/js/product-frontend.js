jQuery(document).ready(function($) {
        
	    if (wcva.tooltip == "yes") {
		    $('.swatchinput label').powerTip();
	    }
      
	   
     
	    $('form.variations_form').on( 'click', '.swatchinput label', function() {
		    var selectid           = $(this).attr("selectid");
            var dataoption         = $(this).attr("data-option");
		    var attributeindex     = $(this).closest('.attribute-swatch').attr('attribute-index');
		    
		    if (wcva.quick_view == "off") {
			    if ($(this).hasClass('selectedswatch')) {
				
				$(this).removeClass('selectedswatch').addClass('wcvaswatchlabel');
				
				var currentoptionToSelect = parent.jQuery("form.variations_form #" + selectid + "").children("[value='']");

               //mark the option as selected
                currentoptionToSelect.prop("selected", "selected").change();
				
				return;
			   }
		    }
		    
		  
	      
		  
		   $( this ).closest('.attribute-swatch').find('.selectedswatch').removeClass('selectedswatch').addClass('wcvaswatchlabel');
	       $( this ).removeClass('wcvaswatchlabel').addClass( 'selectedswatch' );
		  
		  
		  
           //find the option to select
           var optionToSelect = parent.jQuery("form.variations_form #" + selectid + "").children("[value='" + dataoption + "']");

           //mark the option as selected
           optionToSelect.prop("selected", "selected").change();
		 
					    
		});	   
		
		
		if (wcva.disable_options == "yes") {
			 		
			$('form.variations_form').on( 'change', '.variations select', function( event ) {
			
			jQuery('form.variations_form').find( '.variations select' ).each( function( i, e ) {
				
				var eachselect = jQuery( e );
				
				var availableoptions = [];
				
				jQuery(e).trigger('focusin');
				
				jQuery(eachselect).find('option').each(function(index,element){
					
					var isDisabled = $(element).is(':disabled');
					
					if(!isDisabled){
						
						availableoptions.push(element.value);
						
					}
				});
				
				var wcvalabel = jQuery(this).closest('td').find('.swatchinput label');
				
				jQuery(wcvalabel).each(function(){
					var dataoption = jQuery(this).attr("data-option");
					
					if(jQuery.inArray( dataoption, availableoptions ) < 0){
						
						if ($(this).hasClass('selectedswatch')) {
						   jQuery(this).removeClass('selectedswatch').addClass('wcvaswatchlabel');
		                   jQuery(this).addClass('wcvadisabled');
						} else {
						   jQuery(this).addClass('wcvadisabled');
						}
						
					}else{
						
						jQuery(this).removeClass('wcvadisabled');
					}
				});
			   })
		     })
		}
	   
	   
	 

      
       
        $('form.variations_form').on( 'click', '.reset_variations', function() {
			
			$('form.variations_form').find('.selectedswatch').removeClass('selectedswatch').addClass('wcvaswatchlabel');
			
			if (wcva.disable_options == "yes") {
			  $('form.variations_form' ).find('.wcvadisabled').removeClass('wcvadisabled');
			}
			
		});
	   
});