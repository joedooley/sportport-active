/**
* View
*/
var EnviraWooCommerceView = Backbone.View.extend( {

	/**
    * The Tag Name and Tag's Class(es)
    */
    tagName:    'div',
    className:  'envira-woocommerce',

    /**
    * Template
    * - The template to load inside the above tagName element
    */
    template:   wp.template( 'envira-meta-editor-woocommerce' ),

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
	
        // Set the template HTML
		this.$el.html( this.template( this.model.attributes ) );

        // If the model has a WooCommerce product defined, set it as the selected option
        var woocommerce_product = this.model.get( 'woocommerce_product' );
        if ( woocommerce_product > 0 ) {
            this.$el.find( 'select option[value="' + woocommerce_product + '"]' ).attr( 'selected', 'selected' );
        }

	    return this;
	
	}
    
} );

// Add the view to the EnviraGalleryChildViews, so that it's loaded in the modal
EnviraGalleryChildViews.push( EnviraWooCommerceView );