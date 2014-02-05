<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace opus\ecom;

use opus\ecom\basket\Item;
use opus\ecom\models\BasketDiscountInterface;
use opus\ecom\models\BasketItemInterface;
use opus\ecom\models\OrderInterface;
use yii\base\InvalidParamException;
use yii\web\Session;

/**
 * Provides basic basket functionality (adding, removing, clearing, listing items). You can extend this class and
 * override it in the application configuration to extend/customize the functionality
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\ecom
 *
 * @property int $count
 * @property Session $session
 */
class Basket extends Component
{
    const ITEM_PRODUCT = '\opus\ecom\models\BasketProductInterface';
    const ITEM_DISCOUNT = '\opus\ecom\models\BasketDiscountInterface';


    use SubComponentTrait;

    /**
     * @var string Internal class name for holding basket elements
     */
    public $itemClass = 'opus\ecom\basket\Item';
    /**
     * @var array
     */
    protected $items;

    /**
     * @var Session
     */
    private $session;
    /**
     * Override this to provide custom (e.g. database) storage for basket data
     *
     * @var string|\opus\ecom\basket\StorageInterface
     */
    private $storage = 'opus\ecom\basket\storage\Session';

    /**
     * @inheritdoc
     */
    public function init()
    {
//        $_SESSION = [];

        $this->clear(false);
        $this->setStorage(\Yii::createObject($this->storage));
        $this->items = $this->storage->load($this);
    }


    /**
     * @param bool $save
     */
    public function clear($save = true)
    {
        $this->items = [];

        $save && $this->storage->save($this);
    }


    /**
     * @param OrderInterface $order
     * @param bool $clear
     * @throws \Exception
     */
    public function createOrder(OrderInterface $order, $clear = true)
    {
        try {
            $order->saveFromBasket($this);
            $clear && $this->clear();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * @param models\BasketItemInterface $element
     * @param bool $save
     */
    public function add(BasketItemInterface $element, $save = true)
    {
        $this->addItem($element);
        $save && $this->storage->save($this);
    }

    /**
     * @param \opus\ecom\models\BasketItemInterface $item
     * @internal param $quantity
     */
    protected function addItem(BasketItemInterface $item)
    {
        $uniqueId = md5(uniqid('_bs', true));

        $this->items[$uniqueId] = $item;
    }

    /**
     * @param string $uniqueId
     * @param bool $save
     * @throws \yii\base\InvalidParamException
     * @return $this
     */
    public function remove($uniqueId, $save = true)
    {
        if (!isset($this->items[$uniqueId])) {
            throw new InvalidParamException('Item not found');
        }
        unset($this->items[$uniqueId]);

        $save && $this->storage->save($this);
        return $this;
    }

    /**
     * @param string $itemType If specified, only items of that type will be counted
     * @return int
     */
    public function getCount($itemType = null)
    {
        return count($this->getItems($itemType));
    }

    /**
     * @param string $itemType One of self::ITEM_ constants
     * @return BasketItemInterface[]
     */
    public function getItems($itemType = null)
    {
        $items = $this->items;
        if (!is_null($itemType)) {
            $items = array_filter($items,
                function ($item) use ($itemType) {
                    /** @var $item Item */
                    return is_subclass_of($item, $itemType);
                });
        }
        return $items;
    }

    /**
     * Finds all items of type $itemType, sums the values of $attribute of all models and returns the sum.
     */
    public function getAttributeTotal($attribute, $itemType = null)
    {
        $sum = 0;
        foreach ($this->getItems($itemType) as $model) {
            $sum += $model->{$attribute};
        }
        return $sum;
    }

    /**
     * Returns the total price of all items and applies custom functionality to the sum (calls finalizeBasketPrice).
     * Most likely this is a number with custom discounts applied.
     *
     * @param bool $format
     * @return int|string
     */
    public function getTotalDue($format = true)
    {
        // get item total sum
        $sum = $this->getAttributeTotal('totalPrice', self::ITEM_PRODUCT);

        // apply discounts
        foreach ($this->getItems(self::ITEM_DISCOUNT) as $discount)
        {
            /** @var $discount BasketDiscountInterface */
            $discount->applyToBasket($this, $sum);

        }

        $sum = $this->component->finalizeBasketPrice($sum, $this);
        $sum = max(0, $sum);
        return $format ? $this->component->formatter->asPrice($sum) : $sum;
    }

    /**
     * Returns the difference between finalized basket price and the sum of all items. If you haven't done any custom
     * price modification, this most likely returns 0
     *
     * @param bool $format
     * @return int|string
     */
    public function getTotalDiscounts($format = true)
    {
        $sum = $this->getAttributeTotal('totalPrice', self::ITEM_PRODUCT);

        $totalDue = $this->getTotalDue(false);


        $sum = $sum - $totalDue;
        return $format ? $this->component->formatter->asPrice($sum) : $sum;
    }


    /**
     * @param \yii\web\Session $session
     * @return Basket
     */
    public function setSession(Session $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * @return \yii\web\Session
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @param \opus\ecom\basket\StorageInterface|string $storage
     * @return Basket
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return \opus\ecom\basket\StorageInterface|string
     */
    protected function getStorage()
    {
        return $this->storage;
    }
}