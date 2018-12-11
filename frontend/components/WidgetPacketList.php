<?php


namespace app\components;

use common\models\Customer;
use common\models\Packet;
use common\models\Review;
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
use common\models\Main;

class WidgetPacketList extends Widget
{


	public function init()
	{
		parent::init();
	}

	public function run()
	{

        $modelsPacket = Packet::find()->orderBy("id")->all();

		return $this->render("/packet/list", [
			"modelsPacket" => $modelsPacket

		]);

	}


}