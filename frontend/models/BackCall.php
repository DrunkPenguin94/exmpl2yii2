<?php
/**
 * Created by PhpStorm.
 * User: Drunk Penguin
 * Date: 19.09.2017
 * Time: 14:18
 */

namespace frontend\models;

use common\models\Feedback;
use yii\base\Model;

class BackCall extends Model
{
    public $name;

    public $telephone;


    public function rules()
    {
        return [
            [['name',  'telephone'], 'required'],


            [['name', 'telephone'], 'string', 'max' => 30],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Имя',

            'telephone' => 'Телефон',

        ];
    }


    public function sendEmail(){

        $model=new Feedback;
        $model->name=$this->name;
        $model->phone=$this->telephone;
        if($model->validate())
            $model->save();
        else
            Yii::trace($model->getErrors());

        return \Yii::$app->mailer->compose(['html' => 'callBack-html', 'text' => 'callBack-text'], ['model' => $this])
            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ' robot'])

            ->setTo(\Yii::$app->params['infoEmail'])
            ->setSubject('Заказ обратного звонка' )
            ->send();

    }
}