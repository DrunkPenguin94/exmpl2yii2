<?php


namespace app\components;


use yii;
use yii\db\Query;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;


class WidgetCustomPaginator extends Widget
{
    public $allPage=1;
    public $page = 1;
	public function init()
	{
		parent::init();
	}

	public function run()
	{


		return $this->render("/custompaginator/paginator", [
            'page'=>$this->page,
            'allPage'=>$this->allPage,
		]);

	}


}