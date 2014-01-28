<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace opus\ecom\models;

use opus\ecom\Basket;
use opus\payment\services\payment\Response;

/**
 * Class OrderableInterface
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\ecom\models
 */
interface OrderableInterface
{
    /**
     * This method should load the contents of the basket and save the order with all its item in the database
     *
     * @param Basket $basket
     * @return boolean
     */
    public function saveFromBasket(Basket $basket);

    /**
     * Returns the total money due for this order. Should return a value of type double
     *
     * @return double
     */
    public function getTransactionSum();

    /**
     * Returns the primary key for the ActiveRecord item
     *
     * @return string
     */
    public function getPrimaryKey();

    /**
     * @param Response $response
     * @return OrderableInterface
     */
    public function bankReturn(Response $response);
} 