<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use dosamigos\ckeditor\CKEditor;
use yii\widgets\Pjax;
$this->title='Личные данные';
?>


<section class="content">
    <div class="personalDataPage center">
        <div class="personalDataPage_title">Личные данные</div>
        <div class="personalDataPage_content">
            <?if(empty($userData->name) || empty($userData->email) || empty($userData->family) || empty($userData->phone)){?>
                <p class="success_save_info">Для создания заказа вам нужно заполнить обязательные поля :</p>
                <?=empty($userData->name) ? "<p class=\"success_save_info_2\">Имя</p>" : ""?>
                <?=empty($userData->family) ? "<p class=\"success_save_info_2\">Фамилия</p>" : ""?>
                <?=empty($userData->email) ? "<p class=\"success_save_info_2\">Электронный адрес</p>" : ""?>
                <?=empty($userData->phone) ? "<p class=\"success_save_info_2\">Телефон</p>" : ""?>

 <?           }?>


            <?php if (Yii::$app->getSession()->hasFlash('success')) {
                foreach(Yii::$app->getSession()->getFlash('success') as $value)
                    echo '<p class="success_save_info">' . $value . '</p>';
            } ?>
            <?php $form = ActiveForm::begin([
                //   'enableAjaxValidation' => true,
                'id' => 'form-updatedatauserinfo',
                'options' => [
//                    'data-pjax' => true,
                    'class' => '',
                ],
                //'action' => '/site/signup',
                'method' => 'POST',
                'fieldConfig' => [
                    "errorOptions" => [
                        'class' => 'error_text'
                    ],
                    "options" => [
                        "class" => "envelope_inputCliner"

                    ],
                    "template" => "{input}"
                ],

            ]);?>
                <div class="inputListBlock">
                    <?= $form->errorSummary($updateUser,[
                                            "class"=>"envelope_error",
                                        ]) ?>
                    <?= $form->field($updateUser, 'name')->textInput([
                        "placeholder" => "Имя"
                    ]) ?>
                    <?= $form->field($updateUser, 'family')->textInput([
                        "placeholder" => "Фамилия"
                    ]) ?>
                    <?= $form->field($updateUser, 'email')->textInput([
                        "placeholder" => "Электронный адрес"
                    ]) ?>
                    <?= $form->field($updateUser, 'phone')->textInput([
                        "placeholder" => "Телефон"
                    ]) ?>

                </div>
                <button class="btn btnCliner">Сохранить изменения</button>
            <?php ActiveForm::end();?>
<!---->
<? if(!empty($userData->password_hash)) { ?>
            <?php $form = ActiveForm::begin([
                'id' => 'form-updatedatauserpass',
                'options' => [
                    'data-pjax' => true,
                    'class' => '',
                ],
                //'action' => '/site/signup',

                'fieldConfig' => [
                    "errorOptions" => [
                        'class' => 'error_text'
                    ],
                    "options" => [
                        "class" => "envelope_inputCliner"

                    ],
                    "template" => "{input}"
                ],

            ]); ?>
            <div class="personalData_passwordTitle">Изменить пароль</div>
            <div class="inputListBlock">
                <?= $form->errorSummary($updatePassword, [
                    "class" => "envelope_error",
                ]) ?>
                <div class="envelope_error"><p><?= Yii::$app->request->cookies['differentInfo'] ?></p></div>
                <?= $form->field($updatePassword, 'password', [
                ])->passwordInput([
                    "placeholder" => "Старый пароль"
                ]) ?>
                <?= $form->field($updatePassword, 'password_new', [
                ])->passwordInput([
                    "placeholder" => "Новый пароль"
                ]) ?>
                <?= $form->field($updatePassword, 'password_new_repeat', [
                ])->passwordInput([
                    "placeholder" => "Новый пароль еще раз"
                ]) ?>

                <?php if (Yii::$app->getSession()->hasFlash('error')) {
                    foreach (Yii::$app->getSession()->getFlash('error') as $value)
                        echo '<p class="success_save_info">' . $value . '</p>';
                } ?>

            </div>
            <button class="btn btnCliner">Сохранить пароль</button>

            <?php ActiveForm::end();
}
//            ?>
<!--     -->

        </div>
    </div>
</section>


