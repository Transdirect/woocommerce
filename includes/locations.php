<?php
if(isset($_REQUEST['isInternational'])){
	$cnt = curl_init();
	curl_setopt($cnt, CURLOPT_URL, "https://www.transdirect.com.au/api/locations/get_country");
	curl_setopt($cnt, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($cnt, CURLOPT_HEADER, FALSE);
	curl_setopt($cnt, CURLOPT_SSL_VERIFYPEER, false);
	$country = curl_exec($cnt);
	curl_close($cnt);
	echo $country;
}
if(isset($_REQUEST['q'])){
	$q = $_REQUEST['q'];
	if ($q != '0') {
		$q = str_replace ( ' ', '%20', $q);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.transdirect.com.au/api/locations/search?q=".$q);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$response_location = curl_exec($ch);
		curl_close($ch);

		$locations = (array)json_decode($response_location);
		$locations['requestNumber'] = $_REQUEST['requestNumber'];

		$response_location = json_encode($locations);
		echo $response_location;

	} else {
		$locations['requestNumber'] = $_REQUEST['requestNumber'];
		$locations['locations'] = '';
		$response_location = json_encode($locations);
		echo $response_location;
		
	} 
}?>