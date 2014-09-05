<?php
$q=$_REQUEST['q'];
function tds_autocomplete_format($results) {
    foreach ($results as $result) {
        echo $result . '|' . $result . "\n";
    }
}
if($q!='')
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://www.transdirect.com.au/api/locations/search?q=".$q);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	$response_location = curl_exec($ch);
	curl_close($ch);

	$locations = json_decode($response_location);
	//var_dump($locations->locations);
	$results = '';
	if(!empty($locations->locations))
	{
		foreach($locations->locations as $lc)
		{
			$val = $lc->postcode.','.$lc->locality;
			$results[] = $lc->postcode;			
			
		}
		$results = array_unique($results);
	}
	//var_dump($results);exit;
	echo tds_autocomplete_format($results);
}
else
{
	echo '';
}
?>