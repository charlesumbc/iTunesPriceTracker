<!--!IMPORTANT!
	Enter the location of your CSV file into $csvFile.
	Should be just a one column CSV file with an iTunes ID on each row.
	
	Now with CSV support!
	Optimized by using batch lookup - iTunes has some floodgates for handling individual queries it seems
-->

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
<!-- Initialize some things in PHP -->
<?php
		$csvFile = ''; //enter a CSV file location here
		$database = fopen($csvFile, 'r');
		date_default_timezone_set('EST');
?>
    <title>iTunes Price Checker for <?= date('l j F Y') ?> </title>
</head>

<body>
	<table>
<?php

	$start_time = microtime(true); //!benchmarking

	//Appending ID's into one big string
	if (($handle = $database) != FALSE) {
		$count = 0;
		//?instead of 100, use some kind of const
		//this reads any column headers, if you decide to use column headers in your CSV file
/* 		while (($line = fgetcsv($handle, 100, ",")) != FALSE) { */
			//?instead of 50, use a const
			while ($count < 50 && ($line = fgetcsv($handle, 100, ",")) != FALSE) {
				if ($line[0] != '0') {
					$ids = $ids . "," . $line[0];
					$count++;
				}
				/*
				//Making sure 0's aren't read in.
				else {
					echo "Invalid ID: " . $line[0] . "<br>";
				}
				*/
			}
/* 		} */
		fclose($handle);
	}
	else {
		echo "Failed to open file.";
	}
	
	//Long ID string
	$ids = substr($ids, 1);

	//echo $ids, "<br>"; //Making sure my IDs are appended, correctly
	//echo $count, "<br>"; //Making sure I read in the right number of valid values

	//used cURL to keep iTunes Search API happy with my scripted search
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://itunes.apple.com/lookup?id=$ids"); //this queries all of the ID's all at once
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$content = curl_exec($ch); //JSON received
	curl_close($ch);
	
	//results array turned into a PHP array
	$content = json_decode($content);	
	//var_dump($parsed); //Making sure $parsed is right
	
	//if the item exists by comparing object's resultCount value
	if ($content->resultCount == $count) {
		//echo $content->resultCount . "<br>"; //number of results returned from iTunes
		for ($i = 0; $i < $count; $i++) {
			$parsed = $content->results[$i];
			
			//Name, art, price
			$name = $parsed->collectionName;
			$art = $parsed->artworkUrl100;
		
			//if-then for HD collection pricing where applicable		
			if (is_null($parsed->collectionHdPrice))
				$price = $parsed->collectionPrice;
			else
				$price = $parsed->collectionHdPrice;
			
			?>
			<tr>
				<img src="<?= $art ?>"/> <br>
				Name: <a href="https://itunes.apple.com/lookup?id=<?=$id?>"><?= $name ?></a> <br>
				Price: $<?= $price ?> <br>
				<br>
			</tr>
			<?
		}
	}

	$end_time = microtime(true); //!benchmarking
	echo 'Total time to retrieve: ', $end_time-$start_time, ' s', "<br>"; //!benchmarking
	
?>
	</table>
</body>

</html>