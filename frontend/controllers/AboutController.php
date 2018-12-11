<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;


use common\models\Coupon;
/**
 * Site controller
 */
class AboutController extends Controller
{


    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionTest()
    {

//        $model = new Coupon;
       // Yii::trace($model->createFirstClean(77));
        $data="2017-05-15 09:12:00";
        Yii::trace(strtotime($data));
        Yii::trace(date("d.m.Y",strtotime($data)));
        return $this->redirect("/");
    }

}
