<?php


namespace app\components;

use common\models\Customer;
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

class WidgetReviewSlider extends Widget
{


	public function init()
	{
		parent::init();
	}

	public function run()
	{

		$modelsReview = Review::find()->all();

		return $this->render("/review/slider", [
			"modelsReview" => $modelsReview

		]);

	}


}