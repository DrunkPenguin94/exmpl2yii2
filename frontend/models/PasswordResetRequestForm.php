<?php
namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\Model;
use app\components\EmailValidator;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            ['email', 'exist',
//                'targetClass' => '\common\models\User',
//                'message' => 'Пользователя с таким email не существует'
//            ],
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email','message' => 'Неккоректный формат email'],
            ['email', EmailValidator::className()],


        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {

         /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            return false;
        }
        
        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
        }
        
        if (!$user->save()) {
            return false;
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
            ->setTo($this->email)

            ->setSubject('Восстановление пароля')
            ->send();
    }



}
