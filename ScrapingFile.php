<?php

include('./ProductDetailsClass.php');
include('./simple_html_dom.php');

$data = array();
$html = new simple_html_dom(); // Instantiate the DOM parser object



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
        //Checking if the value is not array
        if (!is_array($data[$key])) {
            //Calling CurlCall Function to get the source code 
            $response = CurlCallToAPI($data[$key]);
            $internalArray = ScrapingProducts($response);
            $internalKeys = array_keys($internalArray);
            foreach ($internalKeys as $internalKey) {
                if (!is_array($internalArray[$internalKey])) {
                    $Products = GetProducts($internalArray[$internalKey]);
                    $internalArray[$internalKey] = $Products;
                    // print_r($internalArray[$internalKey]);
                    // echo ("//////////////");
                    // echo ("<br>");
                    // echo ("<br>");
                    // echo ("<br>");
                }
            }
            // replacing existing link with array of child page URLS
            $data[$key] = $internalArray;
        }
    }
    print_r($data);
    $serializedData = serialize($data);
    file_put_contents('data.txt', $serializedData);
}

function CurlCallToAPI($url)
{
    $curl = curl_init();
    $encodedURL = urlencode($url);
    $CurlURL = "https://api.webscrapingapi.com/v1?url=";
    $CurlURL .= $encodedURL;
    $CurlURL .= "&api_key=X80HlWTDGksS0CRy3nCEYNFxufRaTWn0&device=desktop&proxy_type=datacenter&render_js=1&wait_until=domcontentloaded";
    curl_setopt_array($curl, array(
        CURLOPT_URL => $CurlURL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
        exit();
    } else {
        return $response;
    }
}
function ScrapingProducts($response)
{
    $i = 0;
    $internalArray = array();
    require_once './simple_html_dom.php';
    global $html;
    $html->clear();
    $html->load($response);

    $items = $html->find('.nd-listMeta__item');
    foreach ($items as $item) {
        if ($i === 1) {
            break;
        }
        $titletags = $item->find('.nd-listMeta__link');
        foreach ($titletags as $titletag) {
            $innerHtml = $titletag->innertext;
            $link = $titletag->href;
            $internalArray[$innerHtml] = $link;
        }
        $i++;
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
        global $html;
        $html->clear();
        $html->load($response);
        $products = $html->find('.nd-list__item.in-realEstateResults__item');
        $i = 0;
        foreach ($products as $product) {
            if ($i === 1) {
                break 1;
            }
            $links = $product->find('.in-card__title'); // Corrected selector
            foreach ($links as $link) {
                // $ProductArray[] = GetProductDetails($link->href);
                array_push($ProductArray, GetProductDetails($link->href));
                // array_push($ProductArray, $link->href);
            }
            $i++;
        }
        break;
    }
    return $ProductArray;
}

function GetPaginationNumber($url)
{
    $response = CurlCallToAPI($url);
    global $html;
    $html->clear();
    $html->load($response);
    $paginationList = $html->find('.in-pagination__list', 0);
    if ($paginationList) {
        $children = $paginationList->children();
        if ($children) {
            $lastElement = end($children);
            reset($children); // Reset the array pointer after using end()
            $innerText = trim($lastElement->plaintext);
            return $innerText;
        }
    }
}

function GetProductDetails($Producturl)
{
    //declaring all the block scoped variables here
    $ProductDetails = array(); // Clearing the entire array
    $ProductDetails = array(
        'imageLinks' => array(), // Array to store image links
        'name' => '', // String to store the name
        'address' => array(), // Array to store addresses
        'description' => '', // String to store the description
        'Price' => '', // String to store the price
        'Rooms' => '', // String to store the number of rooms
        'Surface' => '', // String to store the surface area
        'Bathroom' => '', // String to store the number of bathrooms
        'floor' => '', // String to store the floor
        'Features' => array(), // Array to store features
        'Expenses' => array(), // Array to store expenses
        'EnergyEfficiency' => array() // Array to store energy efficiency details
    );
    $imagesArray = array();
    $AdressArray = array();
    $Features = array();
    $Expenses = array();
    $EnergyEfficiency = array();

    //Using CurlCall method to get the response
    $response = CurlCallToAPI($Producturl);
    global $html;
    $html->clear();
    $html->load($response);
    // getting image links
    $images = $html->find('.nd-slideshow__item');
    foreach ($images as $internal) {
        $img = $internal->find('img', 0);
        if ($img) {
            // $imagesArray[] = $img->src;
            array_push($imagesArray, $img->src);
        }
    }
    $ProductDetails['imageLinks'] = $imagesArray;





    // Getting Title
    $title = $html->find('.in-titleBlock__title');
    foreach ($title as $internal) {
        $ProductDetails['name'] = $internal->innertext;
    }


    // Getting Address
    $location = $html->find('.in-titleBlock__link');
    foreach ($location as $internal) {
        $spans = $internal->find('span'); // Find all <span> tags within $internal
        foreach ($spans as $span) {
            // $AdressArray[] = $span->innertext;
            array_push($AdressArray, $span->innertext);
        }
        $ProductDetails['address'] = $AdressArray;
    }


    // Getting Price
    $Price = $html->find('.in-detail__mainFeaturesPrice');
    foreach ($Price as $internal) {
        $ProductDetails['Price'] = $internal->innertext;
    }

    // Getting Number of rooms
    $rooms = $html->find('[aria-label="rooms"]');
    foreach ($rooms as $internal) {
        $divs = $internal->find('.in-feat__data');
        foreach ($divs as $div) {
            if (!empty($div)) {
                $innerText = $div->plaintext;
                $ProductDetails['Rooms'] = $innerText;
            }
        }
    }

    // Getting Number of surface
    $Surface = $html->find('[aria-label="surface"]');
    foreach ($Surface as $internal) {
        $divs = $internal->find('.in-feat__data');
        foreach ($divs as $div) {
            if (!empty($div)) {
                $innerText = $div->plaintext;
                $ProductDetails['Surface'] = $innerText;
            }
        }
    }

    // Getting Number of bathroom
    $bathroom = $html->find('[aria-label="bathroom"]');
    foreach ($bathroom as $internal) {
        $divs = $internal->find('.in-feat__data');
        foreach ($divs as $div) {
            if (!empty($div)) {
                $innerText = $div->plaintext;
                $ProductDetails['Bathroom'] = $innerText;
            }
        }
    }

    // Getting Number of floor
    $floor = $html->find('[aria-label="floor"]');
    foreach ($floor as $internal) {
        $divs = $internal->find('.in-feat__data');
        foreach ($divs as $div) {
            if (!empty($div)) {
                $innerText = $div->plaintext;
                $ProductDetails['floor'] = $innerText;
            }
        }
    }
    // Getting Description
    $Description = $html->find('.in-readAll.in-readAll--lessContent');
    foreach ($Description as $internal) {
        $divs = $internal->find('div');
        foreach ($divs as $div) {
            if (!empty($div)) {
                $innerText = $div->outertext;
                $ProductDetails['description'] = $innerText;
            }
        }
    }

    $table = $html->find('.in-realEstateFeatures__list', 0); // Assuming you have selected the <dl> element using find() method
    if ($table) {
        $dtTags = $table->find('dt');
        $ddTags = $table->find('dd');


        for ($i = 0; $i < count($dtTags); $i++) {
            $key = trim($dtTags[$i]->plaintext);
            $value = trim($ddTags[$i]->plaintext);

            if ($key === 'other features') {
                $spanTags = $ddTags[$i]->find('span');
                $spanArray = array();
                foreach ($spanTags as $span) {
                    // $spanArray[] = $span->plaintext;
                    array_push($spanArray, $span->plaintext);
                }
                $value = $spanArray;
            }
            $Features[$key] = $value;
        }

        $ProductDetails['Features'] = $Features;
    }

    $table = $html->find('.in-realEstateFeatures__list', 1); // Assuming you have selected the <dl> element using find() method
    if ($table) {
        $dtTags = $table->find('dt');
        $ddTags = $table->find('dd');


        for ($i = 0; $i < count($dtTags); $i++) {
            $key = trim($dtTags[$i]->plaintext);
            $value = trim($ddTags[$i]->plaintext);
            $Expenses[$key] = $value;
        }

        $ProductDetails['Expenses'] = $Expenses;
    }

    $table = $html->find('.in-realEstateFeatures__list', 2); // Assuming you have selected the <dl> element using find() method
    if ($table) {
        $dtTags = $table->find('dt');
        $ddTags = $table->find('dd');


        for ($i = 0; $i < count($dtTags); $i++) {
            $key = trim($dtTags[$i]->plaintext);
            $value = trim($ddTags[$i]->plaintext);
            $EnergyEfficiency[$key] = $value;
        }

        $ProductDetails['EnergyEfficiency'] = $EnergyEfficiency;
    }

    $html->clear();
    return $ProductDetails;
}

function saveTofile($url, $fileName)
{
    //This functioned is defined if need to save the API response into an HTML file and view it 
    $response = CurlCallToAPI($url);
    $html = new simple_html_dom();
    $html->load($response);

    // Save the HTML to a file
    $file = './';
    $file .= $fileName;
    $file .= '.html';

    file_put_contents($file, $html);
    $html->clear();
}
