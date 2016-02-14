<?php

	//Strip content of AutoP
	function gc_strip_autop($content){
		$content = do_shortcode(shortcode_unautop( $content ));
		$content = preg_replace('#^<\/p>|^<br \/>|<p>$#', '', $content);
		return $content;
	}
	
	//Column Shortcodes
	
	function gc_one_half_first($atts, $content = null ) {
	return '<div class="one-half first">'.gc_strip_autop($content).'</div>';
	}
	add_shortcode("one-half-first","gc_one_half_first");

	function gc_one_half($atts, $content = null) {
	return '<div class="one-half">'.gc_strip_autop($content).'</div>';
	}
	add_shortcode('one-half','gc_one_half');

	function gc_one_third_first($atts, $content = null) {
	return '<div class="one-third first">' .gc_strip_autop($content). '</div>';
	}
	add_shortcode("one-third-first","gc_one_third_first");

	function gc_one_third($atts, $content = null) {
	return '<div class="one-third">' .gc_strip_autop($content). '</div>';
		}
	add_shortcode('one-third','gc_one_third');

	function gc_one_fourth_first($atts, $content = null) {
	return '<div class="one-fourth first">' .gc_strip_autop($content). '</div>';
	}
	add_shortcode("one-fourth-first","gc_one_fourth_first");

	function gc_one_fourth($atts, $content = null) {
	return '<div class="one-fourth">' .gc_strip_autop($content). '</div>';
	}
	add_shortcode('one-fourth','gc_one_fourth');

	function gc_one_fifth_first($atts, $content = null) {
	return '<div class="one-fifth first">' .gc_strip_autop($content). '</div>';
	}
	add_shortcode("one-fifth-first","gc_one_fifth_first");

	function gc_one_fifth($atts, $content = null) {
	return '<div class="one-fifth">' .gc_strip_autop($content). '</div>';
	}
	add_shortcode('one-fifth','gc_one_fifth');

	function gc_one_sixth_first($atts, $content = null) {
	return '<div class="one-sixth first">' .gc_strip_autop($content). '</div>';
	}
	add_shortcode("one-sixth-first","gc_one_sixth_first");

	function gc_one_sixth($atts, $content = null) {
	return '<div class="one-sixth">' .gc_strip_autop($content). '</div>';
	}
	add_shortcode('one-sixth','gc_one_sixth');
	
	function gc_clear($atts) {
	return '<div class="clear"></div>';
	}
	add_shortcode('clear','gc_clear');
	
	function gc_clear_line($atts) {
	return '<div class="clear-line"></div>';
	}
	add_shortcode('clear-line','gc_clear_line');