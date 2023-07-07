<?php

include('./ProductDetailsClass.php');
include('./simple_html_dom.php');

$data = array();

function Main($url)
{
	global $data;
	// Calling the CurlCall function to get response data for the first page
	$response = CurlCallToAPI($url);
	// Storing resultant array in $data array
	$data = ScrapingProducts($response);
	// Getting all keys of $data Array
	$keys = array_keys($data);
	// 
	foreach ($keys as $key) {
		$internalArray = array();
		//Checking if the value is not an array
		if (!is_array($data[$key])) {
			//Calling CurlCall Function to get the source code 
			$response = CurlCallToAPI($data[$key]);
			$internalArray = ScrapingProducts($response);
			$internalKeys = array_keys($internalArray);
			foreach ($internalKeys as $internalKey) {
				if (!is_array($internalArray[$internalKey])) {
					$Products = GetProducts($internalArray[$internalKey]);
					$internalArray[$internalKey] = $Products;
					print_r($internalArray[$internalKey]);
					echo ("//////////////");
					echo ("<br>");
					echo ("<br>");
					echo ("<br>");
				}
			}
			// replacing existing link with array of child page URLS
			$data[$key] = $internalArray;
		}
	}
	print_r($data);
}

function CurlCallToAPI($url)
{
	$curl = curl_init();
	$encodedURL = urlencode($url);
	$CurlURL = "https://api.webscrapingapi.com/v1?url=";
	$CurlURL .= $encodedURL;
	$CurlURL .= "&api_key=X80HlWTDGksS0CRy3nCEYNFxufRaTWn0&device=desktop&proxy_type=datacenter&render_js=1&wait_until=domcontentloaded";
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
		//echo "cURL Error #:" . $err;
	} else {
		return $response;
	}
}

function ScrapingProducts($response)
{
	$internalArray = array();
	$html = new simple_html_dom(); // Instantiate the DOM parser object
	$html->load($response);

	$items = $html->find('.nd-listMeta__item');
	foreach ($items as $item) {
		$titletags = $item->find('.nd-listMeta__link');
		foreach ($titletags as $titletag) {
			$innerHtml = $titletag->innertext;
			$link = $titletag->href;
			$internalArray[$innerHtml] = $link;
		}
	}
	return $internalArray;
}

function GetProducts($url)
{
	$ProductArray = array();
	$paginationNum = GetPaginationNumber($url);
	for ($u = 1; $u <= $paginationNum; $u++) {
		$PaginationUrl = $url . "?pag=" . $u;
		$response = CurlCallToAPI($PaginationUrl);
		$html = new simple_html_dom(); // Instantiate the DOM parser object
		$html->load($response);

		$items = $html->find('.nd-listing__item');
		foreach ($items as $item) {
			$ProductDetails = new ProductDetails();
			$ProductDetails->name = trim($item->find('.nd-listing__name a', 0)->plaintext);
			$ProductDetails->price = trim($item->find('.nd-listing__price', 0)->plaintext);
			$ProductDetails->imageUrl = $item->find('.nd-listing__image img', 0)->src;

			$ProductDetails->url = $item->find('.nd-listing__image a', 0)->href;
			array_push($ProductArray, $ProductDetails);
		}
	}
	return $ProductArray;
}

function GetPaginationNumber($url)
{
	$response = CurlCallToAPI($url);
	$html = new simple_html_dom(); // Instantiate the DOM parser object
	$html->load($response);

	$pagination = $html->find('.nd-pagination__last');
	if (count($pagination) > 0) {
		$lastPageUrl = $pagination[0]->href;
		$lastPageNumber = substr($lastPageUrl, strrpos($lastPageUrl, '=') + 1);
		return intval($lastPageNumber);
	}
	return 1;
}

Main("https://www.example.com");
