<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace opus\ecom;

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
     * @var BasketItemInterface[]
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
        $this->clear(false);
        $this->setStorage(\Yii::createObject($this->storage));
        $this->items = $this->storage->load($this);
    }

    /**
     * Delete all items from the basket
     *
     * @param bool $save
     * @return $this
     */
    public function clear($save = true)
    {
        $this->items = [];

        $save && $this->storage->save($this);
        return $this;
    }

    /**
     * Setter for the storage component
     *
     * @param \opus\ecom\basket\StorageInterface|string $storage
     * @return Basket
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Create an order from the basket contents
     *
     * @param OrderInterface $order
     * @param bool $clear
     * @return \opus\ecom\models\OrderInterface
     * @throws \Exception
     */
    public function createOrder(OrderInterface $order, $clear = true)
    {
        try {
            $order->saveFromBasket($this);
            $clear && $this->clear();
            return $order;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Add an item to the basket
     *
     * @param models\BasketItemInterface $element
     * @param bool $save
     * @return $this
     */
    public function add(BasketItemInterface $element, $save = true)
    {
        $this->addItem($element);
        $save && $this->storage->save($this);
        return $this;
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
     * Removes an item from the basket
     *
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
     * Returns all items of a given type from the basket
     *
     * @param string $itemType One of self::ITEM_ constants
     * @return BasketItemInterface[]
     */
    public function getItems($itemType = null)
    {
        $items = $this->items;
        if (!is_null($itemType)) {
            $items = array_filter($items,
                function ($item) use ($itemType) {
                    /** @var $item BasketItemInterface */
                    return is_subclass_of($item, $itemType);
                });
        }
        return $items;
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
     * Finds all items of type $itemType, sums the values of $attribute of all models and returns the sum.
     *
     * @param string $attribute
     * @param string|null $itemType
     * @return int|float
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
     * Returns the total price of all items and applies discounts and custom functionality to the sum (calls finalizeBasketPrice).
     *
     * @param bool $format
     * @return int|string
     */
    public function getTotalDue($format = true)
    {
        // get item total sum
        $sum = $this->getAttributeTotal('totalPrice', self::ITEM_PRODUCT);

        // apply discounts
        foreach ($this->getItems(self::ITEM_DISCOUNT) as $discount) {
            /** @var $discount BasketDiscountInterface */
            $discount->applyToBasket($this, $sum);
        }

        $sum = $this->component->finalizeBasketPrice($sum, $this);
        $sum = max(0, $sum);
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
     * @return \opus\ecom\basket\StorageInterface|string
     */
    protected function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param string $uniqueId Unique hash
     * @return bool
     */
    public function has($uniqueId)
    {
        return isset($this->items[$uniqueId]);
    }

    /**
     * @param string $uniqueId
     * @param string $attribute
     * @param string $value
     */
    public function update($uniqueId, $attribute, $value)
    {
        if (!$this->has($uniqueId) || !$this->items[$uniqueId]->hasAttribute($attribute)) {
            return false;
        }

        $this->items[$uniqueId]->setAttribute($attribute, $value);
    }
}
