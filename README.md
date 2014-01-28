E-commerce extension for Yii2
=========
Provides support for basic e-commerce operations as a wrapper for [opus-online/yii2-payment](https://github.com/opus-online/yii2-payment). 
For usage examples see [opus-online/yii2-app-ecom](https://github.com/opus-online/yii2-app-ecom) - a sample application using this extension. 

Main features:
- Extensible code, almost everything can be customized
- Integration with ActiveRecord objects (orders, products)
- Shopping basket functionality (support for multiple storage methods)
- Support for payment adapters (currently Estonian banks are implemented)
- Support for discounts
- Basic widgets for displaying shopping basket contents, payment forms, lists

Installation
------------
You can install this package using composer. In your `composer.json` add the following. 
```json
{
	"require": {
			"opus-online/yii2-ecom": "*"
	},
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/opus-online/yii2-ecom"
		}
	]
}
```
The package starts living under `\opus\ecom` namespace.
Configuration
-------------
After the installation you  will probably want to:

1. Override and extend the main class to provide some custom functionality
2. Register this class as application component
3. Configure the bank payment parameters and the return URL. 

You can use something similar in the application configuration to achieve this:
```php
$config = [
	'components' => [
        'ecom' => [
            'class' => 'app\components\MyEcomComponent',
            'payment' => [
                'class' => 'opus\ecom\Payment',
                'bankReturnRoute' => 'bankret',// use url alias to shorten the return url
                'adapterConfig' => \yii\helpers\ArrayHelper::merge(require 'banks-default.php', require 'banks-local.php')
            ],
        ],
	],
];
```
And define the class `MyEcomComponent` like this:
```php
namespace app\components;

class MyEcomComponent extends \opus\ecom\Component
{
  // override methods here to customize
}

```
You can use `banks-default.php` to store general bank configuration (e.g. which banks are used) and define the more specific access parameters in `banks-local.php`, which is environment-specific and is not held in CVS. 

Usage
--------------

### Using ActiveRecord integration
ActiveRecord integration makes it very easy to add items to shopping basket and to use the basket to create orders and payment forms. Currently there are two kind of AR integration available.

1. Implement `opus\ecom\models\PurchasableInterface` in your AR model class to add support for products, services, discounts etc. 
2. Implement `opus\ecom\models\OrderableInterface` to add support for orders.



### Using the shopping basket
TBD
### Creating orders
TBD
### Using payments
#### Generating payment forms
TBD
#### Receiving requests from banks
TBD
