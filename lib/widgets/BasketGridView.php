<?php
/**
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @date 23.01.14
 */

namespace opus\ecom\widgets;

use opus\ecom\Basket;
use Yii;
use yii\data\ArrayDataProvider;

/**
 * Class BasketGridView. Provides the default data provider with no pagination and all basket models
 *
 * @author Ivo Kund <ivo@opus.ee>
 * @package opus\ecom\widgets
 */
class BasketGridView extends GridView
{
    /**
     * @var Basket
     */
    public $basket;

    /**
     * @var string Only items of that type will be rendered. Defaults to null meaning all items will be rendered
     */
    public $itemClass = null;

    public function init()
    {
        if (!isset($this->dataProvider)) {
            $this->dataProvider = new ArrayDataProvider([
                'key' => 'uniqueId',
                'allModels' => $this->basket->getItems($this->itemClass),
                'pagination' => false,
            ]);
        }
        parent::init();
    }
}