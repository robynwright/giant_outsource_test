<?php
$configutator = new giantOutsourceTest();
$configutator->time = time();

//Run through the arguments to set the fileName and the out
foreach ($argv as $arg) {
	if (strpos($arg, "-file=")!== false) {
		$configutator->fileName = str_replace("-file=", "", $arg);
	}

	if (strpos($arg, "-out=")!== false) {
		$configutator->out = str_replace("-out=", "", $arg);
	}
}

if ($configutator->runDocument()) {
	 echo "Ran succesfully\n";
}

class giantOutsourceTest {
	public  $fileName;
    public  $out;
	public 	$time;

	public function runDocument()
	{
		//Before running anything ensure that the file exists and stop if it does not
		if (!file_exists($this->fileName)) {
			echo "File does not exist\n";
			return false;
		}
		$save = array();
		$handle = fopen($this->fileName, "r");
		if ($handle) {
			//go through file line by line
			$i = 0;
			while (($line = fgets($handle)) !== false) {

				//Explode the line by colon to get the instruction and the list
				$lineInfo = explode(":", $line);

				//If the $lineInfo does not get split into two parts then the line should be ignored and passed to the next line
				if (count($lineInfo) < 2) {
					continue;
				}

				//Setting the instruction and the list to be used below
				$instruction = $lineInfo[0];
				$list = explode(',', trim($lineInfo[1]));

				//Setting the instructions all to lowercase to ensure there is no issue with case sensitivity
				switch (strtolower($instruction)) {
					case "min":
						$result = min($list);
						break;
					case "max":
						$result = max($list);
						break;
					case "avg":
						$result = array_sum($list)/count($list);
						break;
					case "sum":
						$result = array_sum($list);
						break;
					case "p90":
						$result = $this->getP90($list);
						break;
					default:
						$result = false;
						break;
					}

					//Only save data if it is true i.e. is one of the predefined instructions
					if($result) {
						$save[$i] = array($instruction, $result);
						$i++;
				}
			}

			fclose($handle);

			//Only create CSV and save to database if there are values that are needed to be saved
			if (count($save) > 0) {
				$this->writeToCsv($save);
				$this->saveToDb($save);
				return true;
			} else {
				echo "There were no items to save\n";
				return false;
			}

		} else {
			echo "Error with file, please correct and try again\n";
			return false;
		}
	}

	/**
     * gets the 90th percentile of a list of arrays
     * @param array $list
	 * @return $result
     */
	public function getP90($list)
	{
		sort($list);
		$index = (90/100) * count($list);
		if (floor($index) == $index) {
			 $result = ($list[$index-1] + $list[$index])/2;
		}
		else {
			$result = $list[floor($index)];
		}
		return $result;
	}

	/**
     * creates and writes the results to a csv
     * @param array $results
	 * @return boolean
     */
	public function writeToCsv($results)
	{
		$resultPath = 'results/'.$this->time;
		mkdir($resultPath);

		header('Content-Type: text/csv');
		$filePath = fopen($resultPath.'/'.$this->out, 'w+');

		//Setting the header for CSV and inserting it as the first row
		$header = array('Instruction', 'Result');
		fputcsv($filePath, $header);

		//Going line by line of the result, insert them into the CSV
		foreach ($results as $result) {
			fputcsv($filePath, $result);
		}

		fclose($filePath);
		echo "CSV can be found here: ". $resultPath."/".$this->out ."\n";
		return true;
	}

	/**
     * writes the results to the database
     * @param array $results
	 * @return boolean
     */
	public function saveToDb($results)
	{
		//Try create the connection to the database or stop with error
		$pdo  = new PDO('sqlite:database/test.db') or die("cannot open the database");

		//Format the results to be sql friendly
		$sql = array();
		foreach($results as $row ) {
			$sql[] = '("'.$row[0].'", '.$row[1].')';
		}

		//Run query
		$query = $pdo->prepare("INSERT INTO instruction_set (instruction, result) VALUES" . implode(',', $sql) . ";");

		//Check if insert was successful
		if($query) {
			$query->execute();
		} else {
			echo "Could not insert values into the database \n";
		}

		//End the connection
		$pdo = null;
		return true;
	}
}