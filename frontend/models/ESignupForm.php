<?php
namespace frontend\models;

use common\models\User;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class ESignupForm extends Model
{
    public $username;

    public $password;


    public $name;
    public $family;
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

            [['username'], 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'Такой Логин уже занят.'],

            ['username', 'string', 'min' => 2, 'max' => 255],
            [['name'], 'required'],
            [['name'], 'string', 'min' => 0, 'max' => 30],

        ];
    }

    public function signup()
    {

        if ($this->validate()) {
            $user = new User();
//            $user->scenario="eauth";
            if(Yii::$app->session->get("referalUserId")!=null){
                $user->referal_id = Yii::$app->session->get("referalUserId");
            }
            $user->username = $this->username;
            $user->name = $this->name ;
            $user->family = $this->family;
            $user->email = $this->email;
//            $user->setPassword($this->password);
            $user->generateAuthKey();
            if ($user->save()) {
                return $user;
            }else{
                die (var_dump( $user->errors));
            }
        }

        return null;
    }
}
