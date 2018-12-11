<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use dosamigos\ckeditor\CKEditor;
use yii\widgets\Pjax;
?>


<section class="content">
    <div class="personalDataPage center">
        <div class="personalDataPage_title">Личные данные</div>
        <div class="personalDataPage_content">
<!--            --><?php //Pjax::begin([
//                'id' => 'pjax_update_data_user_info',
//                "timeout" => 10000
//            ]) ?>
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
                    <?= $form->errorSummary($modelUpdateDataUser,[
                                            "class"=>"envelope_error",
                                        ]) ?>
                    <?= $form->field($modelUpdateDataUser, 'name')->textInput([
                        "placeholder" => $userData['name']
                    ]) ?>
                    <?= $form->field($modelUpdateDataUser, 'family')->textInput([
                        "placeholder" => $userData['family']
                    ]) ?>
                    <?= $form->field($modelUpdateDataUser, 'email')->textInput([
                        "placeholder" => $userData['email']
                    ]) ?>
                    <?= $form->field($modelUpdateDataUser, 'phone')->textInput([
                        "placeholder" => $userData['phone']
                    ]) ?>
                    <?php if (Yii::$app->getSession()->hasFlash('error')) {
                        echo '<p>' . Yii::$app->getSession()->getFlash('error') . '</p>';
                    } ?>

                </div>
                <button class="btn btnCliner">Сохранить изменения</button>
            <?php ActiveForm::end();?>
<!--            --><?php //Pjax::end() ?>

            <?php Pjax::begin([
                'id' => 'pjax_update_data_user_pass',
                "timeout" => 10000
            ]) ?>
            <?php $form = ActiveForm::begin([
               'id' => 'form-updatedatauserpass',
                'options' => [
                    'data-pjax' => true,
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
                <div class="personalData_passwordTitle">Изменить пароль</div>
                <div class="inputListBlock">
                    <?= $form->errorSummary($modelUpdateDataUser,[
                        "class"=>"envelope_error",
                    ]) ?>
                    <div class="envelope_error"><p><?=Yii::$app->request->cookies['differentInfo'] ?></p></div>
                    <?= $form->field($modelUpdateDataUser, 'password_old', [
                    ])->passwordInput([
                        "placeholder" => "Старый пароль"
                    ]) ?>
                    <?= $form->field($modelUpdateDataUser, 'password_new', [
                    ])->passwordInput([
                        "placeholder" => "Новый пароль"
                    ]) ?>
                    <?= $form->field($modelUpdateDataUser, 'password_new_repeat', [
                    ])->passwordInput([
                        "placeholder" => "Новый пароль еще раз"
                    ]) ?>


                </div>
                <button class="btn btnCliner" >Сохранить пароль</button>

            <?php ActiveForm::end();
            //Yii::$app->request->cookies->remove('differentInfo');
            ?>
            <?php Pjax::end() ?>

        </div>
    </div>
</section>


