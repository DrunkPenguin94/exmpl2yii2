<?php

namespace frontend\controllers;


use common\models\Adresses;
use common\models\IncludeType;
use common\models\Order;
use common\models\OrderAddCleaner;
use common\models\OrderAdditional;
use common\models\OrderClient;
use common\models\Packet;
use common\models\User;
use common\models\UserPhone;
use common\models\UserPhoneForm;
use frontend\models\SaveOrder;
use Faker\Provider\Address;
use Yii;

use yii\helpers\Html;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use common\models\Additional;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\filters\AccessControl;
use common\models\LoginForm;
use yii\widgets\ActiveForm;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class CleanerController extends Controller
{
    public function init()
    {
        $this->on('beforeAction', function ($event) {

            // запоминаем страницу неавторизованного пользователя, чтобы потом отредиректить его обратно с помощью  goBack()
            if (Yii::$app->getUser()->isGuest) {
                $request = Yii::$app->getRequest();
                Yii::trace("----");
                Yii::trace($request->getUrl());
                // исключаем страницу авторизации или ajax-запросы
                if (!($request->getIsAjax() || strpos($request->getUrl(), 'login') !== false)) {
                    Yii::$app->getUser()->setReturnUrl($request->getUrl());
                }
            }
        });
    }
    /**
     * Lists all Order models.
     * @return mixed
     */
    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest){
            $modelUser = \common\models\User::findOne(Yii::$app->user->id);
            $is_cleaner =$modelUser->is_cleaner;
            if (!$is_cleaner ){
                $this->redirect("/orders");
            }
        }

        if (parent::beforeAction($action)) {
            if ($this->enableCsrfValidation && Yii::$app->getErrorHandler()->exception === null && !Yii::$app->getRequest()->validateCsrfToken()) {
                throw new BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
            }
            return true;
        }

        return false;
    }
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),

                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],

        ];
    }

    public function actionIndex()
    {


        $queryOrders= Order::find()
            ->innerJoinWith("packet")
            ->innerJoinWith("packetTime")
        //    ->joinWith("orderAdditionals")
            ->groupBy("order.id");//->joinWith("orderAdditionals");

        $dataProvider = new ActiveDataProvider([
            'query' => $queryOrders,
            'pagination' => [
                "pageSize" => 3,
                'defaultPageSize' => 3,
                'forcePageParam' => false,
            ]
        ]);
       $dataProvider->query->Where("cleaner_id IS NULL  
        OR(
            ".
            //Где есть количество доп клинеров
           "
            packet_time.count_cleaner IS NOT NULL ".
            //
            " AND 
            ".
            //Где количество уже добавленных напарников еще меньше чем нужно в пакете
            "
            packet_time.count_cleaner > (SELECT COUNT(order_add_cleaner.user_id) FROM order_add_cleaner WHERE order_add_cleaner.order_id = order.id)+1
            ".
            // Проверить что уже не выбран заказ клинером и нет в добавленных
            " AND  
            (SELECT COUNT(order_add_cleaner.user_id) FROM order_add_cleaner WHERE order_add_cleaner.user_id= ".Yii::$app->user->id." AND order_add_cleaner.order_id=order.id)=0
                AND
                order.cleaner_id <>".Yii::$app->user->id."
            )
         ");
        $dataProvider->query->andWhere("order.modered=1 AND status=0");
        $dataProvider->query->orderBy("date_cleaning");

        return $this->render('index', [


            "dataProvider"=>$dataProvider
        ]);
    }


    public function actionMy()
    {


        $queryOrders= Order::find()->groupBy("order.id")->orderBy("finish_cleaning,date_cleaning");//->joinWith("packet");->joinWith("orderAdditionals");

        $dataProvider = new ActiveDataProvider([
            'query' => $queryOrders,
            'pagination' => [
                "pageSize" => 3,
                'defaultPageSize' => 3,
                'forcePageParam' => false,
            ]
        ]);
        $dataProvider->query->andWhere("cleaner_id = ".Yii::$app->user->id." and status <> -1 ");

        $dataProvider->query->orWhere("
             order.id in (SELECT order_add_cleaner.order_id FROM order_add_cleaner WHERE order_add_cleaner.user_id= ".Yii::$app->user->id.")
             and status <> -1
        ");
        $dataProvider->query->orderBy("ISNULL(finish_cleaning) DESC,date_cleaning ");
        return $this->render('my', [
            "dataProvider"=>$dataProvider
        ]);
    }

    public function actionAccept($id){
        $model = $this->findModel($id);
        if($model->cleaner_id!=null) {
            //if ($model->cleaner_id == Yii::$app->user->id) $this->redirect("/cleaner");
            if ($model->packet_time_id ==null){
                throw new BadRequestHttpException("Нет временной затраты");
            }
            $addCleaners = $model->orderAddCleaners;


            if($model->packetTimePerArea->count_cleaner> (count($addCleaners)+1)){

                //+1 потому что клинер основной еще есть
                $is_found_user =false;
                foreach ($addCleaners as $modeladdCleaner) {
                    if ($modeladdCleaner->user_id == Yii::$app->user->id) {
                        $is_found_user = true;
                    }
                }

                    if(!$is_found_user){
                        $modeladdCleaner = new OrderAddCleaner();
                        $modeladdCleaner->user_id = Yii::$app->user->id;
                        $modeladdCleaner->order_id = $id;
                        if ($modeladdCleaner->save()) return $this->redirect("/cleaner/my");
                        else throw new BadRequestHttpException("Не удалось сохранить  доп клинера");
                    }

            }
            return $this->redirect("/cleaner");
        }
        $model->cleaner_id=Yii::$app->user->id;
        if($model->validate() and $model->save()){
            return $this->redirect("/cleaner/my");
        }else throw  new BadRequestHttpException("Не удалось сохранить");
    }

    public function actionTimeAccept($id){
        $model = $this->findModel($id);
        if($model->cleaner_id==null) return $this->redirect("/cleaner");
        if($model->cleaner_id!=Yii::$app->user->id) return $this->redirect("/my");

        $flagMessage=isset($model->finish_cleaning);
        if($model->load(Yii::$app->request->post()) ){

             if($model->save()) {
                   if(!$flagMessage && isset($model->finish_cleaning) && $model->finish_cleaning!="") {

                        $modelSaveOrder=new SaveOrder();
                        $modelSaveOrder->sendOrderEvaluation($model);
                   }
                 return $this->redirect("/cleaner/my");
             }else {
                return $this->render("/site/_model_errors",["model"=>$model]);
             }
        }else throw  new BadRequestHttpException("Не удалось сохранить");
    }

    public function actionOrderChange()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post= Yii::$app->request->post();

            if(($post["id"])!=null) {
                $modelOrder = $this->findModel($post["id"]);
            }else{
                $modelOrder = new Order();


                    $modelOrder->load($post);
                $modelOrder->date = date("Y-m-d H:i:s");
                $modelOrder->client_id = Yii::$app->user->id;


                if($post["Order"]["dateCleaningDate"]!=null && $post["Order"]["dateCleaningTime"]!=null) {
                    $modelOrder->dateCleaningDate=$post["Order"]["dateCleaningDate"];
                    $modelOrder->dateCleaningTime=$post["Order"]["dateCleaningTime"];
                    $modelOrder->date_cleaning = date("Y-m-d H:i:00",strtotime( $modelOrder->dateCleaningDate." ".$modelOrder->dateCleaningTime));
                    $modelOrder->dayweek_cleaning = date("N",strtotime( $modelOrder->date_cleaning ));
                }




                $modelOrder->sum_order = 0;
                    if (!$modelOrder->validate()) {

$header='<div class="totalBlock_title">Исправьте следующие ошибки:</div><div class="totalBlock_stringList"><div class="totalBlock_string">';
                        $footer="</div></div>";
                                        return  json_encode([
                                            "total" => "",
                                            "errors"=>Html::errorSummary($modelOrder,[
                                                "header"=>$header
                                            ])
                                        ]);
                                    }

            }




            $arrOrderAdditionals= [];

            $isSaveOrder =  filter_var($post["saveOrder"], FILTER_VALIDATE_BOOLEAN);

            if($isSaveOrder ){
                OrderAdditional::deleteAll(["order_id"=>$modelOrder->id]);
            }
            if(!isset($post["arrAdditinal"])) $post["arrAdditinal"] = array();
            foreach ($post["arrAdditinal"] as $keyAdditional =>$valueAdditonoal){
                if($valueAdditonoal>0){
                $ordAdd = new OrderAdditional();
                $ordAdd->order_id=$modelOrder->id;
                $ordAdd->additional_id =$keyAdditional;
                $ordAdd->count =$valueAdditonoal;
                $arrOrderAdditionals[] = $ordAdd;
                if($isSaveOrder ){
                    $ordAdd->save();
                }
                }
            }


            return  json_encode([
                "total" => $this->renderAjax("item/_result",[
                    "modelOrder"=>$modelOrder,
                    "arrOrderAdditionals"=>$arrOrderAdditionals
                ]),

            ]);
        } else  throw new BadRequestHttpException;
    }



    public function actionMakeOrder($id=null,$step=1, $packet_id=null,$area=null,$sanuzel=null){
        $keySmsOrder = Yii::$app->session->get("keySmsOrder",null);
$isShowKeyOrder = false;

        $post= Yii::$app->request->post();




        $modelAdr = new Adresses();
        $arrMyAdresses = Adresses::find()->where(["user_id"=>Yii::$app->user->id])->all();
        $modelUser = UserPhoneForm::findOne(Yii::$app->user->id);
        if($id==null)
        {
            $model = new Order();
            $model->regular_id=1;
            $model->dateCleaningDate = date("d.m.Y", strtotime(date("d.m.Y")."+1 day"));
            $model->dateCleaningTime = "10:00";
            $model->packet_id = $packet_id;
            $model->area = $area;
            $model->sanuzel = $sanuzel;
        }
        else{


            $model  = $this->findModel($id);
            if($model->date_cleaning!=null ){
                $model->dateCleaningDate = date("d.m.Y",strtotime( $model->date_cleaning));
                $model->dateCleaningTime = date("H:i",strtotime( $model->date_cleaning));

            }
        }

        if ($model->load(Yii::$app->request->post())) {
            $isShowKeyOrder =true;
            if ($step == 1) {

                $model->date = date("Y-m-d H:i:s");
                $model->client_id = Yii::$app->user->id;
                if ($model->dateCleaningDate != null && $model->dateCleaningTime != null) {
                    $model->date_cleaning = date("Y-m-d H:i:00", strtotime($model->dateCleaningDate . " " . $model->dateCleaningTime));
                    $model->dayweek_cleaning = date("N", strtotime($model->date_cleaning));
                }
                if($modelUser -> load(Yii::$app->request->post()) or isset($post["User"]["phone"])){
                    if ($modelUser->save()){

                    }else{
                        throw new BadRequestHttpException("Телефон не сохранен");
                    }
                }else{
                    throw new BadRequestHttpException("Не передан телефон");
                }

                if ($model->adress_id == null) {
                    $modelAdr->load(Yii::$app->request->post());
                    $modelAdr->user_id = Yii::$app->user->id;
                    if ($modelAdr->validate()) {
                        $modelAdress = Adresses::find()->where([
                            "city" => $modelAdr->city,
                            "rayon_id" => $modelAdr->rayon_id,
                            "street" => $modelAdr->street,
                            "home" => $modelAdr->home,
                            "korpus" => $modelAdr->korpus,
                            "kvartira" => $modelAdr->kvartira,
                        ])->limit(1)->one();
                        if ($modelAdress != null) {

                        } else {
                            if ($modelAdr->save()) {
                                $modelAdress = $modelAdr;
                            } else {
                                throw new BadRequestHttpException("Адрес не добавлен");


                            }

                        }
                        $model->adress_id = $modelAdress->id;


                    } else {

                        throw new BadRequestHttpException("Не правильный адрес");
                    }
                }


                   }elseif($step==2){


            }
            $model->sum_order = $model->sumOrder(array());
            $model->money_status_id = null;
            $keyRand= rand(1000,9999);
            if ($keySmsOrder ==null){
                $keySmsOrder =Yii::$app->session->set("keySmsOrder",["date"=>date("Y-m-d H:i:s"),"key"=>$keyRand]);
                if(! $model->sendUnisenderSms("Код подтверждения заказа: ".$keyRand)){
                    $post["Order"]["keySmsOrder"] = $keyRand;
                }
            }
            $diff=(strtotime( date("Y-m-d H:i:s")) - strtotime($keySmsOrder["date"]) )/60;
            $limit_minutes =10;
            if($diff>$limit_minutes ){
                Yii::$app->session->set("keySmsOrder",["date"=>date("Y-m-d H:i:s"),"key"=>$keyRand]);
                $keySmsOrder = Yii::$app->session->get("keySmsOrder");
               if(! $model->sendUnisenderSms("Код подтверждения заказа: ".$keyRand)){
                   $post["Order"]["keySmsOrder"] = $keyRand;
               }

            }


            if ($model->adress_id == null) {
                $model->addError("adress_id");
            }
            elseif(!isset($post["Order"]["keySmsOrder"]) && $model->isNewRecord){
                $model->addError("keySmsOrder","Введите код который был отправлен Вам на телефон");

            }
            elseif($post["Order"]["keySmsOrder"]!=$keySmsOrder["key"] && $model->isNewRecord){
                $model->addError("keySmsOrder","Неверный код ");
            }
            elseif ($model->save()) {
                OrderAdditional::deleteAll(["order_id"=>$model->id]);
                if(!isset($post["arrAdditinal"])) $post["arrAdditinal"] = array();
                foreach ($post["arrAdditinal"] as $keyAdditional =>$valueAdditonoal){
                    if($valueAdditonoal>0){
                    $ordAdd = new OrderAdditional();
                    $ordAdd->order_id=$model->id;
                    $ordAdd->additional_id =$keyAdditional;
                    $ordAdd->count =$valueAdditonoal;
                    $arrOrderAdditionals[] = $ordAdd;

                        $ordAdd->save();
                    }
                }

                $this->redirect(["/orders/make-order", "id" => $model->id, "step" => $step + 1]);
            }
            if ($step == 1) {
                if ($model->date_cleaning == null) {
                    $model->addError("dateCleaningDate");
                    $model->addError("dateCleaningTime");
                }
            }
        }


        return $this->render("make-order",[
           "model" =>$model,
            "modelAdr"=>$modelAdr,
            "step"=>$step,
            "arrMyAdresses"=>$arrMyAdresses,
            "modelUser"=>$modelUser,
            "isShowKeyOrder"=>$isShowKeyOrder
        ]);
    }


    public function actionPacketRegularChange()
    {

        if (Yii::$app->request->isAjax) {
            $post =  Yii::$app->request->post();
            $model = $this->findModel($post["Order"]["id"]);
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                if ($model->dateCleaningDate != null && $model->dateCleaningTime != null) {
                    $model->date_cleaning = date("Y-m-d H:i:00", strtotime($model->dateCleaningDate . " " . $model->dateCleaningTime));
                    $model->dayweek_cleaning = date("N", strtotime($model->date_cleaning));
                }
                if($model->save()){
                return  json_encode([
                    "result" => "Успешно изменено",
                    "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$model])
                ]);
                }else{
                    return  json_encode([
                        "result" => "Не удалось сохранить",
                    ]);
                }
            }else{

                return  json_encode([
                    "result" => "Данные не переданы",

                ]);
            }
        } else  throw new BadRequestHttpException;
    }

//    public function actionTest()
//    {
//        $model = new Order;
//          $model->test();
//    }

    public function actionAdressChange()
    {

        if (Yii::$app->request->isAjax) {
            $post =  Yii::$app->request->post();
            $modelOrder = $this->findModel($post["Order"]["id"]);
            $model = new Adresses();
            $model->load(Yii::$app->request->post());
            $model->user_id= Yii::$app->user->id;
            if ( $model->validate()) {
                $modelAdress = Adresses::find()->where([
                    "city"=>$model->city,
                    "rayon_id"=>$model->rayon_id,
                    "street"=>$model->street,
                    "home"=>$model->home,
                    "korpus"=>$model->korpus,
                    "kvartira"=>$model->kvartira,
                ])->limit(1)->one();
                if($modelAdress!=null){

                }else{
                    if($model->save()){
                        $modelAdress = $model;
                    }else{
                        return  json_encode([
                            "result" => "Адрес не добавлен",


                        ]);
                    };

                }
                $modelOrder->adress_id = $modelAdress->id;
                if($modelOrder->save()){
                    return  json_encode([
                        "result" => "Адрес Успешно изменен",
                        "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$modelOrder])
                    ]);
                }else{
                    return  json_encode([
                        "result" => "Адрес не изменен"
                    ]);
                }
            }else{

                return  json_encode([
                    "result" => "Не правильный адрес"
                ]);
            }
        } else  throw new BadRequestHttpException;
    }

public function actionRefuseOrder($id){
    $model = $this->findModel($id);
    OrderAdditional::deleteAll(["order_id"=>$model->id]);
    OrderAddCleaner::deleteAll(["order_id"=>$model->id]);
    Order::deleteAll(["id"=>$model->id]);
    $this->redirect("/orders");
}

public function actionPacketInclude($id){
    $arrIncludeType = IncludeType::find()->all();
    $model = Packet::findOne($id);

    return $this->renderPartial("/packet/_popup_form_block",[
        "arrIncludeType"=>$arrIncludeType,
        "model"=>$model
    ]);
}

    public function actionPacketCalculate($id){

        $modelPacket = Packet::findOne($id);
        $model= new Order();
        $sumOrder="";
        $packetTimePerArea ="";
        if($model->load(Yii::$app->request->post())){
            $model->packet_id=$modelPacket->id;
            $sumOrder = $model->sumOrder([]);
            $packetTimePerArea = $model->packetTimePerArea->hours_max." ".pluralForm($model->packetTimePerArea->hours_max,"час","часа","часов");
        }
        if ($sumOrder==0) {
            $sumOrder="";
            $packetTimePerArea ="";
        }
        return  json_encode(["sumOrder"=>$sumOrder,"packetTimePerArea"=>$packetTimePerArea]);
    }
    protected function findModel($id)
    {
        if ($model = Order::find()
                ->where (["id"=>$id])->one()
        ) {
            return $model;
        } else {
            throw new NotFoundHttpException('Заказ не найден');
        }
    }


}
