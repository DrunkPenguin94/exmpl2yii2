<?php

/* @var $this yii\web\View */
/* @var $model \common\models\Question */
/* @var $model_old \common\models\Question */
/* @var $dataProvider \yii\data\ActiveDataProvider*/

use yii\helpers\Html;

$this->title = 'Мои заказы';
?>
<section class="content">
    <div class="orders center">
        <div class="orders_title">Мои заказы</div>
        <div class="privateOffice_content">
            <div class="noOrders_content">
                <div class="ordersList_title"><span>У вас еще нет заказов</span></div>
                <div class="noOrders_form">
                    <form action="/orders/make-order">
                        <div class="noOrders_text">
                            Закажите свою первую уборку
                            <br/>
                            прямо сейчас
                        </div>
                        <div class="areaBlock">
                            <div class="envelope_input">
                                <input class="orders_input" name="area" type="text" placeholder="Площадь помещения м2">
                            </div>
                        </div>
                        <div class="bathroom_col">
                            <span class="minus"></span>
                            <div class="bathroom_col_input">
                                <input type="text" name="sanuzel" value="1" size="5"/>
                                <span>- Санузел</span>
                            </div>
                            <span class="plus"></span>
                        </div>
                        <br/>
                        <button type="submit" class="btn" >Оформить заказ</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
