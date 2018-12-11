<?
/* @var $modelOrder \common\models\Order*/
if($mod!="finished" ) {
    $this->registerJs("
    
  
    
    if (document.getElementById('form_transferCleaning')){
       
		$(\".transferCleaning-trigger\").off('click');
        $(\".transferCleaning-trigger\").click(global_transferCleaning.toggle.bind(global_transferCleaning));
	}
    if (document.getElementById('form_editDetails')){
		//var commonDialog = new DialogFx(document.getElementById('form_editDetails'));
		$(\".editDetails-trigger\").off('click');
		$(\".editDetails-trigger\").click(global_editDetail.toggle.bind(global_editDetail));
		console.log(\"ajax v\");
		
	}
	/*
	if (document.getElementById('form_changeVolume')){
	    
	    $(\".changeVolume-trigger\").off('click');
    //  var commonDialog = new DialogFx(document.getElementById('form_changeVolume'));
    //  $(\".changeVolume-trigger\").click(commonDialog.toggle.bind(commonDialog));
		$(\".changeVolume-trigger\").click(globalWindowChangeVolume.toggle.bind(globalWindowChangeVolume));
		
	//	globalWindowChangeVolume=commonDialog;
	}
	*/
	if (document.getElementById('form_changeComment')){
		var commonDialog = new DialogFx(document.getElementById('form_changeComment'));
		$(\".changeComment-trigger\").click(commonDialog.toggle.bind(commonDialog));
	}
	
	if (document.getElementById('form_changeAddress')){
		var commonDialog = new DialogFx(document.getElementById('form_changeAddress'));
		$(\".changeAddress-trigger\").click(commonDialog.toggle.bind(commonDialog));
	}

	if (document.getElementById('form_regularCleaning')){
		var commonDialog = new DialogFx(document.getElementById('form_regularCleaning'));
		$(\".regularCleaning-trigger\").click(commonDialog.toggle.bind(commonDialog));
	}
	
	
	$(\".editDetails-trigger\").click(function () {
		var packet_id = $(this).data(\"packet-id\");
		var regular_id = $(this).data(\"regular-id\");
		 parentDiv = $(this).closest(\".ordersBlock_content\");
		var order_id= parentDiv.data(\"order-id\");
		$(\"#select_editDetails_packet\").val(packet_id).change();
		$(\"#select_editDetails_regular\").val(regular_id).change();
		$(\"#select_editDetails_order\").val(order_id);

		$(\"#form_editDetails .formBlock\").removeClass(\"hidden\");
		$(\"#form_editDetails .resultBlock\").addClass(\"hidden\");
		
		$('#select_editDetails_regular-styler li').show();
		
		arrTextOption=[];
		
		if(regular_id==1){
		    $('#select_editDetails_regular option').each(function(indx){
		        if($(this).val()!='1'){
		            arrTextOption.push($(this).text());
		        }
            });
		    $('#form_editDetails .btnOrderAccept').show();
		    $('#form_editDetails .btnOrderAcceptAll').hide();
		}else{
            $('#select_editDetails_regular option').each(function(indx){
                    if($(this).val()=='1' || $(this).val()==''){
                        arrTextOption.push($(this).text());
                    }
            });
		    $('#form_editDetails .btnOrderAccept').hide();
		    $('#form_editDetails .btnOrderAcceptAll').show();
		}
		
		$('#select_editDetails_regular-styler li').each(function(){
		    for(var i=0;i<arrTextOption.length;i++){
		        if($(this).text()==arrTextOption[i]){
		            $(this).hide();
		        }
            }
		});
		
		for(var i=0;i<arrTextOption.length;i++){
		    console.log(arrTextOption[i]);
		}
		
		

	});
	
	
	

	$(\".changeAddress-trigger\").click(function () {
		var city = $(this).data(\"adress-city\");
		var rayon = $(this).data(\"adress-rayon\");
		var street = $(this).data(\"adress-street\");
		var home = $(this).data(\"adress-home\");
		var korpus = $(this).data(\"adress-korpus\");
		var kvartira = $(this).data(\"adress-kvartira\");
		/*var country = $(this).data(\"adress-country\");*/




		parentDiv = $(this).closest(\".ordersBlock_content\");
		var order_id= parentDiv.data(\"order-id\");

		$(\"#select_changeAddress_order\").val(order_id);

		$(\"#input_changeAddress_rayon\").val(rayon).change();
		$(\"#input_changeAddress_city\").val(city);

		$(\"#input_changeAddress_street\").val(street);
		$(\"#input_changeAddress_home\").val(home);
		$(\"#input_changeAddress_korpus\").val(korpus);
		$(\"#input_changeAddress_kvartira\").val(kvartira);
	     /*$(\"#input_changeAddress_country\").prop(\"checked\",country);*/

		$(\"#form_changeAddress .formBlock\").removeClass(\"hidden\");
		$(\"#form_changeAddress .resultBlock\").addClass(\"hidden\");

	});
	
	$('.transferCleaning-trigger.transferToPopup').on('click',function () {
	    clearInfoAdditional();
	   // change_select_time();
		var date_cleaning = $(this).data(\"date-cleaning\");
		var time_cleaning = $(this).data(\"time-cleaning\");
		if(date_cleaning!==undefined && time_cleaning.charAt(0)=='0') time_cleaning=time_cleaning.substr(1);
		
		
		if(date_cleaning===undefined || time_cleaning===undefined){
		
		    date_cleaning = $('.ordersBlock.open .transferCleaning-trigger.transferToPopup').data(\"date-cleaning\");
		    time_cleaning = $('.ordersBlock.open .transferCleaning-trigger.transferToPopup').data(\"time-cleaning\");
		}
		
		parentDiv = $(this).closest(\".ordersBlock_content\");
        var order_id= parentDiv.data(\"order-id\");
		if(order_id==null || order_id==''){
		    order_id=$('.ordersBlock_content.open').data(\"order-id\");
		}
		    
		    
		if($(this).hasClass('dateInDetail')){
		    $('#form_transferCleaning .add').hide();
            $('#form_transferCleaning .major').show();
		}else{
		  
		}
		
		$(\"#select_transferCleaning_order\").val(order_id);
		$(\"#input_transferCleaning_date\").val(date_cleaning);
		$(\"#input_transferCleaning_time-styler .jq-selectbox__select-text\").removeClass('placeholder');
		$(\"#input_transferCleaning_time-styler .jq-selectbox__select-text\").text(time_cleaning);
		$(\"#input_transferCleaning_time\").val(time_cleaning);
		
		change_select_time();
//		$(\"#form_changeComment .formBlock\").removeClass(\"hidden\");
//		$(\"#form_changeComment .resultBlock\").addClass(\"hidden\");

	});
	
	$(\".cancelCleaning-trigger\").click(function () {
//		var date_cleaning = $(this).data(\"date-cleaning\");
//		var time_cleaning = $(this).data(\"time-cleaning\");
//		
		var order_id= $(this).data(\"order-id\");
		var subcrb_id= $(this).data(\"order-subscrib-id\");
//	
//		$(\"#select_transferCleaning_order\").val(order_id);
//		$(\"#input_transferCleaning_date\").val(date_cleaning).change();
//		$(\"#input_transferCleaning_time\").val(time_cleaning).change();
	//	$(\"#refuseThatOrder\").attr('href', '/orders/refuse-order?id='+order_id);
		
		
		console.log($(this).data('is-change'));
		if($(this).data('is-change')==0){
            $('#form_transferCleaning .add').show();
            $('#form_transferCleaning .major').hide();
        }else{
            $('#form_transferCleaning .add').hide();
            $('#form_transferCleaning .major').show();
		}
		
		if($(this).data('is-subsrb')==0){
		   // $(\".cancelOrder_yes#refuseThatSubsrb\").hide();
		   $(\".cancelOrder_yes#refuseThatSubsrb\").addClass('button_not_active');  
		   $(\".cancelOrder_yes#refuseThatSubsrb\").attr(\"href\",'javascript:void(0);');
		}else{
		   // $(\".cancelOrder_yes#refuseThatSubsrb\").show();
		    $(\".cancelOrder_yes#refuseThatSubsrb\").removeClass('button_not_active');  
		    $(\".cancelOrder_yes#refuseThatSubsrb\").attr(\"href\",\"\\\\orders\\\\refuse-order?subcrb=\"+subcrb_id);
		    
		}
		
		
		$(\".cancelOrder_yes#refuseThatOrder\").attr(\"href\",\"\\\\orders\\\\refuse-order?id=\"+order_id);
		
		
		if(
//		    (
//		       // $(this).attr(\"data-order-id\")==$(this).attr(\"data-order-subscrib-id\") &&
//                $(this).attr(\"data-is-subsrb\")=='1' 
//            ) ||
//            (
//		     //   $(this).attr(\"data-order-id\")==$(this).attr(\"data-order-subscrib-id\") &&
//                $(this).attr(\"data-is-change\")=='1' 
//            ) 

            $(this).attr(\"data-is-change\")=='1' 
        ){
            $('#form_thatTheCancel .major').show();
            $('#form_thatTheCancel .add').hide();
		}else{
		    $('#form_thatTheCancel .add').show();
            $('#form_thatTheCancel .major').hide();
		}
		
		
	});
	
    $(\".changeComment-trigger\").click(function () {
		var comment = $(this).data(\"comment\");
		parentDiv = $(this).closest(\".ordersBlock_content\");
		var order_id= parentDiv.data(\"order-id\");
			$(\"#select_changeComment_order\").val(order_id);
		$(\"#input_changeComment_comment\").val(comment);
		
		$(\"#form_changeComment .formBlock\").removeClass(\"hidden\");
		$(\"#form_changeComment .resultBlock\").addClass(\"hidden\");

	});
	
	
	
	/*
	$(\".changeVolume-trigger\").click(function () {
		var area = $(this).data(\"area\");
		var sanuzel= $(this).data(\"sanuzel\");
		parentDiv = $(this).closest(\".ordersBlock_content\");
		var order_id= parentDiv.data(\"order-id\");
			
		$(\"#input_changeVolume_area\").val(area);
		$(\"#input_changeVolume_sanuzel\").val(sanuzel);
		
		$(\"#select_changeVolume_order\").val(order_id);
        
        if($(\".editDetails-trigger\").attr('data-regular-id')==1){
            $('#form_changeVolume .btnOrderAccept').show();
            $('#form_changeVolume .btnOrderAcceptAll').hide();console.log(123);
        }else{
            $('#form_changeVolume .btnOrderAccept').hide();
            $('#form_changeVolume .btnOrderAcceptAll').show();console.log(152);
        }
        
	});
	
	*/

");
}

$flagEdit= $mod=="finished" ||  $mod=="plan" || $modelOrder->modered==1  || $mod=="nearestOrder";
if($flagEdit) {
    $contentInfoString = "contentInfo_string_finished";
    $contentInfoStringLink = "contentInfo_stringLink_finished";
}else{

    $contentInfoString = "contentInfo_string";
    $contentInfoStringLink = "contentInfo_stringLink";
}
?>
<div class="<?=$contentInfoString?>">
    Пакет
    <div class="<?=$contentInfoStringLink?>">

        <?if($modelOrder->packet!=null):?>
            <a  class="<?= $flagEdit ? "" : "editDetails-trigger" ?>" href="javascript:void(0);" data-packet-id="<?=$modelOrder->packet_id?>" data-regular-id="<?=$modelOrder->regular_id?>">
                <?=$modelOrder->packet->name?>
            </a>
        <?else:?>
            <a  class="<?= $flagEdit ? "" : "editDetails-trigger" ?>" href="javascript:void(0);">
                (не назначен)
            </a>
        <?endif?>


    </div>
</div>

<div class="<?=$contentInfoString?>">
    Регулярность
    <div class="<?=$contentInfoStringLink?>">

        <?if($modelOrder->regular!=null):?>
            <a class="<?= $flagEdit ? "" : "editDetails-trigger" ?>" href="javascript:void(0);" data-packet-id="<?=$modelOrder->packet_id?>" data-regular-id="<?=$modelOrder->regular_id?>">
                <?=$modelOrder->regular->name?>
                <?if($modelOrder->regular->discount>0):?>
                    (скидка <?=$modelOrder->regular->discount?>%)
                <?endif;?>
            </a>
        <?else:?>
            <a class="<?= $flagEdit ? "" : "editDetails-trigger" ?>" href="javascript:void(0);" >
                (не назначена)
            </a></a>
        <?endif?>

    </div>
</div>
<div class="<?=$contentInfoString?>">
    Дата уборки
    <div class="<?=$contentInfoStringLink?>">
        <a class="<?= $flagEdit ? "" : "transferCleaning-trigger transferToPopup " ?> dateInDetail" href="javascript:void(0);"
           <??>
           data-date-cleaning="<?=date("d.m.Y ", strtotime($modelOrder->date_cleaning))?>"
           data-time-cleaning="<?=date("H:i", strtotime($modelOrder->date_cleaning))?>"
        >
            <?if($modelOrder->date_cleaning!=null):?>
                <?=rudate("D d M H:i", strtotime( $modelOrder->date_cleaning))?>
            <?else:?>
                (не назначена)
            <?endif?>


        </a>
    </div>
</div>
<div class="contentInfo_string_finished">
    Объем уборки
    <div class="contentInfo_stringLink_finished">
        <a class="<?= $flagEdit ? "" : "changeVolume-trigger " ?>" href="javascript:void(0);" data-area="<?=$modelOrder->area?>" data-sanuzel="<?=$modelOrder->sanuzel?>">
            <?=$modelOrder->area?> м<sup>2</sup>
            <?if($modelOrder->sanuzel>0):?>
                и <?=$modelOrder->sanuzel?> <?=pluralForm($modelOrder->sanuzel,"санузел","санузла","санузлов")?>
            <?endif?>

        </a>
    </div>
</div>

<div class="contentInfo_string_finished">
    Адрес
    <div class="contentInfo_stringLink_finished">

        <?if($modelOrder->adress_id!=null):?>
            <a class="<?= $flagEdit ? "" : "changeAddress-trigger" ?>" href="javascript:void(0);"
               data-adress-city="<?=$modelOrder->adress->city?>"
               data-adress-rayon="<?=$modelOrder->adress->rayon_id?>"
               data-adress-street="<?=$modelOrder->adress->street?>"
               data-adress-home="<?=$modelOrder->adress->home?>"
               data-adress-korpus="<?=$modelOrder->adress->korpus?>"
               data-adress-kvartira="<?=$modelOrder->adress->kvartira?>"
            <!--   data-adress-country="--><?/*=$modelOrder->adress->country*/?>"
            >
                <?=$modelOrder->adress->normadress?>
            </a>
        <?else:?>
            <a class="<?= $flagEdit ? "" : "changeAddress-trigger" ?>" href="javascript:void(0);" >
                (не назначен)
            </a>
        <?endif?>


    </div>
</div>
<div class="<?=$contentInfoString?>">
    Выезд загород
    <div class="<?=$contentInfoStringLink?>">
        <span >
            <?if($modelOrder->adress!=null):?>
                <?=($modelOrder->adress->rayon->country==1)?"Да":"Нет"?>
            <?else:?>
                (не выбран город)
            <?endif?>
        </span>
    </div>
</div>
<div class="<?=$contentInfoString?>">
    Комментарий
    <div class="<?=$contentInfoStringLink?>">
        <a class="<?= $flagEdit ? "" : "changeComment-trigger" ?>" href="javascript:void(0);" data-comment = "<?=$modelOrder->comment_client?>">
            <?if($modelOrder->comment_client!=null and mb_strlen(trim($modelOrder->comment_client))>0):?>
                <?=$modelOrder->comment_client?>
            <?else:?>
                (не оставлен)
            <?endif;?>
        </a>
    </div>
</div>
<!---->
<!--<div class="--><?//=$contentInfoString?><!--">-->
<!--    --><?//=$modelOrder->payForm->name?>
<!--    <div class="--><?//=$contentInfoStringLink?><!--">-->
<!--        --><?//if($modelOrder->regular_id!=1 && $modelOrder->id!=$modelOrder->id_subscription){ ?>
<!--            <a class="changeTypePay-trigger" href="/orders/choice-of-payment?id=--><?//=$modelOrder->id?><!--" target="_blank" data-TypePay = "--><?//=$modelOrder->pay_form_id?><!--">-->
<!--                --><?//=$modelOrder->balance+ $modelOrder->balance_noncash?><!-- р. / --><?//=$modelOrder->sum_order?><!-- р.-->
<!--            </a>-->
<!--        --><?//}else{?>
<!--            <span class="changeTypePay-trigger"  data-TypePay = "--><?//=$modelOrder->pay_form_id?><!--">-->
<!--                --><?//=$modelOrder->balance+ $modelOrder->balance_noncash?><!-- р. / --><?//=$modelOrder->sum_order?><!-- р.-->
<!--            </span>-->
<!--        --><?//}?>
<!---->
<!--    </div>-->
<!--</div>-->