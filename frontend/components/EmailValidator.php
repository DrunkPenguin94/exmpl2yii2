<?php


namespace app\components;


use Yii;

use common\models\User;
use yii\base\Model;
use yii\validators\Validator;

class EmailValidator extends Validator {


    public function clientValidateAttribute($model, $attribute, $view)
    {

return <<<JS
var check_pass;
var text;

$.ajax({
        url: "/help-instrument/email?email="+value,
        dataType:"json",
        async:false,
        success: function(data){
            check_pass=data['data'];
            text=data['text'];
    } ,
        complete:function(){
          
    }
    });
if(check_pass){
    messages.push(String(text));
}
JS;
    }
}