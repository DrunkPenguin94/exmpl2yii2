<?php
namespace frontend\controllers;

use Yii;

use yii\web\Controller;
use frontend\models\NewCleaner;
/**
 * Site controller
 */
class WorkController extends Controller
{


    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $get=Yii::$app->request->get();

        $modelNewCleaner=new NewCleaner;
        $post=Yii::$app->request->post();
        if($modelNewCleaner->load($post) && $modelNewCleaner->validate()){
            $modelNewCleaner->sendEmail();


            return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['work/index',"flagThx" => "true"]));
        }
        Yii::trace($get["flagThx"]);
        return $this->render('index',[
            "modelNewCleaner"=>$modelNewCleaner,
            'flagThx'=>$get["flagThx"]
        ]);
    }

    public function getName(){
        return $this->name;
    }

}
