<?php
/**
 * @author Martin Karu <martin@opus.ee>
 * @date 6.10.2014
 */

namespace opus\ecom\basket;

use opus\ecom\Basket;
use yii\base\Object;
use yii\web\Session;

/**
 * Handles persisting the basket's contents
 *
 * @author Martin Karu <martin@opus.ee>
 * @package opus\ecom\basket
 *
 * @property Session $session
 */
class StorageManager extends Object
{
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
     * Load basket from storage
     * @param Basket $basket
     * @return string[]
     */
    public function load(Basket $basket)
    {
        return $this->storage->load($basket);
    }

    /**
     * Setter for the storage component
     *
     * @param \opus\ecom\basket\StorageInterface|string $storage
     * @return self
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @param Basket $basket
     * @return $this
     */
    public function save(Basket $basket)
    {
        $this->storage->save($basket);
        return $this;
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
}
