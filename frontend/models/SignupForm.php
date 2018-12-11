<?php
namespace frontend\models;

use common\models\User;
use yii\base\Model;
use Yii;
use yii\filters\AccessControl;
/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $phone;
    public $password_repeat;
    public $password_repeat_repeat;
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
            ['username', 'filter', 'filter' => 'trim'],
            [['username'], 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Такой Логин уже занят.'],
            ['phone', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Такой Телефон уже зарегестрирован.'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            [['name','family'], 'required'],
            [['name','family'], 'string', 'min' => 2, 'max' => 30],
            ["phone",'string'],
            ["phone",'required'],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Такой E-mail уже существует.'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6],
            ['password_repeat', 'required'],
            ['password_repeat', 'compare','compareAttribute'=>"password"],

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
            "password_repeat" => "Подтверждение пароля",
            "name" => "Имя",
            "family" => "Фамилия",
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */

    public function sendEmail()
    {
        /* @var $user User */
        $user = $this;

        if ($user) {
            if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
            }

            if ($user->save()) {
                return \Yii::$app->mailer->compose(['html' => 'SignupToken-html', 'text' => 'passwordResetToken-text'], ['user' => $user])
                    ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ' robot'])
                    ->setTo($this->email)
                    ->setSubject('Смена пароля ' )
                    ->send();
            }
        }

        return false;
    }

    public function signup()
    {
        $this->username = $this->email;
        if ($this->validate()) {
            $user = new User();
            if(Yii::$app->session->get("referalUserId")!=null){
                $user->referal_id = Yii::$app->session->get("referalUserId");
            }
            $user->username = $this->email;
            $user->email = $this->email;
            $user->name = $this->name;
            $user->family = $this->family;
            $user->phone= $this->phone;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->status=0;
            if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
                $user->generatePasswordResetToken();
            }

            if ($user->save()) {
                 Yii::$app->mailer->compose(['html' => 'SignupToken-html', 'text' => 'SignupToken-text'], ['user' => $user])
                    ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
                    ->setTo($this->email)
                    ->setSubject('Теперь вы с нами на '.\Yii::$app->name)
                    ->send();
                return $user;
            }
        }

        return null;
    }
}
