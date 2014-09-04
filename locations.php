<?php
$q=$_REQUEST['q'];
if($q!='')
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.transdirect.com.au/api/locations/search?q=".$q);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	$response_location = curl_exec($ch);
	curl_close($ch);

	$locations = json_decode($response_location);
	echo $response_location;
}
else
{
	echo '';
}
?>