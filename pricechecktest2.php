<!-- !IMPORTANT!
	Enter the location of your text file into $textFile.
	Should be just a text file with an iTunes ID on each line.

	Reads a text file that holds iTunes product ID's and then returns the item name, artwork, and price.
	Currently works for iTunes TV shows only.
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
<!-- Initialize some things in PHP -->
<?php
		$textFile = ''; //enter a text file location here
		$file = fopen($textFile, 'r');
		date_default_timezone_set('EST'); //set it to your local time (http://php.net/manual/en/timezones.php)
?>
    <title>iTunes Price Checker for <?= date('l j F Y') ?> </title>
</head>

<body>
	<table>
<?php
	/** PHP script going through the ID's in the text file and getting JSON data from iTunes' server **/

	$start_time = microtime(true); //!benchmarking

	while($id = fgets($file)) {	
		$start = microtime(true); //!benchmarking

		//used cURL to keep iTunes Search API happy with my scripted search
		$curl_session = curl_init();
		curl_setopt($curl_session, CURLOPT_URL, "https://itunes.apple.com/lookup?id=$id");
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		$content = curl_exec($curl_session); //JSON received
		curl_close($curl_session);
		
		//results array turned into a PHP array
		$content = json_decode($content);
		$parsed = $content->results["0"];
	
		//if the item exists by comparing object's resultCount value
		if ($content->resultCount == '1') {
			//Name, art, price
			$name = $parsed->collectionName;
			$art = $parsed->artworkUrl100;
	
			//if-then for HD collection pricing where applicable	
			if (is_null($parsed->collectionHdPrice))
				$price = $parsed->collectionPrice;
			else
				$price = $parsed->collectionHdPrice;		
?>
			<!-- Create iTunes result -->
			<tr>
				<img src="<?= $art ?>"/> <br>
				Name: <a href="https://itunes.apple.com/lookup?id=<?=$id?>"><?= $name ?></a> <br>
				Price: <?= $price ?> <br>
				<br>
			</tr>
<?php
		}
		else {
?>
			<tr>
				<br>
				Error. <?= $id ?> does not exist. <a href="https://itunes.apple.com/lookup?id=<?= $id ?>">Check for yourself.</a>
				<br>
			</tr>
<?php			
		}
		
		$end = microtime(true); //!benchmarking
		echo 'Time to retrieve: ', $end-$start, ' s', "<br>"; //!benchmarking
	}
	echo "<br>";
	$end_time = microtime(true); //!benchmarking
	echo 'Total time to retrieve: ', $end_time-$start_time, ' s', "<br>"; //!benchmarking
	
?>
	</table>
</body>

</html>