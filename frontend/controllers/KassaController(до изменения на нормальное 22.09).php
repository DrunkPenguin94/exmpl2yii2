<?php
namespace frontend\controllers;

use Yii;



use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;

use common\models\Order;
use common\models\ArrivalExpense;
use common\models\Cashbox;
/**
 * Site controller
 */
class KassaController extends Controller
{
    public function beforeAction($action) {
        $this->layout=false;
       // Yii::trace($action->id);
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionSuccess()
    {
        $get = Yii::$app->request->get();

        $modelOrder = Order::find()->where(["id" => $get["orderNumber"]])->one();

        if (isset($modelOrder) && $modelOrder->client_id == Yii::$app->getUser()->id && $modelOrder->id == $modelOrder->id_subscription) {
            return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['orders/end-create-order', "order_id" => $modelOrder->id]));
        }elseif(isset($modelOrder) && $modelOrder->client_id == Yii::$app->getUser()->id && $modelOrder->id != $modelOrder->id_subscription){
            return $this->redirect("/orders/plan");
        }else{
            return $this->redirect("/site/index");
        }




    }

    public function actionFail()
    {


        return $this->render("/site/index");
    }

    public function actionCheck()
    {
        $post=Yii::$app->request->post();
        $this->createBodyResponse($post,'checkOrderResponse');
    }

    public function actionPayment()
    {
        $post=Yii::$app->request->post();
        $result=$this->createBodyResponse($post,'paymentAvisoResponse');

        if($result==0){
            $modelOrder=Order::find()
                ->where(["id"=>intval($post["orderNumber"])])
                ->one();

            if(!isset($modelOrder)) {
                Yii::trace("Error dont search order ");
            }
            //$modelOrder->balance_noncash+=$post["orderSumAmount"];
            $modelOrder->money_status_id=1;
            if($modelOrder->validate())
                $modelOrder->save();
            else {
                Yii::trace("Error save order #" . $modelOrder->id);
                Yii::trace($modelOrder->getErrors());
            }


            $modelCashbox=new Cashbox;

            $modelCashbox->invoice_id=$post["invoiceId"];
            $modelCashbox->status=0;
            $modelCashbox->order_id=$modelOrder->id;
            $modelCashbox->sum=intval($post["orderSumAmount"]);
            if($modelCashbox->validate())
                $modelCashbox->save();
            else {
                Yii::trace("Error save Cashbox");
                Yii::trace($modelCashbox->getErrors());
            }
        }


    }

    public function createBodyResponse($post,$nameMethod){


        $modelOrder=Order::find()
            ->where(["id"=>intval($post["orderNumber"])])
            ->one();

        //Yii::trace(Yii::$app->params["shopIdYandex"]);
        if(isset($modelOrder)){

            if($modelOrder->money_status_id==1) {
                Yii::trace("Money is blocked");
                return 100;
            }

            if(number_format($modelOrder->sum_order,2,'.','')==$post["orderSumAmount"]){

                if($this->checkMD5($post["md5"],$post,$modelOrder)){
                    $code=0;
                }else{
                    $code=1;
                    $message="Ошибка магазина";
                }
            }else{
                $code=100;
                $message="Ошибка цены по заказу №".$post["orderNumber"];
                //разные цены
            }
        }else{
            $code=100;
            $message="Заказ №".$post["orderNumber"]." в системе не найден";
            //заказ не найден
        }


        $post_response= array(
            'performedDatetime'=> $this->createTimeStamp(),
            'code'=>$code ,
            'shopId'=>Yii::$app->params["shopIdYandex"] ,
            'invoiceId'=>$post["invoiceId"] ,
            'orderSumAmount'=>$post["orderSumAmount"] ,
            'message'=>$message ,
            'techMessage'=>'' ,
        );

        Yii::trace($post_response);


        //Yii::trace($responseBody);
        $this->responseHttpYandex($post_response,$nameMethod);
        return $code;
    }

    public function actionErrorDepositionNotification(){
        return json_encode(["1"]);
    }

    public function responseHttpYandex($post_response,$name){
        header("HTTP/1.0 200");
        header("Content-Type: application/xml");
        $responseBody='<?xml version="1.0" encoding="UTF-8"?><'.$name.' ';

        foreach($post_response as $key=>$value){
            $responseBody.=' '.$key.'="'.$value.'" ';
        }

        $responseBody.='/>';
        echo $responseBody;
    }
    public function checkMD5($hashMD5,$post,$modelOrder){
        $hashMD5home=$post["action"].";".
            number_format($modelOrder->sum_order,2,'.','').";".
            $post["orderSumCurrencyPaycash"].";".
            $post["orderSumBankPaycash"].";".
            Yii::$app->params["shopIdYandex"].";".
            $post["invoiceId"].";".
            $post["customerNumber"].";".
            Yii::$app->params["shopPasswordYandex"];



        Yii::trace($hashMD5);
        Yii::trace($hashMD5home);
        $hashMD5home=md5($hashMD5home);
        $hashMD5home=strtoupper($hashMD5home);

        Yii::trace($hashMD5);
        Yii::trace($hashMD5home);

        if($hashMD5==$hashMD5home) {
            return true;
        }
        else {
            return false;
        }
    }

    public function createTimeStamp(){
        $date_performedDatetime=date("Y-m-d h:i:sP");
        $date_part1=date("Y-m-d",strtotime($date_performedDatetime));
        $date_part2=date("h:i:sP",strtotime($date_performedDatetime));
        return $date_part1."T".$date_part2;

    }

    //------------------------------------------------


    private function checkMD5($request) {
        $str = $request['action'] . ";" .
            $request['orderSumAmount'] . ";" . $request['orderSumCurrencyPaycash'] . ";" .
            $request['orderSumBankPaycash'] . ";" . $request['shopId'] . ";" .
            $request['invoiceId'] . ";" . $request['customerNumber'] . ";" . Yii::$app->params["shopPasswordYandex"] ;
        Yii::trace("String to md5: " . $str);
        $md5 = strtoupper(md5($str));
        if ($md5 != strtoupper($request['md5'])) {
            Yii::trace("Wait for md5:" . $md5 . ", recieved md5: " . $request['md5']);
            return false;
        }
        return true;
    }



    private function buildResponse($functionName, $invoiceId, $result_code, $message = null) {
        try {
            $performedDatetime = Utils::formatDate(new DateTime());
            $response = '<?xml version="1.0" encoding="UTF-8"?><' . $functionName . 'Response performedDatetime="' . $performedDatetime .
                '" code="' . $result_code . '" ' . ($message != null ? 'message="' . $message . '"' : "") . ' invoiceId="' . $invoiceId . '" shopId="' . $this->settings->SHOP_ID . '"/>';
            return $response;
        } catch (\Exception $e) {
            $this->log($e);
        }
        return null;
    }

}
