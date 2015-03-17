<?php

$data = file_get_contents('php://input');
$services = getenv("VCAP_SERVICES");
$services_json = json_decode($services, true);

for ($i = 0; $i < sizeof($services_json["user-provided"]); $i++){
	if ($services_json["user-provided"][$i]["name"] == "ordersAPI"){
		$ordersHost = $services_json["user-provided"][$i]["credentials"]["host"];
	}
}

$parsedURL = parse_url($ordersHost);
$ordersRoute = $parsedURL["scheme"] . "://" . $parsedURL["host"];
$ordersURL = $ordersRoute . "/rest/orders";

function httpPost($data,$url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, true);  
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	$output = curl_exec ($ch);
	$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close ($ch);
	return $code;
}

echo json_encode(array("httpCode" => httpPost($data,$ordersURL), "ordersURL" => $ordersURL));

?>