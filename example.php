<?php
require("opencurl/opencurl.php");

// INIT OPENCURL
$curl = new Curl(array(
	"cookieFile"=>"democookie"
));

// -----------------------------------
// "GET" DEMO

$curl->get(array(
	"url"=>"http://google.com",
	"data" => array(),  			// optional, default null
	"hasFile" => false,  			// optional, default false
	"showHeaders" => true, 			// optional, default true
	"fresh" => false,  				// optional, default false
	"autofollow" => true,			// optional, default true
	"headers" => array()			// optional, default auto
));

// -----------------------------------
// "POST" DEMO WITH FILE UPLOAD

$filename = "exampleimage.jpg";

$curl->post(array(
	"url"=>"http://google.com",
	"hasFile" => true,
	"data" => array(
		"file" => "@".$filename;
	)
));

// -----------------------------------
// DEMO: READING RESPONSE DATA

$curl->get(array(
	"url"=>"http://google.com"
));

print_r( $curl->headers );		// array of received headers
echo $curl->html;				// string of curl response

// to traverse through html, load via simple_dom:

$htmlTree = str_get_html($html);
$googleInput = $htmlTree->find('input');

?>