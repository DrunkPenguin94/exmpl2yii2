<?php


namespace app\components;


use common\models\Packet;
use common\models\Rayon;
use common\models\Regular;
use common\models\Review;
use common\models\User;
use frontend\models\ContactForm;

use yii;
use yii\db\Query;
use yii\base\Widget;

use yii\data\ActiveDataProvider;
Use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\SignupForm;
use yii\helpers\Url;
use common\models\Main;

class WidgetMenuLogin extends Widget
{


	public function init()
	{
		parent::init();
	}

	public function run()
	{
        if(!Yii::$app->user->isGuest){
            $modelUser = User::find()->where(["id"=>Yii::$app->user->id])->one();
        }else{
            $modelUser =null;
        }
        return $this->render("/layouts/_login", [
            "modelUser"=>$modelUser
        ]);

	}


}