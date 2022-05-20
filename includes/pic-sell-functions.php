<?php

function pic_esc_json( $json, $html = false ) {
	return _wp_specialchars(
		$json,
		$html ? ENT_NOQUOTES : ENT_QUOTES, // Escape quotes in attribute nodes only.
		'UTF-8',                           // json_encode() outputs UTF-8 (really just ASCII), not the blog's charset.
		true                               // Double escape entities: `&amp;` -> `&amp;amp;`.
	);
}

function pic_check_html($html){
        $start =strpos($html, '<');
        $end  =strrpos($html, '>',$start);
      
        $len=strlen($html);
      
        if ($end !== false) {
          $string = substr($html, $start);
        } else {
          $string = substr($html, $start, $len-$start);
        }
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xml = simplexml_load_string($string);
        if(count(libxml_get_errors())==0){
            return $html;
        }else{
            return "<p>HTML NON VALIDE</p>";
        }
        
    
}

?>