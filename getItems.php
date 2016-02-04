<?php

function RetrieveItems()
{
    //echo "\r\n**************************************";
    $application = getenv("VCAP_APPLICATION");
    //echo "\r\napplication:" . $application;
    $application_json = json_decode($application, true);
    $applicationName = $application_json["name"];
    //echo "\r\napplicationName:" . $applicationName;
    $catalogAppName = str_replace("ui-", "catalog-api-", $applicationName);
    $catalogAppName = str_replace("-ui", "-catalog-api", $catalogAppName);
    //echo "\r\ncatalogAppName:" . $catalogAppName;
    $applicationURI = $application_json["application_uris"][0];
    //echo "\r\napplicationURI:" . $applicationURI;
    $catalogHost=substr_replace($applicationURI, $catalogAppName, 0, strlen($applicationName));
    //echo "\r\ncatalogHost:" . $catalogHost;    
    $catalogRoute = "http://" . $catalogHost;
    //echo "\r\ncatalogRoute:" . $catalogRoute;    
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

