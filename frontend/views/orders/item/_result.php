<?
/* @var $modelOrder \common\models\Order*/

$sum=0;

$sum_packet=$modelOrder->costPerArea+$modelOrder->costPerSanuzel+ $modelOrder->costPerKitchen;

$discount_weekend;
$time_clean=0;
if($modelOrder->packet_id!=null && $modelOrder->area!=null){
$sum +=$modelOrder->changeSum($sum_packet)-$modelOrder->changeSum($sum_packet/100*$modelOrder->regular->discount);
}
?>
<div class="totalBlock_title">
    <?if($modelOrder->packet_id!=null && $modelOrder->area!=null):?>

            Пакет <?=$modelOrder->packet->name?><br><?=$modelOrder->area?> м<sup>2</sup>

            с <?=$modelOrder->sanuzel?> санузл<?= $modelOrder->sanuzel==1?"ом":"ами"?>


    <?endif;?>
</div>
    <div class="totalBlock_stringList">
        <div class="cleaningPackage">
            <?if($modelOrder->packet->name!=null ):?>
                <div class="totalBlock_string">
                    Пакет <p>«<?=$modelOrder->packet->name?>»</p>
                </div>

            <?endif;?>

            <?if($modelOrder->date_cleaning!=null ):?>

                <div class="totalBlock_string">
                    Дата уборки <p> <?=rudate("D, d.m.y, H:i", strtotime( $modelOrder->date_cleaning))?></p>
                </div>

            <?endif;?>




            <?if($modelOrder->packet_id!=null ):?>

                <?$time_clean=$modelOrder->packetTimePerArea->hours_max;   ?>
                <?foreach ($arrOrderAdditionals as $modelOrderAdditionals):?>
                    <?$time_clean+=$modelOrderAdditionals->additional->hours*$modelOrderAdditionals->count;  ?>
                <?endforeach;?>
                <div class="totalBlock_string <?=$time_clean<=13 && $modelOrder->checkIntervalTime($modelOrder->date_cleaning,$time_clean) ? "" :"red" ?>">
                    Время уборки <p time_clean="<?=$time_clean?>"> ~ <?=$time_clean?> <?=pluralForm($time_clean, "час","часа","часов")?></p>
                </div>

            <?endif;?>
            <?if($modelOrder->packet_id!=null ):?>
            <div class="totalBlock_string" >
                Регулярность <p><?=$modelOrder->regular->name?></p>
            </div>
            <?endif ?>


        </div>


        <?if($modelOrder->packet_id!=null && $modelOrder->area!=null):?>
            <div class="totalBlock_string">
                    Стоймость уборки <p> <?=$sum?>
                    <span>е</span></p>
            </div>
        <?endif?>



        <?foreach ($arrOrderAdditionals as $modelOrderAdditionals):?>

            <?if($modelOrderAdditionals->additional_id!="11"){?>
                <?$sum+=$modelOrderAdditionals->additional->price * $modelOrderAdditionals->count ?>
                <div class="totalBlock_string" title="<?=$modelOrderAdditionals->additional->name?>">
                    <?=mb_strlen ($modelOrderAdditionals->additional->name,'UTF-8')<23 ?$modelOrderAdditionals->additional->name : mb_strimwidth($modelOrderAdditionals->additional->name,0,23,"...",'UTF-8')?> x<?=$modelOrderAdditionals->count?>  <p><?=($modelOrderAdditionals->additional->price * $modelOrderAdditionals->count)?> <span>е</span></p>
<!--                    --><?//=$modelOrderAdditionals->additional->name ?><!--<br>еще не много текста x--><?//=$modelOrderAdditionals->count?><!--  <p>--><?//=($modelOrderAdditionals->additional->price * $modelOrderAdditionals->count)?><!-- <span>е</span></p>-->
<!--                --><?//
//                Yii::trace($modelOrderAdditionals->additional->name);
//                Yii::trace(mb_strlen ($modelOrderAdditionals->additional->name,'UTF-8'));
//                Yii::trace(mb_strimwidth($modelOrderAdditionals->additional->name,0,15));
//                ?>
                </div>

            <?}?>
        <?endforeach;?>


        <?if($modelOrder->dayweek_cleaning==7 || $modelOrder->dayweek_cleaning==6){
            $discount_weekend=$modelOrder->changeSum($sum*number_format(Yii::$app->params["weekendSurcharge"]/100,2,'.','') );
            $sum+=$discount_weekend;
        }?>
        <?if($modelOrder->dayweek_cleaning!=null):?>
            <?if($modelOrder->dayweek_cleaning==7 || $modelOrder->dayweek_cleaning==6):?>
                <div class="totalBlock_string">
                    Выходной +<?=Yii::$app->params["weekendSurcharge"]?>%<p><?=$discount_weekend?> <span>е</span></p>

                </div>
            <?endif?>
        <?endif?>
        <?if($modelOrder->adress!=null || $modelOrder->addRayonSum != null):?>
            <?if($modelOrder->adress->rayon->country==1 || $modelOrder->addRayonSum->country==1):?>
                <?$sum+=Yii::$app->params["departureCity"];?>
                <div class="totalBlock_string">
                    Выезд за город<p> <?=Yii::$app->params["departureCity"]?> <span>е</span></p>
                </div>
            <?endif;?>
        <?endif?>
        <?foreach ($arrOrderAdditionals as $modelOrderAdditionals):?>
            <?if($modelOrderAdditionals->additional_id=="11"){?>
                <?$sum+=$modelOrderAdditionals->additional->price * $modelOrderAdditionals->count ?>

                <div class="totalBlock_string">
                <?=$modelOrderAdditionals->additional->name?> x<?=$modelOrderAdditionals->count?>  <p><?=($modelOrderAdditionals->additional->price * $modelOrderAdditionals->count)?> <span>е</span></p>
                </div>

            <?}?>
        <?endforeach;?>





<!--        --><?//
//        if ($modelOrder->coupon!=null){
//            $modelCoupon = \common\models\Coupon::find()
//                ->where("name=:coupon_name",["coupon_name"=>$modelOrder->coupon])
//                ->andWhere("order_id IS NULL")
//                ->andWhere("CURDATE() BETWEEN `start_date` AND `finish_date`")
//                ->one();
//
//
//            $cntOrder = \common\models\Order::find()
//                ->where(["client_id"=>Yii::$app->user->id])
//
//                ->count();
//            if($modelCoupon !=null){
//                if($modelCoupon->first_clean  && $cntOrder ==0){
//                    $modelCoupon=null;
//                }
//                else {
//                     $sum=$sum-$modelCoupon->discount;
//
//                }
//            }else{
//                //   $result["result" ]=false;
//            }
//        }else{
//            if(!$modelOrder->isNewRecord) $modelCoupon = \common\models\Coupon::find()->where(["order_id"=>$modelOrder->id])->one();
//            else $modelCoupon =null;
//        }
//        ?>



        <?php
            if(isset($modelOrder->id)){
                $allFineUserBind=$modelOrder->allFineUserBind
        ?>
                <?if(isset($allFineUserBind)):?>
                    <?foreach($allFineUserBind as $fineUserModelOne){
                        $sum+=$fineUserModelOne->sum;
                        ?>
                        <div class="totalBlock_string">
                            Штраф (Заказ №<?=$fineUserModelOne->order_id?>)<p> <?=$fineUserModelOne->sum?> <span>е</span></p>
                        </div>
                    <?}?>
                <?endif;?>
        <?
            }else{
        ?>
            <?php
            $allFineUser=$modelOrder->allFineUser;
            if(isset($allFineUser)):?>
                <?foreach($allFineUser as $fineUserModelOne){
                    $sum+=$fineUserModelOne->sum;
                    ?>
                    <div class="totalBlock_string">
                        Штраф (Заказ №<?=$fineUserModelOne->order_id?>)<p> <?=$fineUserModelOne->sum?> <span>е</span></p>
                    </div>
                <?}?>
            <?endif;?>
        <?
            }
        ?>


        <?
        if(!empty($modelOrder->coupon))
            $modelCoupon=$modelOrder->getCoupon($modelOrder->coupon);
        elseif(isset($modelOrder->id))
            $modelCoupon=$modelOrder->couponBind;
        ?>

        <?if($modelCoupon!=null):?>
            <?if($modelCoupon->discount>0){
                $sum-=$modelCoupon->discount;
            ?>
                <div class="totalBlock_string">
                    Скидка по промокоду <p> -<?=$modelCoupon->discount?><span>е</span></p>
                </div>
            <?}?>
            <?if($modelCoupon->percent>0){
                $sum-=$modelOrder->changeSum($sum*$modelCoupon->percent/100);
                ?>
                <div class="totalBlock_string">
                    Скидка по промокоду<p> -<?=$modelCoupon->percent?>%</p>
                </div>
            <?}?>
        <?endif;?>
<!--        --><?//if($modelVerTel==null){?>
<!---->
<!--                <div class="totalBlock_string">-->
<!--                    Телефон не подтвержден<p> </p>-->
<!--                </div>-->
<!---->
<!--        --><?//}?>

    </div>



    <div class="totalBlock_result">
        ИТОГО:
        <?if($modelOrder->pay_form_id!=3){?>
            <p><?=$modelOrder->sumOrder($arrOrderAdditionals)?><span> г</span></p>
        <?}else{?>
            <p>Бесплатно</p>
        <?}?>
        <div class="hidden"> <?=$sum?></div>
    </div>
