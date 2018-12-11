<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use common\models\User;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
/**
 * Site controller
 */
class HelpInstrumentController extends Controller
{


    /**
     * Displays homepage.
     *
     * @return mixed
     */


    public function actionEmail($email='1')
    {

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');

        $text="";

        $userData = User::find()->where(['like','email',$email,false])->one();
        if(!\Yii::$app->user->isGuest) {
            $text.="Такой email уже существует.";
            if (isset($userData['email']))
                $item = array('data' => true,'text' => $text);
            else
                $item = array('data' => false,'text' => $text);
        }else{
            $text.="Пользователя с таким email не существует.";
            if (isset($userData['email']))
                $item = array('data' => false,'text' => $text);
            else
                $item = array('data' => true,'text' => $text);
        }

        return json_encode($item, JSON_UNESCAPED_UNICODE);
    }



}
