/**
* Image Model
*/
var EnviraGalleryImage = Backbone.Model.extend( {

    /**
    * Defaults
    * As we always populate this model with existing data, we
    * leave these blank to just show how this model is structured.
    */
    defaults: {
        'id':       '',
        'title':    '',
        'caption':  '',
        'alt':      '',
        'link':     '',  
    },

} );

/**
* Images Collection
* - Comprises of all images in an Envira Gallery
* - Each image is represented by an EnviraGalleryImage Model
*/
var EnviraGalleryImages = new Backbone.Collection;

/**
* Modal Window
*/
var EnviraGalleryModalWindow = new wp.media.view.Modal( {
    controller: {
        trigger: function() {

        }
    }
} );

/**
* View
*/
var EnviraGalleryView = wp.Backbone.View.extend( {

    /**
    * The Tag Name and Tag's Class(es)
    */
    tagName:    'div',
    className:  'edit-attachment-frame mode-select hide-menu hide-router',

    /**
    * Template
    * - The template to load inside the above tagName element
    */
    template:   wp.template( 'envira-meta-editor' ),

    /**
    * Events
    * - Functions to call when specific events occur
    */
    events: {
        'click .edit-media-header .left':               'loadPreviousItem',
        'click .edit-media-header .right':              'loadNextItem',

        'keyup input':                                  'updateItem', 
        'keyup textarea':                               'updateItem', 
        'change input':                                 'updateItem',
        'change textarea':                              'updateItem',
        'blur textarea':                                'updateItem',
        'change select':                                'updateItem', 

        'click .actions a.envira-gallery-meta-submit':  'saveItem',

        'keyup input#link-search':                      'searchLinks',
        'click div.query-results li':                   'insertLink',

        'click button.media-file':                      'insertMediaFileLink',
        'click button.attachment-page':                 'insertAttachmentPageLink',
    },

    /**
    * Initialize
    *
    * @param object model   EnviraGalleryImage Backbone Model
    */
    initialize: function( args ) {

        // Set some flags
        this.is_loading = false;
        this.collection = args.collection;
        this.child_views = args.child_views;
        this.attachment_id = args.attachment_id;
        this.attachment_index = 0;
        this.search_timer = '';

        // Get the model from the collection
        var count = 0;
        this.collection.each( function( model ) {
            // If this model's id matches the attachment id, this is the model we want
            if ( model.get( 'id' ) == this.attachment_id ) {
                this.model = model;
                this.attachment_index = count;
                return false;
            }

            // Increment the index count
            count++;
        }, this );

    },

    /**
    * Render
    * - Binds the model to the view, so we populate the view's fields and data
    */
    render: function() {

        // Get HTML
        this.$el.html( this.template( this.model.attributes ) );

        // If any child views exist, render them now
        if ( this.child_views.length > 0 ) {
            this.child_views.forEach( function( view ) {
                // Init with model
                var child_view = new view( {
                    model: this.model
                } );

                // Render view within our main view
                this.$el.find( 'div.addons' ).append( child_view.render().el );
            }, this );
        }

        // Set caption
        this.$el.find( 'textarea[name=caption]' ).val( this.model.get( 'caption' ) );

        // Init QuickTags on the caption editor
        // Delay is required for the first load for some reason
        setTimeout( function() {
            quicktags( {
                id:     'caption', 
                buttons:'strong,em,link,ul,ol,li,close' 
            } );
            QTags._buttonsInit();
        }, 500 );

        // Init Link Searching
        wpLink.init;

        // Enable / disable the buttons depending on the index
        if ( this.attachment_index == 0 ) {
            // Disable left button
            this.$el.find( 'button.left' ).addClass( 'disabled' );
        }
        if ( this.attachment_index == ( this.collection.length - 1 ) ) {
            // Disable right button
            this.$el.find( 'button.right' ).addClass( 'disabled' );
        }
        
        // Return
        return this;
        
    },

    /**
    * Tells the view we're loading by displaying a spinner
    */
    loading: function() {

        // Set a flag so we know we're loading data
        this.is_loading = true;

        // Show the spinner
        this.$el.find( '.spinner' ).css( 'visibility', 'visible' );

    },

    /**
    * Hides the loading spinner
    */
    loaded: function( response ) {

        // Set a flag so we know we're not loading anything now
        this.is_loading = false;

        // Hide the spinner
        this.$el.find( '.spinner' ).css( 'visibility', 'hidden' );

        // Display the error message, if it's provided
        if ( typeof response !== 'undefined' ) {
            alert( response );
        }

    },

    /**
    * Load the previous model in the collection
    */
    loadPreviousItem: function() {
        
        // Decrement the index
        this.attachment_index--;

        // Get the model at the new index from the collection
        this.model = this.collection.at( this.attachment_index );

        // Update the attachment_id
        this.attachment_id = this.model.get( 'id' );

        // Re-render the view
        this.render();

    },

    /**
    * Load the next model in the collection
    */
    loadNextItem: function() {

        // Increment the index
        this.attachment_index++;

        // Get the model at the new index from the collection
        this.model = this.collection.at( this.attachment_index );

        // Update the attachment_id
        this.attachment_id = this.model.get( 'id' );

        // Re-render the view
        this.render();

    },

    /**
    * Updates the model based on the changed view data
    */
    updateItem: function( event ) {

        // Check if the target has a name. If not, it's not a model value we want to store
        if ( event.target.name == '' ) {
            return;
        }

        // Update the model's value, depending on the input type
        if ( event.target.type == 'checkbox' ) {
            value = ( event.target.checked ? 1 : 0 );
        } else {
            value = event.target.value;
        }

        // Update the model
        this.model.set( event.target.name, value );

    },

    /**
    * Saves the image metadata
    */
    saveItem: function() {

        // Tell the View we're loading
        this.trigger( 'loading' );

        // Make an AJAX request to save the image metadata
        wp.media.ajax( 'envira_gallery_save_meta', {
            context: this,
            data: {
                nonce:     envira_gallery_metabox.save_nonce,
                post_id:   envira_gallery_metabox.id,
                attach_id: this.model.get( 'id' ),
                meta:      this.model.attributes,
            },
            success: function( response ) {

                // Tell the view we've finished successfully
                this.trigger( 'loaded loaded:success' );

                // Assign the model's JSON string back to the underlying item
                var item = JSON.stringify( this.model.attributes );
                jQuery( 'ul#envira-gallery-output li#' + this.model.get( 'id' ) ).attr( 'data-envira-gallery-image-model', item );

                // Show the user the 'saved' notice for 1.5 seconds
                var saved = this.$el.find( '.saved' );
                saved.fadeIn();
                setTimeout( function() {
                    saved.fadeOut();
                }, 1500 );

            },
            error: function( error_message ) {

                // Tell wp.media we've finished, but there was an error 
                this.trigger( 'loaded loaded:error', error_message );

            }
        } );

    },

    /**
    * Searches Links
    */
    searchLinks: function( event ) {


    },

    /**
    * Inserts the clicked link into the URL field
    */
    insertLink: function( event ) {


    
    },

    /**
    * Inserts the direct media link for the Media Library item
    *
    * The button triggering this event is only displayed if we are editing a
    * Media Library item, so there's no need to perform further checks
    */
    insertMediaFileLink: function( event ) {

        // Tell the View we're loading
        this.trigger( 'loading' );

        // Make an AJAX request to get the media link
        wp.media.ajax( 'envira_gallery_get_attachment_links', {
            context: this,
            data: {
                nonce:          envira_gallery_metabox.save_nonce,
                attachment_id:  this.model.get( 'id' ),
            },
            success: function( response ) {

                // Update model
                this.model.set( 'link', response.media_link );

                // Tell the view we've finished successfully
                this.trigger( 'loaded loaded:success' );

                // Re-render the view
                this.render();

            },
            error: function( error_message ) {

                // Tell wp.media we've finished, but there was an error 
                this.trigger( 'loaded loaded:error', error_message );

            }
        } );

    },

    /**
    * Inserts the attachment page link for the Media Library item
    *
    * The button triggering this event is only displayed if we are editing a
    * Media Library item, so there's no need to perform further checks
    */
    insertAttachmentPageLink: function( event ) {

        // Tell the View we're loading
        this.trigger( 'loading' );

        // Make an AJAX request to get the media link
        wp.media.ajax( 'envira_gallery_get_attachment_links', {
            context: this,
            data: {
                nonce:          envira_gallery_metabox.save_nonce,
                attachment_id:  this.model.get( 'id' ),
            },
            success: function( response ) {

                // Update model
                this.model.set( 'link', response.attachment_page );

                // Tell the view we've finished successfully
                this.trigger( 'loaded loaded:success' );

                // Re-render the view
                this.render();

            },
            error: function( error_message ) {

                // Tell wp.media we've finished, but there was an error 
                this.trigger( 'loaded loaded:error', error_message );

            }
        } );

    }

} );

/**
* Sub Views
* - Addons must populate this array with their own Backbone Views, which will be appended
* to the settings region
*/
var EnviraGalleryChildViews = [];

/**
* DOM
*/
jQuery( document ).ready( function( $ ) {

    // Populate the collection
    EnviraGalleryImagesUpdate();

    // Edit Image
    $( '#envira-gallery' ).on( 'click.enviraModify', '.envira-gallery-modify-image', function( e ) {

        // Prevent default action
        e.preventDefault();

        // Get the selected attachment
        var attachment_id = $( this ).parent().data( 'envira-gallery-image' );

        // Pass the collection of images for this gallery to the modal view, as well
        // as the selected attachment
        EnviraGalleryModalWindow.content( new EnviraGalleryView( {
            collection:     EnviraGalleryImages,
            child_views:    EnviraGalleryChildViews,
            attachment_id:  attachment_id,
        } ) );

        // Open the modal window
        EnviraGalleryModalWindow.open();

    } );

} );

/**
* Populates the EnviraGalleryImages Backbone collection
*
* Called when images are added, deleted or reordered
* Doesn't need to be called when an image is edited, as the model will be updated automatically in the collection
*/
function EnviraGalleryImagesUpdate() {

    // Clear the collection
    EnviraGalleryImages.reset();

    // Iterate through the gallery images in the DOM, adding them to the collection
    jQuery( 'ul#envira-gallery-output li.envira-gallery-image' ).each( function() {
        // Build an EnviraGalleryImage Backbone Model from the JSON supplied in the element
        var envira_gallery_image = jQuery.parseJSON( jQuery( this ).attr( 'data-envira-gallery-image-model' ) );

        // Add the model to the collection
        EnviraGalleryImages.add( new EnviraGalleryImage( envira_gallery_image ) );
    } );

}