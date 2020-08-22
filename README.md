# REWE eReceipt Parser

## Installation

```
$ composer require mrkriskrisu/rewe-ereceipt-parser
```

```json
{
    "require": {
        "mrkriskrisu/rewe-ereceipt-parser": "^0.3"
    }
}
```

## Example Usage
```php
<?php
require 'vendor/autoload.php';

use REWEParser\Parser;

$receipt = Parser::parseFromPDF('receipt.pdf');

echo "You've paid " . $receipt->getTotal() . " Euros.";
```
