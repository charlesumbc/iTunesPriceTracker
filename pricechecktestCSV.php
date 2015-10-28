<!--!IMPORTANT!
	Enter the location of your CSV file into $CSVFile.
	Should be just a one column CSV file with an iTunes ID on each row.
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
	<link type="text/css" rel="stylesheet" href="stylesheet.css"/>
	<!-- Initialize some things in PHP -->
	<?php
		date_default_timezone_set('EST');
	?>
    <title>iTunes Price Checker for <?= date('l j F Y') ?> </title>
</head>

<body>
<?php
	$CSVFile = ""; //--!!Enter your CSV file location here!!--

	$benchmarking_on = false; //enable/disable benchmarking
	$start_time = microtime(true); //for benchmarking
	
	/**
	 * Turn the CSV into an array
	 * $CSVFile - CSV file location
	 * returns an associatve array holding the IDs and prices from the CSV file
	 */
	function createArrayFromCSV($CSVFile)
	{
		$CSVArray = array();
		
		$file_handle = fopen($CSVFile, 'r');
		$line = fgetcsv($file_handle); //skip the first row/header row
		while (($line = fgetcsv($file_handle)) !== false) 
		{
			$CSVArray[$line[0]] = $line[1];
		}
		fclose($file_handle);
	
		return $CSVArray;
	}

	/**
	 * Creates a string of IDs to enter into iTunes Search API lookup
	 * $numIDs - number of IDs to put in the string (iTunes Search API likes grouping searches in one query)
	 * $CSVArray - array created from CSV file
	 * returns a string of $numIDs of IDs
	 */
	function createIDsString($numIDs, $CSVArray) {
		$keys = array_keys($CSVArray);
		foreach ($keys as $key) {
			$IDs = $IDs . "," . $key;
		}
		$IDs = substr($IDs, 1); //take off that first comma
		
		return $IDs;
	}

	/**
	 * Use cURL (Client URL Library) to get JSON objects from iTunes Search API
	 * $concatID - ID's to put into iTunes Search API URL
	 * returns JSON objects as an array
	 */
	function getJSONObjects($concatID) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://itunes.apple.com/lookup?id=$concatID");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$content = curl_exec($ch); //JSON received
		curl_close($ch);
		
		$content = json_decode($content); //decodes JSON string into an array
		
		return $content;
	}
	
	/**
	 * Creates the info card of a given iTunes ID
	 * $JSONResult - JSON objects to sort and break
	 * $oldPrice - old price retrieved from the CSVArray
	 */
	function createInfo($JSONResult, $oldPrice) {
		$id = $JSONResult->collectionId;
		$name = $JSONResult->collectionName;
		$art = $JSONResult->artworkUrl100;
		//if-then for HD collection pricing where applicable
		if (is_null($JSONResult->collectionHdPrice))
			$price = $JSONResult->collectionPrice;
		else
			$price = $JSONResult->collectionHdPrice;
		
		if ($oldPrice > $price) {
		?>
		<div class = "iTunesItemPriceDown">
			<img src="<?= $art ?>"/> <br>
			<a href="https://itunes.apple.com/lookup?id=<?=$id?>"><?= $name ?></a> <br>
			$<?= $oldPrice ?> -> $<?= $price ?> <br>
			<br>
		</div>
		<br>
		<?
		}
		else if ($oldPrice < $price) {
		?>
		<div class = "iTunesItemPriceUp">
			<img src="<?= $art ?>"/> <br>
			<a href="https://itunes.apple.com/lookup?id=<?=$id?>"><?= $name ?></a> <br>
			$<?= $oldPrice ?> -> $<?= $price ?> <br>
			<br>
		</div>
		<br>
		<?
		}
		else {
		?>
		<div class = "iTunesItem">
			<img src="<?= $art ?>"/> <br>
			<a href="https://itunes.apple.com/lookup?id=<?=$id?>"><?= $name ?></a> <br>
			$<?= $price ?> <br>
			<br>
		</div>
		<br>
		<?
		}			
	}
	
	$CSVArray = createArrayFromCSV($CSVFile);
	$IDString = createIDsString(50, $CSVArray);
	$searchResults = getJSONObjects($IDString);
	//TODO: resultCount can/will be different from how many ID's are in the string
	for ($i = 0; $i < $searchResults->resultCount; $i++) {
		createInfo($searchResults->results[$i], $CSVArray[$searchResults->results[$i]->collectionId]);
	}

	if ($benchmarking_on) {
		$end_time = microtime(true); //for benchmarking
		echo 'Total time to retrieve: ', $end_time - $start_time, ' s', "<br>"; //for benchmarking
	}	
?>
</body>

</html>
