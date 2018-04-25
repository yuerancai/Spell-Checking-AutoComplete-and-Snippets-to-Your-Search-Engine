<?php
include "simple_html_dom.php";

function excerpt($query, $id)
{
	$text = file_get_html($id)->plaintext;
	//words
	$words = join('|', explode(' ', preg_quote($query)));

	$s = '\s\x00-/:-@\[-`{-~'; 

	preg_match_all('#(?<=['.$s.']).{1,80}(('.$words.').{1,80})+(?=['.$s.'])#uis', $text, $matches, PREG_SET_ORDER);

	$snippet=$matches[0][0];

	$snippet = preg_replace('#'.$words.'#iu', "<span class=\"red\">\$0</span>", $snippet);

	return $snippet;
}
?>