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
class UpdateUserPassword extends Model
{
    public $id;
    public $password;
    public $password_new;
    public $password_new_repeat;


    /**
     * @inheritdoc
     */
    public function rules()
    {

        return [
            [['password', 'password_new','password_new_repeat'], 'required'],


            ['password', 'string', 'min' => 6],
            ['password_new', 'string', 'min' => 6],
            ['password_new_repeat', 'string', 'min' => 6],
            ['password_new_repeat', 'compare','compareAttribute'=>"password_new"],
            ['password', function ($attribute, $params) {

                $user = User::find()->where(["id" =>Yii::$app->getUser()->id])->one();

                if (!isset($user) || !Yii::$app->getSecurity()->validatePassword($this->$attribute, $user->password_hash)) {

                    $this->addError($attribute, 'Старый пароль неправильный.');

                }

            }],

        ];
    }

    public function attributeLabels()
    {
        return [
            'password' => 'Пароль',
            "password_new" => "Новый пароль",
            "password_new_repeat" => "Новый пароль еще раз"
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */

     public function changePassword()
     {
         $user=User::find()->where(["id"=>$this->id])->one();

         if(isset($user)){
             $user->setPassword($this->password_new);
             $user->generatePasswordResetToken();
             $user->save();
             Yii::trace(" save model");
             return $user;
         }

         Yii::trace(" not save model");
         return null;
     }



}
