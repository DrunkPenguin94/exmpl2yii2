<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use common\models\Order;
use common\models\ArrivalExpense;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
/**
 * Site controller
 */
class BalanceController extends Controller
{

    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest){
            $modelUser = \common\models\User::findOne(Yii::$app->user->id);
            $is_cleaner =$modelUser->is_cleaner;
            if (!$is_cleaner ){
                $this->redirect("/orders");
            }
        }



        return true;
    }


    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex($page=1)
    {



        $countItemOnPage=10;
        $month=date('m');
        $lastMonth=ArrivalExpense::find()
            ->where(["arrival_id"=>Yii::$app->getUser()->id,"arrival_service"=>"User","expense_service"=>"Order"])
            ->orWhere(["and",
                        [
                    "expense_id"=>Yii::$app->getUser()->id,
                    "expense_service"=>"User",
                    "arrival_service"=>"Order"],
                ["<","sum","0"]])

            ->andWhere("MONTH(date) = ".$month."")
            ->sum('sum');

        $model=ArrivalExpense::find()
            ->where(["arrival_id"=>Yii::$app->getUser()->id,"arrival_service"=>"User","expense_service"=>"Order"])
            ->orWhere(["and",
                [
                    "expense_id"=>Yii::$app->getUser()->id,
                    "expense_service"=>"User",
                    "arrival_service"=>"Order"],
                ["<","sum","0"]])
            ->orWhere(["expense_service"=>"Company","arrival_service"=>"User","arrival_id"=>Yii::$app->getUser()->id])
            ->all();
        $countModel=count($model);

        $allPage=ceil($countModel/$countItemOnPage);
        if($allPage<$page || $page<0)
            $page=1;

        $model=ArrivalExpense::find()
            ->where(["arrival_id"=>Yii::$app->getUser()->id,"arrival_service"=>"User","expense_service"=>"Order"])
            ->orWhere(["and",
                [
                    "expense_id"=>Yii::$app->getUser()->id,
                    "expense_service"=>"User",
                    "arrival_service"=>"Order"],
                ["<","sum","0"]])
            ->orWhere(["expense_service"=>"Company","arrival_service"=>"User","arrival_id"=>Yii::$app->getUser()->id])
            ->offset(($page-1)*$countItemOnPage)
            ->limit($countItemOnPage)
            ->orderBy("date Desc")
            ->all();

        $arrAdress=[];
        foreach($model as $value){
           // $arrAdress[$value->id]=$value->adress->normadress;
            if($value->expense_service=="Order" && $value->arrival_service=="User")
                $arrAdress[]=$value->expense_id;
        }

        $modelOrder=Order::find()
            ->where(["in","id",$arrAdress])
            ->all();

        $arrAdressName=[];
        foreach($modelOrder as $value){
            $arrAdressName[$value->id]=$value->adress->normadress;

        }





        return $this->render('index',[
            'lastMonth'=>$lastMonth,
            'model'=>$model,
            "countModel"=>$countModel,
            "arrAdressName"=>$arrAdressName,
            "page"=>$page,
            "countItemOnPage"=>$countItemOnPage,
            "allPage"=>$allPage
        ]);
    }

}
