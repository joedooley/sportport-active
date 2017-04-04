
	/*
 * Attaches the image uploader to the input field
 */
jQuery(document).ready(function($){
	
	$('#color-picker1,#text-color,#title-color,#overlay-color').wpColorPicker();
	 $('#chart-categories').select2(
	{

		 maximumSelectionLength: 100,
		 theme: "classic"
		
		} 
	 );


     $('._img_remove').on('click',  function(event) {

        var data=$(this).data('id'),
        placeholder=$(this).data('placeholder');
        if(data){
            $('input#'+data).val('');
            $(this).next('img').attr('src',placeholder);
            $(this).css({
                display: 'none',
            });

        }
         
     });
	 
	 $('select#position').change(function(){
		 
		var value=$(this).val();
		if(value=="tab"){
			$(".tab-or-modal").hide(500);
			
			} 
			else{
				
			$(".tab-or-modal").show(500);	
				}
		 });
	 
	 // Instantiates the variable that holds the media library frame.
    var meta_image_frame;
	$('#meta-image-button').click(function(e){
		 e.preventDefault();
        // If the frame already exists, re-open it.
        if ( meta_image_frame ) {
            meta_image_frame.open();
            return;
        }
 
        // Sets up the media library frame
        meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text:  meta_image.button },
            library: { type: 'image' }
        });
 
        // Runs when an image is selected.
        meta_image_frame.on('select', function(){
 
            // Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = meta_image_frame.state().get('selection').first().toJSON();
 
            // Sends the attachment URL to our custom image input field.
            $('#primary-chart-image').attr('value',media_attachment.id);
            $('.chart-0 ._img_remove').css({
                display: 'block',
            });
			$('#meta_img').attr("src",media_attachment.url);
			$('#meta_img').attr("width","auto");
			$('#meta_img').attr("height","180");
			$('.media-modal-icon').click();
			 
        });
 
        // Opens the media library frame.
        meta_image_frame.open();
		
    }); 
	
	var meta_image_frame_1;
	$('#meta-image-button-1').click(function(e){
		 e.preventDefault();
        // If the frame already exists, re-open it.
        if ( meta_image_frame_1 ) {
            meta_image_frame_1.open();
            return;
        }
 
        // Sets up the media library frame
        meta_image_frame_1 = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text:  meta_image.button },
            library: { type: 'image' }
        });
 
        // Runs when an image is selected.
        meta_image_frame_1.on('select', function(){
 
            // Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = meta_image_frame_1.state().get('selection').first().toJSON();
 
            // Sends the attachment URL to our custom image input field.
            $('#chart-image-1').attr('value',media_attachment.id);
            $('.chart-1 ._img_remove').css({
                display: 'block',
            });
			$('#meta_img_1').attr("src",media_attachment.url);
			$('#meta_img_1').attr("width","auto");
			$('#meta_img_1').attr("height","180");
			$('.media-modal-icon').click();
			 
        });
 
        // Opens the media library frame.
        meta_image_frame_1.open();
		
    }); 
	
	var meta_image_frame_2;
	$('#meta-image-button-2').click(function(e){
		 e.preventDefault();
        // If the frame already exists, re-open it.
        if ( meta_image_frame_2 ) {
            meta_image_frame_2.open();
            return;
        }
 
        // Sets up the media library frame
        meta_image_frame_2 = wp.media.frames.meta_image_frame = wp.media({
            title: meta_image.title,
            button: { text:  meta_image.button },
            library: { type: 'image' }
        });
 
        // Runs when an image is selected.
        meta_image_frame_2.on('select', function(){
 
            // Grabs the attachment selection and creates a JSON representation of the model.
            var media_attachment = meta_image_frame_2.state().get('selection').first().toJSON();
 
            // Sends the attachment URL to our custom image input field.
            $('#chart-image-2').attr('value',media_attachment.id);
            $('.chart-2 ._img_remove').css({
                display: 'block',
            });
			$('#meta_img_2').attr("src",media_attachment.url);
			$('#meta_img_2').attr("width","auto");
			$('#meta_img_2').attr("height","180");
			$('.media-modal-icon').click();
			 
        });
 
        // Opens the media library frame.
        meta_image_frame_2.open();
		
    }); 

    
});