<?php
    $application = getenv("VCAP_APPLICATION");
    $application_json = json_decode($application, true);
    $applicationURI = $application_json["application_uris"][0];

    if (!isset($_GET['action']) || $_GET['action'] == 'status') {
        //Show status of load testing
        if (file_exists("RUNNING")) {
			echo "Load test is running. ";
			echo file_get_contents("RUNNING");
		} else {
			echo "Load test is not running. Use http://$applicationURI/autoLoadTest.php?count=COUNT&delay=DELAY&action=start to start load testing.";
		}	
    } else if ($_GET['action'] == 'stop') {
		//Turn off load test
		exec('rm -f RUNNING');
		print "Load test is stopped.\n";
	} else if ($_GET['action'] == 'start') {
		//If already running, tell user to issue a stop first. We could fancier and kill
		//the running script and update with the new params but for now let's just...
		if (file_exists("RUNNING")) {
			print("Load test is already running. Use http://$applicationURI/autoLoadTest.php?action=stop to stop the running test.");
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
		echo exec("echo 'Count: $count Delay: $delay' > RUNNING");
    
		//Start the load script in the background and return
		#Print "load.sh -d $delay -u $url\n";
		echo exec("sh load.sh -d $delay -u $url > load.log 2>&1 &");
		print "Loaded started with delay $delay on $url\n";
	} else {
		print "Unsupported action {$_GET['action']}";
	}
?>
