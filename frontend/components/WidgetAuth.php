<?php

namespace app\components;

use common\models\Customer;
use common\models\User;
use frontend\models\ContactForm;
use frontend\models\PayForm;
use yii;
use yii\db\Query;
use yii\base\Widget;

use yii\data\ActiveDataProvider;
Use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\SignupForm;
use yii\helpers\Url;

use frontend\models\BackCall;
class WidgetAuth extends Widget
{
    public $model;
    public $giftsCount;

    public function init()
    {
        parent::init();
    }

    public function run()
    {

        Url::remember($_SERVER['REQUEST_URI'],"auth");
        $modelLogin = new LoginForm();
        $modelPasswordReset = new PasswordResetRequestForm();
        $modelSignupForm = new SignupForm();
        $modelBackCall=new BackCall;
        $request = Yii::$app->request->post();





        $report = ["responce" => null, "title" => "", "message" => ""];

       if (isset($request["SignupForm"])) {
          // Yii::trace('LOLO2');
            if ($modelSignupForm->load(Yii::$app->request->post())) {
                if ($user = $modelSignupForm->signup()) {
                    $report = [
                        "responce" => true,
                        "title" => "Вы успешно зарегистрировались!",
                        "message" => " Вам на почту отправлена ссылка для активации учетной записи."
                    ];
                }else{
                    $report = [
                        "responce" => false,
                        "title" => "Произошла ошибка!",
                        "message" => " Обратитесь к администратору."
                    ];
                }

            }
        }





        return $this->render("/auth/index", [
            "modelLogin" => $modelLogin,
            "modelPasswordReset" => $modelPasswordReset,
            "modelSignupForm" => $modelSignupForm,
            "report" => $report,
            "modelBackCall"=>$modelBackCall

        ]);

    }


}