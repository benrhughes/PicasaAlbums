<?php
/*------------------------------------------------------------------------------
| Albums.php
|
| This script creates an 'index' page of albums from your PicasaWeb account.
| It's useful when using PicasaViewer or PicasaBox.
|
| This script includes some very simple formatting, which you may like to 
| discard.
|
| XML parsing logic from 
|	http://www.sitepoint.com/article/php-xml-parsing-rss-1-0
|
| For more info and updates see https://github.com/benrhughes/PicasaAlbums
|
| Created by Ben Hughes - benrhughes.com
| 16 July 2007
| Version 1.0
------------------------------------------------------------------------------*/

/*------------------------------------------------------------------------------
| INSTALLATION
|
| 1. Modify the user config section below
| 2. Upload to your web host
|
| That's it!
------------------------------------------------------------------------------*/

/*------------------------------------------------------------------------------
| USER CONFIGURATION START
------------------------------------------------------------------------------*/
$userid = "picasaviewer"; // Your Google user name

$target = "PicasaBox.php/?album="; //URL to pass the name of the album to for the links

/*------------------------------------------------------------------------------
| USER CONFIGURATION END
------------------------------------------------------------------------------*/

// *** Only modify past this point if you know what you're doing ***

$insideentry = false;
$tag = "";
$title = "";
$url = "";

// function to parse the start of an XML element
function startElement($parser, $name, $attrs) {
	global $insideentry, $tag, $title, $url;
	if ($insideentry) {
		$tag = $name;
		
		if ($name == "MEDIA:THUMBNAIL"){
			$url = $attrs["URL"];
		}
	} elseif ($name == "ENTRY") {
		$insideentry = true;
	}
}

// function to parse the end of an XML element
function endElement($parser, $name) {
	global $insideentry, $tag, $title, $url, $albums;
	if ($name == "ENTRY") {
		$albums[] = array($title, $url);
		//echo $title . ' ' . $url;
		$title = "";
		$url = "";
		$insideentry = false;
	}
}

// function to parse the contents of an XML element
function characterData($parser, $data) {
	global $insideentry, $tag, $title, $url;
	if ($insideentry) {
		if ($tag == "TITLE") {
			$title .= $data;
		}
	}
}

// Lets get started... 

// Create an XML parser, using the functions above
$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startElement", "endElement");
xml_set_character_data_handler($xml_parser, "characterData");

// The URL of the album feed
$feed = "http://picasaweb.google.com/data/feed/api/user/" . $userid . "?kind=album";

// Open the feed
$fp = fopen($feed,"r")
	or die("Error reading RSS data.");

// Parse the feed
while ($data = fread($fp, 4096))
	xml_parse($xml_parser, $data, feof($fp))
		or die(sprintf("XML error: %s at line %d", 
			xml_error_string(xml_get_error_code($xml_parser)), 
			xml_get_current_line_number($xml_parser)));
// Close the feed
fclose($fp);
xml_parser_free($xml_parser);


// Generate the HTML
$htmlout = '<html>';

$htmlout .= '<head>';
$htmlout .= '	<title>' . $userid. '\'s albums</title>';
$htmlout .= '	<style type="text/css">';
$htmlout .= '		body{ color: #333; font: 13px "Lucida Grande", Verdana, sans-serif;	}';
$htmlout .= 	'	.Album { width: 625px; background: #f5f5f5; padding: 5px; float:center;}';
$htmlout .= 	'	.AlbumHeader { text-align:center; padding-left:0px; }';
$htmlout .= 	'	.AlbumHeader h3 { font: normal 24px Arial, Helvetica, sans-serif; text-align: center; }';
$htmlout .= 	'	.AlbumHeader h4 { font: 16px Arial, Helvetica, sans-serif; color: #FF0084; color: #660033; text-align: center; }';
$htmlout .= 	'	.AlbumPhoto { background: #f5f5f5; margin-bottom: 10px;}';
$htmlout .= 	'	.AlbumPhoto p { float: center; font: Arial, Helvetica, sans-serif; padding: 0px; border: 0px; background: #fff; margin: 8px; text-align: center; }';
$htmlout .= 	'	.AlbumPhoto span { float: left; padding: 4px 4px 12px 4px; border: 1px solid #ddd; background: #fff; margin: 8px; }';
$htmlout .= 	'	.AlbumPhoto img { border: none; }';
$htmlout .= 	'</style>';
$htmlout .= '</head>';

$htmlout .= '<body>';
$htmlout .= 	'<div class="Album">';
$htmlout .= 	'	<div class="AlbumHeader">';
$htmlout .= 	'		<h3>Albums</h3>';
$htmlout .= 	'	</div>';
$htmlout .= 	'	<br clear=all>';
$htmlout .= 	'	<div class="AlbumPhoto">';


foreach($albums as $album)
{
	$htmlout .= '<span><a href="'. $target . $album[0] . '"><img src="' . $album[1] . '" border=0></a><p>' . $album[0] . '</p></span>';
}

$htmlout .= 	'	</div>';
$htmlout .= 	'	<br clear=all>';
$htmlout .= 	'</div>';

$htmlout .= '</body></html>';

// Return the html 
print $htmlout;
exit;

?>
