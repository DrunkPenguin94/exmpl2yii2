
<?php

/* @var $this yii\web\View */
/* @var $modelOrder \common\models\Order */
/* @var $dataProvider \yii\data\ActiveDataProvider*/

use yii\helpers\Html;
use yii\widgets\ActiveForm;


?>





<div class="revenueExpenses_block
<?

//Yii::trace(Yii::$app->controller->action->id=="index");
//Yii::trace($modelOrder->cleaner_id);


if($modelOrder->cleaner_id!=null){
//    Yii::trace(Yii::$app->controller->action->id=="index");
    if($modelOrder->finish_cleaning==null or $modelOrder->start_cleaning==null){
        echo "purchase";
    }elseif(strtotime(date("Y-m-d H:i:s"))> strtotime($modelOrder->startCleaningDate) ){
        echo "compleated";
    }elseif((strtotime(date("Y-m-d H:i:s")))> strtotime($modelOrder->startCleaningDate) ){
        echo "purchase";
    }


}else{
    echo "purchase";
}?>
">
    <div class="revenueExpenses_header">
        <div class="revenueExpenses_headerContent">
            <div class="revenueExpensesDate">
                <div class="date">  <?=rudate("d F H:i", strtotime( $modelOrder->date_cleaning))?></div>
                <div class="address">
                    <?if($modelOrder->adress_id!=null):?>
                        <?=$modelOrder->adress->normadress?>
                    <?else:?>
                        (не назначен)
                    <?endif?>
                </div>
            </div>
            <div class="revenueExpensesSum">
                <div class="revenueExpensesSum_content">
                    <p class="revenueExpensesSum_title">Вознаграждение</p>
                    <div class="sum">

<!--                        --><?//=ceil(($sumOrder*Yii::$app->params["rewardCleaner"])/$modelOrder->packetTime->count_cleaner )?><!-- <span>г</span>-->
                    <?if($modelOrder->pay_form_id!=3){ ?>
                        <?=ceil($modelOrder->reward/$modelOrder->packetTime->count_cleaner )?><span>г</span>
                        <?=($modelOrder->packetTime->count_cleaner >1)?'<i class="revenueExpensesSum_title">/ чел.</i>':''?>
                    <?}else{?>
                        Бесплатно
                    <?}?>
                    </div>
                </div>
                <div class="revenueExpensesSum_content">
                    <p class="revenueExpensesSum_title">Сумма заказа </p>
                    <div class="sum">
<!--                       --><?//$sumOrder = $modelOrder->sumOrder($modelOrder->orderAdditionals)?>
<!--                        --><?//=$sumOrder?><!-- <span>г</span>-->
                        <?=$modelOrder->sum_order?> <span>г</span>
                    </div>
                </div>

            </div>
            <div class="revenueExpensesBtn">
                <? if($modelOrder->cleaner_id==null):?>
                    <a class="btn " href="/cleaner/accept?id=<?=$modelOrder->id?>">
                        Принять
                    </a>

                <? elseif(count($modelOrder->addCleaners) + 1 < $modelOrder->packetTime->count_cleaner
                    and
                    Yii::$app->controller->action->id!="my"):?>
                    <a class="btn " href="/cleaner/accept?id=<?=$modelOrder->id?>">
                        Принять
                    </a>
                <?else:?>

                    <?
                    if($modelOrder->startCleaningDate==null && $modelOrder->finishCleaningDate==null){
                        echo '  <div class="btn">Ожидание</div>';
//                        Yii::trace(date("Y-m-d H:i:s"));
//                        Yii::trace($modelOrder->startCleaningDate);
                    }
                    elseif($modelOrder->finishCleaningDate!=null && $modelOrder->startCleaningDate!=null) {
                        echo '  <div class="btn disabled">Завершен</div>';
                    }elseif($modelOrder->startCleaningDate!=null &&  $modelOrder->finishCleaningDate==null){
                        echo '  <div class="btn btnPerformed">Выполняется</div>';
                    }else{
                        echo '  <div class="btn btnPerformed">Ожидание</div>';
                    }
                    ?>

                <?endif;?>
            </div>
        </div>
    </div>
    <div class="revenueExpenses_content">
        <div class="orderingContent">
            <div class="orderingContentLeft">
                <div class="orderingContent_info">
                    <div class="orderingContent_infoText">
                        <? $countCleaner = $modelOrder->packetTime->count_cleaner?>
                        <!--                        --><?// $sumSelected = ($modelOrder->cleaner_id!=null)? 1: 0?>
                        <!--                        --><?// $sumSelected += count( $modelOrder->addCleaners)?>
                        <!--                        <p>--><?//=$sumSelected?><!-- из --><?//=$countCleaner?><!--  --><?//=pluralForm($countCleaner, "уорщика", "уборщиков","уборщиков" )?><!-- </p>-->
                        <p> <?=$countCleaner?>  <?=pluralForm($countCleaner, "клинер", "клинера","клинеров" )?> </p>

                    </div>
                    <div class="orderingContent_infoTitle">
                        Параметры:
                    </div>
                    <div class="orderingContent_infoText">

                        <?if($modelOrder->packet!=null):?>

                            <?=$modelOrder->packet->name?>
                            <?if($modelOrder->packetTime!=null):?>
                                ~ <?=$modelOrder->timeAllClean($modelOrder->orderAdditionals)?> <?=pluralForm($modelOrder->packetTime->hours_max,"час","часа","часов")?>
                            <?endif;?>
                        <?else:?>

                            (пакет не назначен)

                        <?endif?>
                        <p>(<?=$modelOrder->area?> м2  <?=$modelOrder->sanuzel?> <?=pluralForm($modelOrder->sanuzel,"санузел","санузла","санузлов")?>)</p>
                    </div>
                </div>
                <div class="orderingContent_info">

                    <?if(count($modelOrder->orderAdditionals )>0):?>
                        <div class="orderingContent_infoTitle">
                            Доп. работы:
                        </div>
                        <div class="orderingContent_infoText">
                            <? foreach ($modelOrder->orderAdditionals as $orderAdditionals): ?>
                                <?=$orderAdditionals->additional->name?>
                                <?if($orderAdditionals->count>0):?>
                                    (<?=$orderAdditionals->count?>шт .)
                                <?endif?>
                            <?endforeach ?>
                        </div>
                    <?endif;?>
                </div>

                <?
                $modelCashbox=$modelOrder->cashbox;
                if(   $modelOrder->pay_form_id==1 &&
                        isset($modelCashbox) &&
                        $modelOrder->sum_order-$modelCashbox->sum>0
                    ){ ?>
                <div class="orderingContent_info addPayNoncash">
                    <div class="orderingContent_infoTitle ">Доплата :</div>
                    <div class="orderingContent_infoText">
                        <?=$modelOrder->sum_order-$modelCashbox->sum?> <span>г</span>
                    </div>
                </div>
                <?}?>
                <?if($modelOrder->comment_client):?>
                    <div class="orderingContent_info">
                        <div class="orderingContent_infoTitle">
                            Клиент:
                        </div>
                        <div class="orderingContent_infoText">
                            <?=$modelOrder->comment_client?>
                        </div>
                    </div>
                <?endif?>
                <?if($modelOrder->fine_comment):?>
                    <div class="orderingContent_info">
                        <div class="orderingContent_infoTitle">
                            Администратор:
                        </div>
                        <div class="orderingContent_infoText">
                            <?=$modelOrder->fine_comment?>
                            <br>
                            ( Штраф  <?=($modelOrder->fine+$modelOrder->fine_noncash)/$modelOrder->packetTime->count_cleaner?> <span>р.</span>)
                        </div>
                    </div>
                <?endif;?>
            </div>
            <div class="orderingContentRight">
                <div class="orderingContent_info">
                    <div class="orderingContent_infoTitle">

                    </div>
                    <div class="orderingContent_infoText">
                        <p class="card">
                            <?if($modelOrder->pay_form_id!=null):?>
                                <?=$modelOrder->payForm->name?>
                            <? else: ?>
                                (Не назначен)
                            <? endif?>
                        </p>
                    </div>
                </div>
                <div class="orderingContent_info">
                    <div class="orderingContent_infoTitle">
                        Заказ
                    </div>
                    <div class="orderingContent_infoText">
                        №<?=$modelOrder->id?>
                    </div>
                </div>
            </div>

<!--            --><?//  Yii::trace(date("Y-m-d H:i:s"));?>
<!--            --><?//  Yii::trace(date('Y-m-d  H:i:s', strtotime("+3 hours", strtotime(date("Y-m-d H:i:s")))));?>
            <?if( Yii::$app->controller->action->id=="my"):?>
                <? if ($modelOrder->cleaner_id== Yii::$app->user->id
                    and ($modelOrder->start_cleaning == null OR $modelOrder->finish_cleaning == null)
                    and mktime(date("H")+Yii::$app->params["timeDifference"],date("i"),date("s"),date("m"),date("d"),date("Y"))
                    > strtotime($modelOrder->date_cleaning )): ?>



                    <div class="cleaningSchedule">
                        <?php $form = ActiveForm::begin([
                            'enableClientValidation' => true,
                            'id' => 'time-form' . $modelOrder->id,
                            'action' => '/cleaner/time-accept?id=' . $modelOrder->id,
                            'method' => 'POST',

                            'fieldConfig' => [
                                "template" => '{input}{error}'
                            ]
                        ]); ?>

                        <?
                        if($modelOrder->start_cleaning !=null){
                            $modelOrder->start_cleaning = date("H:i",strtotime($modelOrder->start_cleaning ));
                        }
                        if($modelOrder->finish_cleaning  !=null){
                            $modelOrder->finish_cleaning  = date("H:i",strtotime($modelOrder->finish_cleaning ));
                        }
                        ?>

                        <div class="cleaningScheduleBlock">
                            <div class="cleaningScheduleBlock_title">Начало уборки:</div>

                            <?= $form->field($modelOrder, 'start_cleaning', [
                                // "template" => "{input}{error}",
                                "options" => [
                                    "class" => "envelope_inputCliner"
                                ]
                            ])->textInput([
                                "class" => "cleaningSchedule_time",
                                "placeholder" => "00:00:00"
                            ]) ?>
                        </div>
                        <div class="cleaningScheduleBlock">
                            <div class="cleaningScheduleBlock_title">Окончание:</div>

                            <?= $form->field($modelOrder, 'finish_cleaning', [
                                // "template" => "{input}{error}",
                                "options" => [
                                    "class" => "envelope_inputCliner"
                                ]
                            ])->textInput([
                                "class" => "cleaningSchedule_time",
                                "placeholder" => "00:00:00"
                            ]) ?>
                        </div>

                        <?=Html::submitButton("Сохранить",["class"=>"btn"])?>
                        <?$form->errorSummary($modelOrder)?>
                        <?php ActiveForm::end(); ?>

                    </div>
                <? else: ?>
                    <? if ($modelOrder->cleaner_id!= Yii::$app->user->id):?>
                        <div class="cleaningSchedule">


                            <div class="cleaningScheduleBlock">

                                Вы являетесь напарником, время уборки выставит уборщик №1
                            </div>
                        </div>
                    <? endif;?>

                    <div class="cleaningSchedule">

                    <?if(isset ($modelOrder->start_cleaning) && isset ($modelOrder->finish_cleaning)): ?>
                        <div class="cleaningScheduleBlock">

                            <div class="cleaningScheduleBlock_title">Начало уборки:</div>
                            <?=$modelOrder->start_cleaning?>
                        </div>
                        <div class="cleaningScheduleBlock">
                            <div class="cleaningScheduleBlock_title">Окончание:</div>
                            <?=$modelOrder->finish_cleaning?>

                        </div>
                    <? endif;?>


                    </div>
                <? endif; ?>
            <? endif; ?>
        </div>
    </div>
</div>
