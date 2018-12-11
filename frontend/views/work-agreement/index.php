<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

use frontend\models\DocumentPagesData;
$doc_data=new DocumentPagesData();
$doc_data=$doc_data->getData(4);
//Yii::trace($doc_data);

$this->title = $doc_data["title"];


?>
<section class="content">
    <div class="workAgreement center">
        <div class="workAgreement_title"><?=$doc_data["title"]?></div>

        <div class="termsUse">
            <div class="versionAgreement center">
    <!--            <div class="versionAgreement_title">Версия 1.2 от 04 апреля 2016</div>-->
                <div class="versionAgreement_text">
                    <?=$doc_data["version_text"]?>

                </div>
            </div>
            <div class="documentBlock">
                <div class="documentBlock_item">
                    <div class="documentBlock_itemText">
                       <?=$doc_data["text_list"]?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
