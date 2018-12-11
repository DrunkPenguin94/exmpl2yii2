<?php
namespace frontend\controllers;

use Yii;

use yii\web\Controller;

use common\models\File;
/**
 * Site controller
 */
class SpecificationController extends Controller
{


    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {

        $specific=File::find()->where("type like 'specification' ")->one();
        if(isset($specific)){
         //   $path=Yii::getAlias('@app').'\files\specification\\'.$specific[name].'.'.$specific[format];
            $path=Yii::getAlias('@app').'/files/specification/'.$specific[name].'.'.$specific[format];
            $path=str_replace("frontend","backend",$path);

            return Yii::$app->response->sendFile($path , $specific[name].".".$specific[format]);
        }

        return $this->redirect('site');
    }

}
