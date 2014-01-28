<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 24.01.14
 */

namespace opus\ecom\widgets;

use opus\ecom\assets\PayAssetBundle;
use opus\ecom\models\OrderableInterface;
use opus\ecom\SubComponentTrait;
use opus\payment\services\payment\Form;
use opus\payment\services\payment\Transaction;
use opus\payment\services\Payment;
use opus\payment\widgets\PaymentWidget;
use yii\helpers\Html;

/**
 * Class PaymentWidget
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\ecom\widgets
 *
 * @property OrderableInterface order
 * @property Payment service
 */
class PaymentButtons extends PaymentWidget
{
    /**
     * @var OrderableInterface
     */
    private $_order;
    /**
     * @var Payment
     */
    private $_service;
    /**
     * @var Transaction
     */
    private $_transaction;
    /**
     * @var PayAssetBundle
     */
    private $_asset;

    public function init()
    {
        $this->_asset = PayAssetBundle::register($this->view);
        parent::init();
    }

    public function run()
    {
        $this->forms = $this->_service->generateForms($this->_transaction);
        parent::run();
    }

    /**
     * @param \opus\ecom\models\OrderableInterface $order
     * @return PaymentButtons
     */
    public function setOrder(OrderableInterface $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * @param \opus\payment\services\Payment $service
     * @return PaymentButtons
     */
    public function setService(Payment $service)
    {
        $this->_service = $service;
        return $this;
    }

    /**
     * @param \opus\payment\services\payment\Transaction $transaction
     * @return PaymentButtons
     */
    public function setTransaction(Transaction $transaction)
    {
        $this->_transaction = $transaction;
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function generateSubmit(Form $form)
    {
        $image = $this->_asset->baseUrl . '/banks/' . strtolower($form->getProviderTag()) . '.jpg';
        return Html::input('image', $form->getProviderName(), null, ['src' => $image]);
    }
}