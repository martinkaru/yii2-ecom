<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace opus\ecom\basket\storage;

use opus\ecom\Basket;
use opus\ecom\basket\StorageInterface;

/**
 * Class SessionStorage
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\ecom\basket
 */
class Session implements StorageInterface
{
    /**
     * @var string
     */
    public $basketVar = 'basket';

    /**
     * @inheritdoc
     */
    public function load(Basket $basket)
    {
        $items = [];
        if (false !== ($session = ($basket->session->get($this->basketVar, false)))) {
            $items = unserialize($session);
        }
        return $items;
    }

    /**
     * @inheritdoc
     */
    public function save(Basket $basket)
    {
        $sessionData = serialize($basket->getItems());
        $basket->session->set($this->basketVar, $sessionData);
    }
}