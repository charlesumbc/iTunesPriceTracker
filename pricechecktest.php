<!-- Example parsing relevant information for Adventure Time, Volume 1 -->

<?php
	$context = stream_context_create(array('http' => array('header'=>'Connection: close')));
	$content = file_get_contents("https://itunes.apple.com/lookup?id=361706312");
	$content = json_decode($content);
	$parsed = $content->results["0"];
	
/* 	var_dump($parsed); */

	$name = $parsed->collectionName;	
	$price = $parsed->collectionHdPrice;
	$art = $parsed->artworkUrl100;
	$type = $parsed->collectionType;
	
	date_default_timezone_set('EST');
/*
	echo "Prices for " . date('l j F Y') . "<br>";
	echo "Name: $name <br>";
	echo "Price: $price <br>";
	echo "Type: $collectionType <br>";
*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
    <title>iTunes Price Checker for <?= date('l j F Y') ?> </title>
</head>
<body>
	<img src="<?= $art ?>"/> <br>
	Name: <?= $name ?> <br>
	Price: <?= $price ?> <br>
</body>
</html>