E-commerce extension for Yii2
=========
Provides support for basic e-commerce operations as a wrapper for [opus-online/yii2-payment](https://github.com/opus-online/yii2-payment). 
For usage examples see [opus-online/yii2-app-ecom](https://github.com/opus-online/yii2-app-ecom) - a sample application using this extension. This guide assumes that you are familiar with implementations of Estonian bank payments (see [pangalink.net](http://pangalink.net))

Main features:
- Extensible code, almost everything can be customized
- Integration with ActiveRecord objects (orders, products)
- Shopping basket functionality (support for multiple storage methods)
- Support for payment adapters (currently Estonian banks are implemented)
- Support for discounts
- Basic widgets for displaying shopping basket contents, payment forms, lists

Installation
------------
You can install this package using composer. In your `composer.json` add the following required package: 
```json
{
	"require": {
			"opus-online/yii2-ecom": "*"
	}
}
```
Technical overview
------------------
The package lives under `\opus\ecom` namespace and consists of 3 sub-components (these can be overridden in configuration and are directly accessible as properties of the main component).

1. **basket** - provides shopping basket functionality
2. **payment** - provides functionality for receiving payments
3. **formatter** - helper class used in grid views

Most classes and subclasses can be overridden in the configuration.

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
The following examples assume that you have registered `opus\ecom\Component` (or its child class) as an application component under `Yii::$app->ecom`.

### Using ActiveRecord integration
ActiveRecord integration makes it very easy to add items to shopping basket and to use the basket to create orders and payment forms. Currently there are two kind of AR integration available.

1. Implement `opus\ecom\models\PurchasableInterface` in your AR model class to add support for products, services, discounts etc. 
2. Implement `opus\ecom\models\OrderableInterface` to add support for orders.



### Using the shopping basket
Operations with the shopping basket are very straightforward when using a model that implements `PurchasableInterface`. The basket object can be accessed under `\Yii::$app->ecom->basket` and can be overridden in configuration if you need to customize it. 
```php
// access the basket from "basket" subcomponent
$basket = \Yii::$app->ecom->basket;

// Product is an AR model implementing PurchasableInterface
$product = Product::find(1);

// add an item to the basket
$basket->add($product);

// add an item with "quantity" property of the Item class set to 2
$basket->add($product, ['quantity' => 2]);

// returns the sum of all basket item prices
$sum = $basket->getTotalDue();

// clear the basket
$basket->clear();

// render the contents of the basket with default parameters
echo \opus\ecom\widgets\BasketGridView::widget([
    'basket' => $basket,
]);
```

#### Items in the basket
Products/items in the basket are stored as instances of `opus\ecom\basket\Item` that is a child class of `yii\base\Model`. You can override this class in the configuration to use your custom objects. 

```php
// get all items from the basket
$items = $basket->getItems();

// get only items of Product class
$items = $basket->getItems(Product::className());

// loop through basket items
foreach ($items as $item) {
	// use any Model-related method on items
	var_dump($item->getAttributes());
	
	// generates an AR object based on model attributes stored in session
	$cachedModel = $item->getModel();
	
	// loads an AR model from the database
	$realModel = $item->getModel(true);
	
	// remove an item from the basket by its ID
	$basket->remove($item->uniqueId)
}
```

### Creating orders
The basket object can easily be converted into orders (AR objects that implement the `OrderableInterface`).
```php
$order = new Order([
	'user_id' = \Yii::$app->user->id,
	'status' => 'new',
]);
\Yii::$app->ecom->basket->createOrder($order);
```
This calls the `saveFromBasket` method from your Order class, which could look something like this.
```php
public function saveFromBasket(Basket $basket)
{
    $transaction = $this->getDb()->beginTransaction();
    try
    {
        $this->due_amount = $basket->getTotalDue(false);
        if (!$this->save()) {
            throw new \RuntimeException('Could not save order model');
        }

        foreach ($basket->getItems() as $item) {
            // create and save "order line" objects looking up necessary attributes from $item
        }
        $transaction->commit();
    }
    catch (\Exception $exception)
    {
        $transaction->rollback();
        throw $exception;
    }
}
```


### Using payments
#### Generating payment forms
If you have saved your Order objects, you can use the included widget `opus\ecom\widgets\PaymentButtons` to render all the bank forms included in your configuration. You can provide your own widget class in the configuration if you need customization (override `widgetClass` under `payment` sub-component). There is a shorthand method for generating the widget with correct parameters:
```php
// generate FORM tags for every bank with hidden inputs and bank logos as submit images
\Yii::$app->ecom
	->payment
	->createWidget($order)
	->run();
```
#### Receiving requests from banks
After payment the user is directed back to the application to a URL specified in your configuration by `bankReturnRoute` under `payment` sub-component. In your controller action you can pass the request to the component like this:
```php
public function actionBankReturn()
{
	// pass in the request data and the name of the class you want to use as order object
	$model = \Yii::$app->ecom->payment->handleResponse($_REQUEST, Order::className());

	$this->redirect(['order/view', 'orderId' => $model->id]);
}
```
The above code loads the right Order from the database and calls the `bankReturn` method from your Order class. You are responsible for saving the order and logging necessary data. 
```php
public function bankReturn(Response $response)
{
    $this->status = $response->isSuccessful() ? 'paid' : 'error';
    $this->save();

    \Yii::$app->log->log('Returned from bank: ' . $response->__toString(), Logger::LEVEL_INFO);

    return $this;
}
```
