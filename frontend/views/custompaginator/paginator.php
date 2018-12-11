<?php

/* @var $this yii\web\View */
/* @var $modelsReview  \common\models\Review[] */




?>
<ul class="paginate">
<?for($i=1;$i<=$allPage;$i++) {   ?>
    <li class='<?= $i==$page? "active":"" ?>'
    > <a href='<?="/question/page/".$i?>' > <?=$i?> </a> </li>
<?}   ?>
</ul>