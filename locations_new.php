<?php
$q=$_REQUEST['q'];
function autocomplete_format($results) {
    foreach ($results as $result) {
        echo $result[0] . '|' . $result[1] . "\n";
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
			  $results[] = array($val, $val);			
			
		}
	}
	//var_dump($results);exit;
	echo autocomplete_format($results);
}
else
{
	echo '';
}
?>