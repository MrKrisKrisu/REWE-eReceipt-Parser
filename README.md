# REWE eReceipt Parser

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/6bbb1a99e88f4df198db20314bb42bf5)](https://www.codacy.com/gh/MrKrisKrisu/REWE-eReceipt-Parser/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=MrKrisKrisu/REWE-eReceipt-Parser&amp;utm_campaign=Badge_Grade)
![PHPUnit](https://github.com/MrKrisKrisu/REWE-eReceipt-Parser/workflows/PHP%20Composer/badge.svg)

## Installation

```
$ composer require mrkriskrisu/rewe-ereceipt-parser
```

## Example Usage
```php
<?php
require 'vendor/autoload.php';

use REWEParser\Parser;

$receipt = Parser::parseFromPDF('receipt.pdf');

echo "You've paid " . $receipt->getTotal() . " Euros.";
```

## Requirements
If you want to use the build in PDF Parser you need to install ``pdftotext`` in your system. 
But don't worry, you can use your own PDF to Text Software if you want and give it to 
the eReceipt-Parser via the ``Parser::parseFromText($text)`` function.

## Get the eReceipt
To get your digital Receipt at REWE you need to register at rewe.de and connect your 
bonus card (PAYBACK) to the account. Then you can activate the "eBon" in the settings 
and you'll receive the receipt after every purchase via Mail.

## Contribution
I'm glad that you want to help this library to be perfect. 
Just do your magic und make a Pull Request. ✨
