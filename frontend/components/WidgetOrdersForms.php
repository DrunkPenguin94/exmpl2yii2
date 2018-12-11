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

class WidgetOrdersForms extends Widget
{


	public function init()
	{
		parent::init();
	}

	public function run()
	{
       $arrPackets = Packet::find()->orderBy("id")->all();
        $arrRegular = Regular::find()->orderBy("id")->all();
        $arrRayon = Rayon::find()->orderBy("id")->all();
		return $this->render("/orders/_forms", [
            "arrPackets" =>$arrPackets,
            "arrRegular"=>$arrRegular,
            "arrRayon"=>$arrRayon
		]);

	}


}