<?php

$data=array();
function CurlCallToAPI($url){
    $curl = curl_init();
    $encodedURL=urlencode($url);
    $CurlURL="https://api.webscrapingapi.com/v1?url=";
    $CurlURL.=$encodedURL;
    $CurlURL.="&api_key=X80HlWTDGksS0CRy3nCEYNFxufRaTWn0&device=desktop&proxy_type=datacenter&render_js=1&wait_until=domcontentloaded";
	curl_setopt_array($curl, [
		CURLOPT_URL => $CurlURL,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
	]);
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
		echo "cURL Error #:" . $err;
	} else {
		return $response;
	}
}
function ScrapingProducts($response) {
    $internalArray=array();
	require_once './simple_html_dom.php';
    $html = new simple_html_dom(); // Instantiate the DOM parser object
    $html->load($response);
    
    $items = $html->find('.nd-listMeta__item');
    foreach ($items as $item) {
        $titletags=$item->find('.nd-listMeta__link');
        foreach ($titletags as $titletag) {
            $innerHtml=$titletag->innertext;
            $link=$titletag->href;
            $internalArray[$innerHtml]=$link;
        }
    }
    return $internalArray;
}
// Calling the CurlCall function to get response data for the first page
$response=CurlCallToAPI('https://www.indomio.gr/en/');
// Storing resultant array in $data array
$data=ScrapingProducts($response);
// Getting all keys of $data Array
$keys = array_keys($data);
// 
foreach($keys as $key){
    $internalArray=array();
    //Checking if the value is not array
    if(!is_array($data[$key])){
        //Calling CurlCall Function to get the source code 
        $response=CurlCallToAPI($data[$key]);
        $internalArray=ScrapingProducts($response);
        // replacing existing link with array of child page URLS
        $data[$key]=$internalArray;
    }
    break;
}
print_r($data);

?>