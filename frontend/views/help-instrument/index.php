<?php


/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $modelLogin \common\models\LoginForm */
/* @var $modelContact \frontend\models\ContactForm */
use common\models\User;
?>
<div id="check_pass">
<?php
$id=Yii::$app->request->get('pass');
$userData = User::find()->where([id=>Yii::$app->user->getId()])->one();
//if (Yii::$app->getSecurity()->validatePassword($id, $userData['password_hash'])) {
//    echo 'yeap'.'<br>';
//}else
//    echo 'no'.'<br>';


?>
</div>
