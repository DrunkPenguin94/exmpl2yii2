<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\DocumentPages;
/**
 * ContactForm is the model behind the contact form.
 */
class DocumentPagesData extends Model
{


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [

        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'verifyCode' => 'Получение данных по документу',
        ];
    }

    /**
     * Sends an email to the specified email address using the information collected by this model.
     *
     * @param  string  $email the target email address
     * @return boolean whether the email was sent
     */
    public function getData($idi=1)
    {
        $data_document=DocumentPages::find()->where(['id'=>$idi])->one();

//        $lala=new DocumentPagesData();
//        $lala->getData(2);
//        Yii::trace($data_document);

        return $data_document;
    }
}
