<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace opus\ecom\models;

use opus\ecom\Basket;

/**
 * Interface PurchasableInterface
 *
 * @package opus\ecom\models
 */
interface BasketProductInterface extends BasketItemInterface
{
    /**
     * Returns the price of the element. This should include multiplication with any quantity attributes
     *
     * @return mixed
     */
    public function getTotalPrice();
}