<?php

    $application = getenv("VCAP_APPLICATION");
    $application_json = json_decode($application, true);
    $applicationName = $application_json["name"];
    $catalogAppName = str_replace("ui-", "catalog-api-", $applicationName);
    $catalogAppName = str_replace("-ui", "-catalog-api", $catalogAppName);
    $applicationURI = $application_json["application_uris"][0];
    $catalogHost=substr_replace($applicationURI, $catalogAppName, 0, strlen($applicationName));
    $catalogRoute = "http://" . $catalogHost;

    if (isset($_GET['count'])) {
        $count = $_GET['count'];
    } else {
        $count = 100;
    }
    $url = $catalogRoute . "/loadTest?count=" . $count;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $curlResult = curl_exec($curl);
    $httpResponse = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($httpResponse !== 200) {
        $errorObject = new stdClass();
        $errorObject->code = $httpResponse;
        $firstChar = substr($curlResult, 0, 1); /* should check if $curlResult === FALSE if newer PHP */
        if ($firstChar == "{") {
            $parsedResult = json_decode($curlResult);
            $errorObject->error = $parsedResult->msg;
        }
        http_response_code($httpResponse);
        echo json_encode($errorObject);
        return;
    }
    echo $curlResult;

?>
