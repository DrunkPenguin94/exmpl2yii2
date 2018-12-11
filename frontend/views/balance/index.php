<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Баланс';

$balance=0;

$modelUser = \common\models\User::findOne(Yii::$app->user->id);
$is_cleaner =$modelUser->is_cleaner;
$balance=$modelUser->balance+$modelUser->balance_noncash;
?>

    <section class="content">
        <div class="balancePage center">
            <div class="balancePage_title">
                Ваш текущий баланс<div class="balancePage_titleBalance"><?=$balance?> <span>г</span></div>
            </div>
            <div class="costSection">
                <div class="costBlock">
                    <div class="costBlock_title">
                        Вознаграждение
                        <br/>
                        за текущий месяц:
                    </div>
                    <div class="costBlock_price">
                        <?=empty($lastMonth) ? "0" : $lastMonth?> <span>г</span>
                    </div>
                </div>
<!--                <div class="costBlock">-->
<!--                    <div class="costBlock_title">-->
<!--                        Покупки-->
<!--                        <br/>-->
<!--                        в текущем месяце:-->
<!--                    </div>-->
<!--                    <div class="costBlock_price">-->
<!--                        0 <span>г</span>-->
<!--                    </div>-->
<!--                </div>-->
            </div>
            <div class="revenueExpenses_section">
                <div class="revenueExpenses_sectionList">
                    <?
                    foreach($model as $value){

                        if($value->arrival_service=="User" && $value->expense_service=="Order"){
                    ?>


                    <div class="revenueExpenses_block remuneration">
                        <div class="revenueExpenses_header">
                            <div class="revenueExpenses_block_date"><?=date("d.m.Y",strtotime($value->date))?></div>
                            <div class="revenueExpenses_block_text">
                                <div class="revenueExpenses_block_textContent">
                                    Вознаграждение
                                    <br/>
                                    заказ № <span><?=$value->expense_id?></span>
                                </div>
                            </div>
                            <div class="revenueExpenses_block_sum">+ <?=$value->sum?> <span>г</span></div>
                        </div>
                        <div class="revenueExpenses_content">
                            <div class="remuneration_content">
<!--                                <div class="remuneration_fine">-->
<!--                                    <div class="title">Штраф:</div>-->
<!--                                    <div class="text">- 150 <span>г</span></div>-->
<!--                                </div>-->
                                <div class="remuneration_address">
                                    <?=$arrAdressName[$value->expense_id]?>
                                </div>

                            </div>
                        </div>
                    </div>
                    <?}elseif($value->expense_service=="User" && $value->arrival_service=="Order") {
                            ?>

                            <div class="revenueExpenses_block withdrawal">
                                <div class="revenueExpenses_block_date"><?=date("d.m.y",strtotime($value->date))?></div>
                                <div class="revenueExpenses_block_text">
                                    <div class="revenueExpenses_block_textContent">
                                        Отмена<br> вознаграждения
                                    </div>
                                </div>
                                <div class="revenueExpenses_block_sum"><?=$value->sum?> <span>г</span></div>
                            </div>

                            <?
                        }elseif($value->expense_service=="Company" && $value->arrival_service=="User"){?>
                            <div class="revenueExpenses_block withdrawal">
                                <div class="revenueExpenses_block_date"><?=date("d.m.Y",strtotime($value->date))?></div>
                                <div class="revenueExpenses_block_text">
                                    <div class="revenueExpenses_block_textContent">
                                        Снятие наличных
                                    </div>
                                </div>
                                <div class="revenueExpenses_block_sum">-<?=$value->sum==0 ? $value->sum_noncash : $value->sum  ?> <span>г</span></div>
                            </div>

                    <?    }
                    }
                    ?>


                    <?
                        $page;//текущая страниц
                        //всего страниц
                        $countItemOnPage;//количество постов
                        $arrPage[2]=$page;
                        $arrPage[1]=$page==1 ? "" : $page-1;
                        $arrPage[0]=$page==1 ? "" : ($page-1==1 ? "" : $page-2);
                        $arrPage[3]=$page==$allPage ? "" : $page+1;
                        $arrPage[4]=$page==$allPage ? "" : ($page+1==$allPage ? "" : $page+2)
                    ?>
                <div class="paginationBlock">
                    <ul class="paginate">
                        <li class="prev <?=$arrPage[0]==""? "disabled": ""?>"><a href="<?=$arrPage[0]=="" ? "#" : "/balance?page=".$arrPage[0]?>"><span></span></a></li>
                        <li><a href="/balance?page=<?=$arrPage[1]?>" data-page="0"><?=$arrPage[1]?></a></li>
                        <li  class="active"><a href="/balance?page=<?=$arrPage[2]?>" data-page="1"><?=$arrPage[2]?></a></li>
                        <li><a href="/balance?page=<?=$arrPage[3]?>" data-page=""><?=$arrPage[3]?></a></li>
                        <li class="next <?=$arrPage[4]==""? "disabled": ""?>"><a href="<?=$arrPage[4]=="" ? "#" : "/balance?page=".$arrPage[4]?>" data-page="1"></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>


