<?php

	//$RUNNING_FILE = "/tmp/RUNNING"; //Uncomment for local testing with apache
	$RUNNING_FILE = "RUNNING";

	function echoStatus($running, $message) {
			global $RUNNING_FILE;
        	if (file_exists("$RUNNING_FILE")) {
        		$runningParams = file_get_contents("$RUNNING_FILE");
				$runningParams = json_decode($runningParams, true);
				$status = array('running' => $running, 'message' => $message, 'count' => $runningParams['count'], 'delay' => $runningParams['delay']);
			} else {
				$status = array('running' => $running, 'message' => $message);
			}
        	echo json_encode($status);
	}

    $application = getenv("VCAP_APPLICATION");
    $application_json = json_decode($application, true);
    $applicationURI = $application_json["application_uris"][0];

    if (!isset($_GET['action']) || $_GET['action'] == 'status') {
        //Return status of load testing
        if (file_exists("$RUNNING_FILE")) {
        	echoStatus(true, 'Load test is running.');
			//echo file_get_contents("$RUNNING_FILE");
		} else {
			echoStatus(false, 'Load test is not running.');
		}	
    } else if ($_GET['action'] == 'stop') {
		//Turn off load test
		exec("rm -f $RUNNING_FILE");
		echoStatus(false, 'Load test stopped.');
	} else if ($_GET['action'] == 'results') {
		echo file_get_contents("results.log");
	} else if ($_GET['action'] == 'start') {
		//If already running, user needs to issue a stop first. We could get fancier and kill
		//the running script and update with the new params but for now let's just...
		if (file_exists("$RUNNING_FILE")) {
			echoStatus(true, 'Load test is running.');
			exit();
		}

		//Calculate route to catalog loadTest URL
		$applicationName = $application_json["name"];
		$catalogAppName = str_replace("ui-", "catalog-api-", $applicationName);
		$catalogAppName = str_replace("-ui", "-catalog-api", $catalogAppName);
		$catalogHost=substr_replace($applicationURI, $catalogAppName, 0, strlen($applicationName));
		$catalogRoute = "http://" . $catalogHost;

		//Specify count in loadTest URL
		if (isset($_GET['count'])) {
			$count = $_GET['count'];
		} else {
			$count = 100;
		}
		$url = $catalogRoute . "/loadTest?count=" . $count;

		//Specify the delay between calls to loadTest
		if (isset($_GET['delay'])) {
			$delay = $_GET['delay'];
		} else {
			$delay = 0;
		}

		//Use a RUNNING file to indicate load test is running
		$running = array('count' => $count, 'delay' => $delay);
        $running = json_encode($running);

		#echo exec("echo $running > $RUNNING_FILE");
		file_put_contents($RUNNING_FILE, $running);		
    
		//Start the load script in the background and return
		echo exec("sh load.sh -d $delay -u $url > load.log 2>&1 &");
		echoStatus(true, 'Load test started.');
	} else {
		echoStatus((file_exists("$RUNNING_FILE")), "Unsupported action {$_GET['action']}");
	}
?>