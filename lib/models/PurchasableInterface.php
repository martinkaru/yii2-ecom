<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace opus\ecom\models;

use opus\ecom\Basket;
use yii\db\ActiveRecordInterface;

/**
 * Interface PurchasableInterface
 *
 * @package opus\ecom\models
 */
interface PurchasableInterface extends ActiveRecordInterface
{
    /**
     * Returns the ActiveRecord class name for the object
     *
     * @return string
     */
    public static function className();

    /**
     * Returns the label for the purchasable item (displayed in basket etc)
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns the price of the element. This can be positive or negative (on coupon-type discounts)
     *
     * @return mixed
     */
    public function getPrice();
}