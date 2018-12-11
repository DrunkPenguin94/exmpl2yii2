<?php

/* @var $this yii\web\View */
/* @var $model \common\models\Question */
/* @var $model_old \common\models\Question */
/* @var $dataProvider \yii\data\ActiveDataProvider*/

use yii\helpers\Html;

$this->title = 'Заказы';

?>

<section class="content">
    <div class="ordersPage center">
        <ul class="ordersNav">
            <li>
                <a href="/cleaner">Текущие заказы</a>
            </li>
            <li class="active">
                <a >Мои заказы</a>
            </li>
        </ul>
        <div class="revenueExpenses_section">
            <div class="revenueExpenses_sectionList">


                <?foreach ( $dataProvider->getModels() as $modelOrder):?>
                    <?=$this->render("_item",[
                      //  "arrAdditional"=> $arrAdditional,
                        "modelOrder"=>$modelOrder
                    ])?>

                <?endforeach;?>

            </div>


            <?= \yii\widgets\LinkPager::widget([
                "pagination"=> $dataProvider->pagination,
                "options" => [
                    'class' => 'paginate',
                ],
                "nextPageLabel"=>"",
//    "nextPageCssClass"=>"spacer",
//    "prevPageLabel"=>"",
//    "prevPageCssClass"=>"spacer",
//    "firstPageLabel" => 1,
//    "lastPageLabel" => $dataProvider->pagination->pageCount,
                'maxButtonCount' => 3,
                'registerLinkTags'=>false

            ]);?>
        </div>
    </div>
</section>