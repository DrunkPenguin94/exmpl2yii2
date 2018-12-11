<?
/* @var $arrAdditional  \common\models\Additional[]*/
/* @var $modelOrder  \common\models\Order*/

$arrOrderAdditionals = $modelOrder->orderAdditionals;
?>


<div class="orders_itemList">


    <?foreach ($arrAdditional as $modelAdditional):?>

        <?
        $valueAdditional=0;
        foreach ($arrOrderAdditionals as $modelOrderAdditional){
            if($modelAdditional->id==$modelOrderAdditional->additional_id){
                $valueAdditional=$modelOrderAdditional->count;
            }
        }?>
    <div class="orders_item <?=$modelAdditional->id==4 ? 'tablewareInformation-trigger' : ''?>">

		<div class="orders_itemImg">
            <img src="/img/additional_<?=$modelAdditional->id?>.png" alt=""/>
        </div>
        <div class="orders_itemTitle">
            <p>
                <?=$modelAdditional->name?>
            </p>
        </div>
        <div class="orders_col">
        <?if($mod!="finished" && $mod!="plan" && $modelOrder->modered!=1  && $mod!="nearestOrder"){?>

            <span class="minus"></span>
            <div class="orders_col_input">
                <input type="text" readonly name="arrAdditinal[<?=$modelAdditional->id?>]" value="<?=$valueAdditional?>" data-additional-id="<?=$modelAdditional->id?>" class="addotionalOrderInput" size="5"/>
            </div>
            <span class="plus"></span>

        <?}else{?>
            <span class="minus_disabled"></span>
            <div class="orders_col_input">
                <input disabled type="text" readonly name="arrAdditinal[<?=$modelAdditional->id?>]" value="<?=$valueAdditional?>" data-additional-id="<?=$modelAdditional->id?>" class="addotionalOrderInput" size="5"/>
            </div>
            <span class="plus_disabled"></span>
        <?}?>
        </div>
    </div>

    <?endforeach?>
</div>