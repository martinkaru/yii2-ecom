<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 4.02.14
 */

namespace opus\ecom\models;

/**
 * Interface BasketItemInterface
 *
 * @package opus\ecom\models
 */
interface BasketItemInterface extends \Serializable
{
    /**
     * Returns the label for the purchasable item (displayed in basket etc)
     *
     * @return string
     */
    public function getLabel();
} 