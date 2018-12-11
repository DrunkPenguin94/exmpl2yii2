<?php
/**
 * Created by PhpStorm.
 * User: Drunk Penguin
 * Date: 19.09.2017
 * Time: 14:18
 */

namespace frontend\models;


use yii\base\Model;

class NewCleaner  extends Model
{
    public $name;
    public $surname;
    public $patronymic;
    public $birthday;
    public $mail;
    public $telephone;
    public $text;


    public function rules()
    {
        return [
            [['name',  'surname', 'patronymic', 'birthday', 'mail', 'telephone', 'telephone','text'], 'required'],


            [['name', 'surname', 'patronymic'], 'string', 'max' => 30],
            [['mail', 'telephone'], 'string', 'max' => 30],
            [['text'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'patronymic' => 'Отчество',
            'birthday' => 'Дата рождения',
            'mail' => 'Почта',
            'telephone' => 'Телефон',
            'text' => 'Кратко о себе',

        ];
    }


    public function sendEmail(){

        return \Yii::$app->mailer->compose(['html' => 'newCleaner-html', 'text' => 'newCleaner-text'], ['model' => $this])
            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ' robot'])

            ->setTo(\Yii::$app->params['jobEmail'])
            ->setSubject('Заявка от нового клинера' )
            ->send();

    }
}