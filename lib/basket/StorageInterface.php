<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace opus\ecom\basket;

use opus\ecom\Basket;

/**
 * Interface StorageInterface
 *
 * @package opus\ecom\basket
 */
interface StorageInterface
{
    /**
     * @param Basket $basket
     * @return mixed
     */
    public function load(Basket $basket);

    /**
     * @param \opus\ecom\Basket $basket
     * @return void
     */
    public function save(Basket $basket);
}