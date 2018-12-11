<?php

namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use frontend\models\UploadForm;
use common\models\OrderComplaint;
use yii\helpers\Html;

class SaveFileController extends \yii\web\Controller
{
//    public function actionIndex()
//    {
//        return $this->render('index');
//    }



    public function actionComplaint()
    {
        try {

            $model = new UploadForm();
            if($_FILES['UploadForm']['name']['imageFile1']!='') {
                $model->imageFile1 = UploadedFile::getInstance($model, 'imageFile1');
            }

            if($_FILES['UploadForm']['name']['imageFile2']!='') {
                $model->imageFile2 = UploadedFile::getInstance($model, 'imageFile2');
            }

            $idImages=$model->upload();

            if($idImages!=false){
                Yii::trace($idImages['first']);
                Yii::trace($idImages['second']);
                $modelOrderComplain= new OrderComplaint();
                $modelOrderComplain->id_file1=Html::encode($idImages['first']);
                $modelOrderComplain->id_file2=$idImages['second'];
                $modelOrderComplain->text=$_POST['problem'];
                $modelOrderComplain->order_id=$_POST['Order']['id'];
                $modelOrderComplain->save();
            }

        }catch (Exception $e){
            Yii::trace($e);
            return Yii::$app->response->redirect(['/orders/finished']);
        }
        return Yii::$app->response->redirect(['/orders/finished']);
    }

}
