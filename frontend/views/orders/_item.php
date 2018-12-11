
<?php

/* @var $this yii\web\View */
/* @var $modelOrder \common\models\Order */
/* @var $dataProvider \yii\data\ActiveDataProvider*/

use yii\helpers\Html;
$allCleaners=$modelOrder->orderAddCleaners;

$isCashBox=$modelOrder->IsCashbox;


$heightOrdersBlockHeader=56+21+21*count($allCleaners);
$heightOrdersBlockHeader=($modelOrder->status==0 && $modelOrder->pay_form_id==1 && !$isCashBox )? $heightOrdersBlockHeader+65: $heightOrdersBlockHeader;
?>


<li class="ordersBlock">
    <div class="ordersBlock_header" style="height: <?=$heightOrdersBlockHeader.'px'?>">

        <div class="ordersBlock_headerAddress">
			<div class="ordersBlock_headerAddress_title">
                <?=$modelOrder->adress->rayon->name?>
                <?=$modelOrder->adress->rayon->id!=1 ? " район" : "" ?>
			</div>
            <?if($modelOrder->adress_id!=null):?>
                <?=$modelOrder->adress->normadress?>
            <?else:?>
                (не назначен)
            <?endif?>
        </div>
        <div class="ordersBlock_headerDate">
            <?=rudate("d F H:i", strtotime( $modelOrder->date))?>
        </div>
        <div class="ordersBlock_headerCleaner">
            <div class="cleaner">
                <div class="cleaner_title">
                    Клинер:
                </div>
                <div class="cleaner_name">
                    <?if($modelOrder->cleaner!=null):?>
                        <?=mb_ucfirst($modelOrder->cleaner->family)?>
                    <?else:?>
                        <span>еще не назначен</span>
                    <?endif?>

                </div>
                <?
                    if(isset($allCleaners)){
                    foreach($allCleaners as $cleaner){
                ?>
                <div class="cleaner_name">
                    <?=$cleaner->user->family?>
                </div>
                <?}}?>
            </div>
        </div>
        <div class="cleaner_orderNumber">
            <div class="orderNumber">Заказ №<?=$modelOrder->id?></div>
            <?  if($mod=="finished"){ ?>
                <a class="btn_order btn_estimate  detailsWork-trigger" id_order='<?=$modelOrder->id?>' href="javascript:void(0);" >Оценить уборку</a>
            <?}
                if(($mod=="index" || $mod=="nearestOrder" || $modelOrder->modered==1) && $mod!="finished" && $mod!="plan"){
                    $modelOrder->modered==1  || $mod=="nearestOrder" ? $isChange=0 : $isChange=1 ;

            ?>
               <a class="btn_order btn_cancel cancelCleaning-trigger"   data-date-cleaning="<?=date("d.m.Y H:i", strtotime($modelOrder->date_cleaning))?>"
                    data-time-cleaning="<?=date("H:i", strtotime($modelOrder->date_cleaning))?>"
                    data-order-id="<?=$modelOrder->id?>"
                    data-order-subscrib-id="<?=$modelOrder->id_subscription?>"
                    data-is-change="<?=$isChange?>"
                    data-is-subsrb="<?=$modelOrder->regular_id==1 ? 0 : 1?>"
                    href="javascript:void(0);">Отменить уборку</a>

            <?}


            if(
                ($modelOrder->status==10 && ( ( !$isCashBox && $modelOrder->pay_form_id==1) || $modelOrder->pay_form_id==2)   )||
                ($modelOrder->status==0 && $modelOrder->pay_form_id==1 && !$isCashBox)


            ){ ?>
            <a class="btn_order btn_pay_order"   data-date-cleaning="--><?=date("d.m.Y H:i", strtotime($modelOrder->date_cleaning))?>>
               data-time-cleaning="<?=date("H:i", strtotime($modelOrder->date_cleaning))?>" data-order-id="<?=$modelOrder->id?>" href="/orders/choice-of-payment?id=<?=$modelOrder->id?>"  >Оплатить уборку</a>

            <?}?>
<!--            --><?//}else{?>
<!--                <a class="btn_order btn_cancel cancelCleaning-trigger"   data-date-cleaning="--><?//=date("d.m.Y H:i", strtotime($modelOrder->date_cleaning))?><!--"-->
<!--                   data-time-cleaning="--><?//=date("H:i", strtotime($modelOrder->date_cleaning))?><!--" data-order-id="--><?//=$modelOrder->id?><!--" href="javascript:void(0);">Отменить уборку</a>-->
<!--            --><?//}?>
        </div>
    </div>
    <div class="ordersBlock_content" data-order-id="<?=$modelOrder->id?>" data-order-subscrib-id="<?=$modelOrder->id_subscription?>">
        <input type="hidden" id="order-coupon" class="form-control" name="Order[coupon]">
        <div class="ordersBlock_contentBlock">
            <div class="ordersBlock_contentInfo">
                <div class="contentInfo_stringList">
                    <?=$this->render("item/_detail",[

                            "modelOrder"=>$modelOrder,
                            "mod"=>$mod,

                    ])?>

                </div>
         <?=$this->render("item/_additional",[
             "arrAdditional"=>$arrAdditional,
             "modelOrder"=>$modelOrder,
             "mod"=>$mod,
         ])?>
            </div>
            <div class="ordersBlock_contentResult">
                <div class="totalBlock">
                <?=$this->render("item/_result",[
                    "modelOrder"=>$modelOrder,
                    "arrOrderAdditionals"=>$modelOrder->orderAdditionals
                ])?>
                </div>
                <?if($mod!="finished" && $modelOrder->pay_form_id!=3 && !isset($modelOrder->couponBind)){?>
                <div class="totalBlock_promotionalCode credited"> <!-- Если enter то еще не ввели, если credited то ввели, если ошибка то error-->
                    <div class="promotionalCode_title">
                        <p class="title_enter">Ввести промокод</p>
                        <p class="title_credited">Промокод верен</p>
                        <p class="title_error">Не верный промокод</p>
                    </div>
                    <div class="promotionalCode_block">
                        <input class="promotionalCode_input" type="text" placeholder="">
                        <button class="btn">Применить</button>
                    </div>
                </div>
                <?}?>
            </div>
        </div>
        <div class="save_order" ></div>
        <?if($mod!="finished" && $mod!="plan" && $modelOrder->modered!=1 && $mod!="nearestOrder"){?>
        <div class="confirmationBlock hidden">
            <div class="confirmationBlock_title">
      <?      $text="    Вы внесли изменение в подписку. Хотите применить ко всем подпискам или только для этого заказа? ";
                if($modelOrder->regular_id==1){
                    $text_addOrder="Вы внесли изменение в дополнительные услуги. Хотите применить изменения?";
                }else{
                    $text_addOrder="Вы внесли изменение в дополнительные услуги. Хотите применить ко всей подписке или только для этого заказа?";
                }
      ?>
                <?=$text_addOrder?>
            </div>
            <?if($modelOrder->regular_id!=1){?>
                <a class="confirmation_btn confirmation_btn_red btnAcceptOrder" mod="1"  >
                    Для этого
                    <br/>
                    заказа
                </a>

                <a class="confirmation_btn confirmation_btn_red confirmation_btn_red_all"  mod="2">
                    Ко всей
                    <br/>
                    подписке
                </a>
            <?}else{?>
                <a class="confirmation_btn confirmation_btn_red btnAcceptOrder"  mod="1" style="padding-top: 20px;">
                    Сохранить
                </a>

            <?}?>
            <a class="confirmation_btn reset" href="/orders">Отменить</a>

        </div>
        <?}?>
    </div>

</li>