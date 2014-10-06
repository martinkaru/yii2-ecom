<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 4.02.14
 */

namespace opus\ecom\models;

/**
 * All objects that can be added to the basket must implement this interface
 *
 * @package opus\ecom\models
 */
interface BasketItemInterface extends \Serializable
{
    /**
     * Returns the label for the basket item (displayed in basket etc)
     *
     * @return string
     */
    public function getLabel();
    /**
     * Checks if the item is valid
     * Errors are viewable through $this->getErrors();
     *
     * @return bool
     */
    public function validateItem();
    /**
     * Returns all errors for current model (after validating)
     *
     * @return string[]
     */
    public function getItemErrors();
} 