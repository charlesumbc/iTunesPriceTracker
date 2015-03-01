<!--!IMPORTANT!
	Enter the location of your CSV file into $csvFile.
	Should be just a one column CSV file with an iTunes ID on each row.
	
	Broke up code into functions.
	I also learned about how globals work in PHP. Lesson learned.
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
	<!-- Initialize some things in PHP -->
	<?php
		date_default_timezone_set('EST');
	?>
    <title>iTunes Price Checker for <?= date('l j F Y') ?> </title>
</head>

<body>
	<table>
<?php
	//globals
	$csvFile = "/Users/chwang/GitHub Projects/iTunesPriceTracker/IDs.csv"; //enter a CSV file location here
	
	$start_time = microtime(true); //!benchmarking

	/**
	 * opens up a CSV file with a list of iTunes items and creates a string of IDs to enter into iTunes Search API lookup
	 * $numIDs - number of IDs to put in the string
	 * $readerLength - fgetcsv()'s length
	 * returns a string of $numIDs of IDs
	 */
	function createIDsString($numIDs, $readerLength) {
		global $csvFile;
		
		$database = fopen($csvFile, 'r');
		if ($database != FALSE) {
			$count = 0;
			$prevLine = 0;
			//?instead of 100, use some kind of const
			//this reads any column headers, if you decide to use column headers in your CSV file
/* 			while (($line = fgetcsv($handle, 100, ",")) != FALSE) { */
			//?instead of 50, use a const
				while (($count < $numIDs) && (($line = fgetcsv($database, $readerLength, ",")) != FALSE)) {
					if ($line[0] != '0') { //iTunes Search API returns 0 for nonexistent records, using 0 in my CSV for testing sake
						$ids = $ids . "," . $line[0];
						$count++;
						$prevLine = $line[0];
					}
					/*
					//Making sure 0's aren't read in.
					else {
						echo "Invalid ID: " . $line[0] . "<br>";
					}
					*/
				}
				$count = 0;
/* 			} */
			fclose($database);
		}
		else {
			echo "Failed to open file.";
		}
		
		//Long ID string
		$ids = substr($ids, 1); //take off that last comma
		//echo $ids, "<br>"; //!debug
		return $ids;
	}

	/**
	 * use cURL (Client URL Library) to get JSON objects from iTunes Search API
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
	 * creates the info card of a given iTunes ID
	 * $JSONResult - JSON objects to sort and break
	 */
	function createInfo($JSONResult) {		
		$name = $JSONResult->collectionName;
		$art = $JSONResult->artworkUrl100;
		//if-then for HD collection pricing where applicable
		if (is_null($JSONResult->collectionHdPrice))
			$price = $JSONResult->collectionPrice;
		else
			$price = $JSONResult->collectionHdPrice;
		
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
	
	
	// Where the action happens...
	$IDList = createIDsString(50, 100);
	$searchResults = getJSONObjects($IDList);
	for ($i = 0; $i < $searchResults->resultCount; $i++) {
		//echo $i + 1, ": <br>";//!debug
		createInfo($searchResults->results[$i]);
	}

	$end_time = microtime(true); //!benchmarking
	echo 'Total time to retrieve: ', $end_time - $start_time, ' s', "<br>"; //!benchmarking
	
?>
	</table>
</body>

</html>