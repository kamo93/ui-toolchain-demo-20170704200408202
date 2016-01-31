<?php

function RetrieveItems()
{
    $application = getenv("VCAP_APPLICATION");
    $application_json = json_decode($application, true);
    $applicationName = $application_json["name"];
    $catalogAppName = $applicationName . "-catalog-api";
    $applicationURI = $application_json["application_uris"][0];
    $catalogHost=substr_replace($applicationURI, $catalogAppName, strlen("http://"), strlen($applicationName));
	  
    $parsedURL = parse_url($catalogHost);
    $catalogRoute = $parsedURL["scheme"] . "://" . $parsedURL["host"];
    $url = $catalogRoute . "/items";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $curlResult = curl_exec($curl);
    $curlError = curl_error($curl);
    $curlErrno = curl_errno($curl);
    curl_close($curl);
    $firstChar = substr($curlResult, 0, 1); /* should check if $curlResult === FALSE if newer PHP */
    if ($firstChar != "{") {
        $errorObject = new stdClass();
        $errorObject->error = $curlError;
        $errorObject->errno = $curlErrno;
        return json_encode($errorObject);
    }
    return $curlResult;
}

?>

