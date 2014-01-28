<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace opus\ecom\basket;

use opus\ecom\Basket;
use opus\ecom\models\PurchasableInterface;
use yii\base\Model;

/**
 * Class Item
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\ecom\basket
 *
 * @property mixed $totalPrice
 * @property PurchasableInterface $model
 */
class Item extends Model implements \Serializable
{
    const OP_SERIALIZE = 'serialize';
    const OP_USER_INPUT = 'user-input';
    /**
     * @var Basket
     */
    public $basket;
    /**
     * @var string
     */
    public $uniqueId;
    /**
     * @var string
     */
    public $modelClass;
    /**
     * @var string
     */
    public $pkValue;

    /**
     * @var integer
     */
    public $price;

    /**
     * @var string
     */
    public $label;

    /**
     * @var double
     */
    public $quantity = 1;

    /**
     * @var array Model attributes stored for serializing/unserializing objects
     */
    public $modelAttributes;

    /**
     * @var PurchasableInterface
     */
    private $_model;

    /**
     * @param PurchasableInterface $element
     * @param array $options
     */
    public function __construct(PurchasableInterface $element, array $options)
    {
        // get model attributes
        $modelAttributes = [];
        foreach ($element->attributes() as $attribute) {
            $modelAttributes[$attribute] = $element->$attribute;
        }

        $options += [
            'uniqueId' => md5(uniqid('_bs', true)),
            'modelClass' => $element->className(),
            'pkValue' => $element->getPrimaryKey(),
            'price' => $element->getPrice(),
            'label' => $element->getLabel(),
            'modelAttributes' => $modelAttributes,
        ];
        parent::__construct($options);
    }

    /**
     * @param bool $reload Whether to reload the item from database
     * @return PurchasableInterface
     */
    public function getModel($reload = false)
    {
        if (true === $reload) {
            $this->_model = call_user_func([$this->modelClass, 'find'], $this->pkValue);
        } else {
            if (!isset($this->_model)) {
                $this->_model = new $this->modelClass($this->modelAttributes);
            }
        }
        return $this->_model;
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::OP_SERIALIZE => [
                'quantity',
                'label',
                'price',
                'pkValue',
                'modelClass',
                'modelAttributes',
                'uniqueId'
            ],
            self::OP_USER_INPUT => ['quantity'],
        ];
    }

    /**
     * @return double
     */
    public function getTotalPrice()
    {
        return $this->price * $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        $this->scenario = self::OP_SERIALIZE;
        return serialize($this->getAttributes($this->activeAttributes()));
    }

    /**
     * @inheritdoc
     */
    public function unserialize($serialized)
    {
        $this->scenario = self::OP_SERIALIZE;
        $this->setAttributes(unserialize($serialized), false);
    }
}