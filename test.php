<?php
$productID = 21;
function generateProductId($productID) {
    $sku = 'SKU-' . strtoupper(bin2hex(random_bytes(4))) ."-". $productID;
    return $sku;
}

echo generateProductId($productID);
//test - akali