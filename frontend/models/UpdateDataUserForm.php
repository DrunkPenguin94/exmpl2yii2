<?php
namespace frontend\models;

use common\models\User;
use yii\base\Model;
use Yii;
use app\components\PasswordOldValidator;
use app\components\EmailValidator;
use app\components\PhoneValidator;
/**
 * Signup form
 */
class UpdateDataUserForm extends Model
{
    public $username;
    public $email;
    public $phone;
    public $password_old;
    public $password_new;
    public $password_new_repeat;
    public $name;
    public $family;

    /**
     * @inheritdoc
     */
    public function rules()
    {
//        Yii::trace("help");
//        Yii::trace($name);
//        Yii::trace($password_old);
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Такой Логин уже занят.'],
            ['phone', PhoneValidator::className()],
            ['username', 'string', 'min' => 2, 'max' => 255],
            [['name','family'], 'string', 'min' => 2, 'max' => 30],
            ["phone",'string'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', EmailValidator::className()],

            // ['password_old','validatePasswordOld'],
            [['password_old', 'password_new','password_new_repeat'], 'required'],


            ['password_old', 'string', 'min' => 6],
            ['password_new', 'string', 'min' => 6],
            ['password_new_repeat', 'string', 'min' => 6],
            ['password_new_repeat', 'compare','compareAttribute'=>"password_new"],
            ['password_old',PasswordOldValidator::className()],



            //["agree",'boolean'],
            //["agree",'in', 'range' => [1],"message"=>"Ознакомьтесь с политикой конфиденциальности"],

        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Пароль',
            'email' => 'Эл. почта',
            'username' => 'Логин',
            "phone"=>"Телефон",
            "password_old" => "Старый пароль",
            "password_new" => "Новый пароль",
            "password_new_repeat" => "Новый пароль еще раз",
            "name" => "Имя",
            "family" => "Фамилия",
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */





}
