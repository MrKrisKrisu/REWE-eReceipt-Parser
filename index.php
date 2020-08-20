<?php

require_once './vendor/autoload.php';

use REWEParser\Parser;

$receipt = Parser::parseFromPDF('/Users/mrkriskrisu/Documents/GitHub/REWE-eReceipt-Parser/tests/receipts/weight_eccash.pdf', '/usr/local/bin/pdftotext');

echo "Total: " . $receipt->getTotal();


