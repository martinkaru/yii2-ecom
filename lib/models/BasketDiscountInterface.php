<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 4.02.14
 */

namespace opus\ecom\models;

use opus\ecom\Basket;

/**
 * Interface BasketSpecialItemInterface
 *
 * @package opus\ecom\models
 */
interface BasketDiscountInterface extends BasketItemInterface
{
    /**
     * @param Basket $basket
     * @param integer|float $basketTotalSum
     * @return void
     */
    public function applyToBasket(Basket $basket, &$basketTotalSum);
} 