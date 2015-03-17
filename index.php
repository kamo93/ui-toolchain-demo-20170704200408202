<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<?php
$services = getenv("VCAP_SERVICES");
$services_json = json_decode($services, true);

for ($i = 0; $i < sizeof($services_json["user-provided"]); $i++){
	if ($services_json["user-provided"][$i]["name"] == "catalogAPI"){
		$catalogHost = $services_json["user-provided"][$i]["credentials"]["host"];
	}
}

$parsedURL = parse_url($catalogHost);
$catalogRoute = $parsedURL["scheme"] . "://" . $parsedURL["host"];

function CallAPI($method, $url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
$result = CallApi("GET", $catalogRoute . "/items");
?>

<script>
var items = <?php echo $result?>;
  
function loadItems(){
	var i = 0;
	console.log("Load Items: " + items.rows);
	document.getElementById("loading").innerHTML = "";
	for(i = 0; i < items.rows.length; ++i){
		addItem(items.rows[i].doc);
	}
}

function addItem(item){
	var div = document.createElement('div');
	div.className = 'item';
	div.innerHTML = "<div class ='well'><img width='100%' height='auto' src = '"+item.imgsrc+"'/><br><button onclick='orderItem(\""+item._id+"\")'><b>Buy</b></button><br><u>"+item.name+"</u><br>"+item.description+"<br><b>$"+item.usaDollarPrice + "</b></div>";
	document.getElementById('boxes').appendChild(div);
}

function orderItem(itemID){
	//create a random customer ID and count
	var custID = Math.floor((Math.random() * 999) + 1); 
	var count = Math.floor((Math.random() * 9999) + 1); 
	var myjson = {"itemid": itemID, "customerid":custID, "count":count};
    
    $.ajax ({
    	type: "POST",
    	contentType: "application/json",
	    url: "submitOrders.php",
	    data: JSON.stringify(myjson),
	    dataType: "json",
	    success: function( result ) {
	        if(result.httpCode != "201" && result.httpCode != "200"){
	        	alert("Failure: check that your JavaOrders API App is running and your user-provided service has the correct URL.");
	        }
	        else{
	        	alert("Order Submitted! Check your Java Orders API to see your orders: \n" + result.ordersURL);
	        }
	    },
	    error: function(XMLHttpRequest, textStatus, errorThrown) { 
	    	alert("Error");
        	console.log("Status: " , textStatus); console.log("Error: " , errorThrown); 
    }  
	});

}

</script>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<link rel="stylesheet" href="style.css">
</head>
<table class="headerTable">
	<tr>
		<td><span class="pageTitle"><h1>Microservices Sample</h1></span></td> 
	</tr>
</table>
<body  onload="loadItems()">
	<div class="container">
		<div id='boxes' class="notes"></div>
	</div>
	<div id="loading"><br>Loading...</div>
</body>
</html>