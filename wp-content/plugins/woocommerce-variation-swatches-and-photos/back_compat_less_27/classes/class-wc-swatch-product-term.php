<?php

class WC_Product_Swatch_Term extends WC_Swatch_Term {

    protected $attribute_options;

    public function __construct($config, $option, $taxonomy, $selected = false) {
        global $woocommerce, $_wp_additional_image_sizes;
	
        $this->attribute_options = $attribute_options = $config->get_options();

        $this->taxonomy_slug = $taxonomy;
        if (taxonomy_exists($taxonomy)) {
            $this->term = get_term($option, $taxonomy);
            $this->term_label = $this->term->name;
            $this->term_slug = $this->term->slug;
	    $this->term_name = $this->term->name;
        } else {
            $this->term = false;
            $this->term_label = $option;
            $this->term_slug = $option;
        }
        
        $this->selected = $selected;

        $this->size = $attribute_options['size'];
        $the_size = isset($_wp_additional_image_sizes[$this->size]) ? $_wp_additional_image_sizes[$this->size] : $_wp_additional_image_sizes['shop_thumbnail'];
        if (isset($the_size['width']) && isset($the_size['height'])) {
            $this->width = $the_size['width'];
            $this->height = $the_size['height'];
        } else {
            $this->width = 32;
            $this->height = 32;
        }
	
        $key = md5( sanitize_title($this->term_slug) );
	$old_key = sanitize_title($this->term_slug);
	
	$lookup_key = '';
	if (isset($attribute_options['attributes'][$key])) {
		$lookup_key = $key;
	} elseif (isset($attribute_options['attributes'][$old_key])) {
		$lookup_key = $old_key;
	}
	
        $this->type = $attribute_options['attributes'][$lookup_key]['type'];

        if (isset($attribute_options['attributes'][$lookup_key]['image']) && $attribute_options['attributes'][$lookup_key]['image']) {
            $this->thumbnail_id = $attribute_options['attributes'][$lookup_key]['image'];
            $this->thumbnail_src = current(wp_get_attachment_image_src($this->thumbnail_id, $this->size));
	        $this->thumbnail_alt = trim( strip_tags( get_post_meta(  $this->thumbnail_id, '_wp_attachment_image_alt', true ) ) );
        } else {
            $this->thumbnail_src = apply_filters( 'woocommerce_placeholder_img_src', WC()->plugin_url() . '/assets/images/placeholder.png' );
        }

        $this->color = isset($attribute_options['attributes'][$lookup_key]['color']) ? $attribute_options['attributes'][$lookup_key]['color'] : '#FFFFFF;';
    }
}