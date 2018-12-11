<?php

/* @var $this yii\web\View */
/* @var $model \common\models\Order */
/* @var $model_old \common\models\Question */
/* @var $dataProvider \yii\data\ActiveDataProvider */


use yii\helpers\ArrayHelper;
use yii\helpers\Html;
$this->title = 'Переход к оплате через Яндекс кассу';


?>
<section class="content">
    <div class="makeOrders center">
        <div class="orders_title">Вы успешно оформили заказ!</div>
        <div class="privateOffice_content">
            <div class="makeOrders_content">
                <div class="ordersList_title"><span>Переход к Яндекс кассе:</span></div>

                <div class="orderDetails_content">
                    <div class="questionAnswerBlock">
                        <div class="questionAnswerBlock_title">Страница автоматически будет перенаправлена на Яндекс кассу через :</div>
                        <div class="payContact_list">
                           <span class="timeback" time_start="5" time_end="0">5</span>
                        </div>
                    </div>

                    <form action="https://demomoney.yandex.ru/eshop.xml" method="post" style="display:none;" id="form_yandex">
                        <!-- Обязательные поля -->
                        <input name="shopId" value="<?=Yii::$app->params["shopIdYandex"]?>" type="hidden"/>
                        <input name="scid" value="<?=Yii::$app->params["scidYandex"]?>" type="hidden"/>
                        <input name="customerNumber" value="<?=$modelOrder->client_id?>" type="hidden"/>
                        <input name="sum" value="<?=$modelOrder->sum_order?>" type="hidden">
                        <input name="orderNumber" value="<?=$modelOrder->id?>" type="hidden">
                        <input name="cps_email" value="<?=$modelOrder->client->email?>" type="hidden">
                        <input name="cps_phone" value="<?=$modelOrder->client->phone?>" type="hidden">
                        <input name="paymentType" value="AC" type="hidden"/>
                        <!--                        <input name="ym_merchant_receipt" value='--><?//=$modelOrder->createJsonCheck()?><!--' type="hidden"/>-->
                        <input type="submit" value="Оплатить" class="btn makeOrders_btn makeOrders_btn_end pay_button pay_button1"/>
                    </form>
                </div>
            </div>
        </div>
        <div class="ordersBlock_contentResult">
            <div class="totalBlock">
                <?=$this->render("item/_result",[
                    "modelOrder"=>$modelOrder,
                    "arrOrderAdditionals"=>$modelOrder->orderAdditionals
                ])?>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</section>
