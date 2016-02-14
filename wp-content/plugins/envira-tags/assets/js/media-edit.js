/**
* View
*/
var EnviraTagsView = Backbone.View.extend( {

	/**
    * The Tag Name and Tag's Class(es)
    */
    tagName:    'div',
    className:  'envira-tags',

    /**
    * Template
    * - The template to load inside the above tagName element
    */
    template:   wp.template( 'envira-meta-editor-tags' ),

    /**
    * Events
    */
    events: {
        'click p.the-tagcloud a': 'insertTag',
    },

    /**
    * Initialize
    */
    initialize: function( args ) {

        this.model = args.model;

    },

    /**
    * Render
    */
    render: function() {
	
		this.$el.html( this.template( this.model.attributes ) );
	    return this;
	
	},

    /**
    * Insert Tag
    */
    insertTag: function( event ) {

        // Get the chosen tag and the current tags
        var tag = jQuery( event.target ).text(),
            tags = this.$el.find( 'input[name=tags]' ).val();

        // Update the tags field
        this.$el.find( 'input[name=tags]' ).val( tags + ', ' + tag );

        // Trigger a change event on the input field so the mode updates
        this.$el.find( 'input[name=tags]' ).trigger( 'change' );
        
    }
    
} );

// Add the view to the EnviraGalleryChildViews, so that it's loaded in the modal
EnviraGalleryChildViews.push( EnviraTagsView );