<?php
namespace frontend\models;

use common\models\User;
use common\models\VerificationTelephone;
use yii\base\Model;

/**
 * Signup form
 */
class SmsConfirm extends Model
{


   public function sendSms(){


       return true;
   }

    public function checkConfirm($code,$telephone){
        $model=VerificationTelephone::find()
            ->where("code"=>$code)
            ->one();

        if(!isset($model)){
            return true;
        }else{
            return false;
        }
    }

}
