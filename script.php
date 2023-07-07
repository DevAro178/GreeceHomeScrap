<?php

include('./ScrapingFile.php');
// Main('https://www.indomio.gr/en/');

// $data = file_get_contents('data.txt');
// $array = unserialize($data);
// // echo "<pre>";
// // print_r($array);
// // echo "</pre>";

// echo "<pre>";
print_r(GetProductDetails('https://www.indomio.gr/en/aggelies/569912/'));
