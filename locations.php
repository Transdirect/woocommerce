<?php
$q=$_REQUEST['q'];
if($q!='0')
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.transdirect.com.au/api/locations/search?q=".$q);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	$response_location = curl_exec($ch);
	curl_close($ch);

	$locations = (array)json_decode($response_location);	
	$locations['requestNumber'] = $_REQUEST['requestNumber'];
	
	$response_location = json_encode($locations);
	echo $response_location;
}
else
{
	$locations['requestNumber'] = $_REQUEST['requestNumber'];
	$locations['locations'] = '';
	$response_location = json_encode($locations);
	echo $response_location;
}
?>