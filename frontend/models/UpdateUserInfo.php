<?php
namespace frontend\models;

use common\models\User;
use yii\base\Model;
use Yii;
use yii\filters\AccessControl;
/**
 * Signup form
 */
class UpdateUserInfo extends Model
{
    public $id;
    public $email;
    public $phone;
     public $name;
    public $family;

    /**
     * @inheritdoc
     */

    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],

                ],
            ],

        ];
    }
    public function rules()
    {
        return [
            ['phone', 'unique', 'targetClass' => '\common\models\User','filter'=>function($query){
                $query->andwhere(["<>","id",Yii::$app->getUser()->id]);

            return $query;
            },'message' => 'Такой Телефон уже зарегестрирован.'],
            [['name','family'], 'required'],
            [['name','family'], 'string', 'min' => 2, 'max' => 30],
            ["phone",'string'],
            ["phone",'required'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User','filter'=>function($query){
                $query->andwhere(["<>","id",Yii::$app->getUser()->id]);

                return $query;
            }, 'message' => 'Такой E-mail уже существует.'],


        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'email' => 'Эл. почта',
            "phone"=>"Телефон",

            "name" => "Имя",
            "family" => "Фамилия",
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */



    public function changeInfo()
    {
        if ($this->validate()) {
            $user=User::find()->where(["id"=>$this->id])->one();
            $user->email = $this->email;
            $user->name = $this->name;
            $user->family = $this->family;
            $user->phone= $this->phone;


            if ($user->validate()) {
                $user->save();
                return $user;
            }
        }

        return null;
    }
}
