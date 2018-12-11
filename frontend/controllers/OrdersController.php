<?php

namespace frontend\controllers;


use common\models\Adresses;
use common\models\ArrivalExpense;
use common\models\Coupon;
use common\models\Coupons;
use common\models\IncludeType;
use common\models\Order;
use common\models\OrderAddCleaner;
use common\models\OrderAdditional;
use common\models\OrderClient;
use common\models\Packet;
use common\models\User;
use common\models\UserPhone;
use common\models\UserPhoneForm;
use common\models\VerificationTelephone;
use common\models\OrderRating;
use frontend\models\SaveOrder;
use common\models\Journal;
use common\models\FineUser;
use frontend\models\UpdateSubscription;
use common\models\Shopping;
use common\models\ReminderMobile;
use common\models\Cashbox;
use common\models\CashBackCliner;
use common\models\CouponsUsed;
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
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * OrderController implements the CRUD actions for Order model.
 */
class OrdersController extends Controller
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
            if ($is_cleaner ){
                return $this->redirect("/cleaner");
            }



            if(empty($modelUser->name) || empty($modelUser->email) || empty($modelUser->family) || empty($modelUser->phone)){
                return $this->redirect("/cabinet");
            }
        }else{
//            $cookies = Yii::$app->request->cookies;
//            $cookies->add(new \yii\web\Cookie([
//                'name' => 'redirect_after_login_controller',
//                'value' =>  Yii::$app->controller->id,
//            ]));
//
//            $cookies->add(new \yii\web\Cookie([
//                'name' => 'redirect_after_login_action',
//                'value' =>  Yii::$app->controller->action->id,
//            ]));

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
     //   return $this->redirect(["/cleaner"]);
        return [
            'access' => [
                'class' => AccessControl::className(),

                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],

                    ], [
                        'actions' => ['packet-calculate','packet-include'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],

        ];
    }

    public function actionIndex()
    {
        if(Order::find()->where(["client_id"=>Yii::$app->user->id])->all()==null)
            return $this->render('noOrders', []);

        $arrAdditional = Additional::find()->all();

        $queryOrders= Order::find()
            ->andWhere(["client_id"=>Yii::$app->user->id])
            ->andWhere("start_cleaning IS  NULL OR finish_cleaning IS NULL")
            ->andWhere(["status"=>"0"])
            ->orderBy("date_cleaning");

        $dataProvider = new ActiveDataProvider([
            'query' => $queryOrders,
            'pagination' => [
                "pageSize" => 10,
                'defaultPageSize' => 10,
                'forcePageParam' => false,
            ]
        ]);




        return $this->render('index', [
            //'dataProvider' => $dataProvider,
            "arrAdditional"=> $arrAdditional,
            "dataProvider"=>$dataProvider
        ]);
    }


    public function actionFinished()
    {
        $arrAdditional = Additional::find()->all();

        $queryOrders= Order::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $queryOrders,
            'pagination' => [
                "pageSize" => 10,
                'defaultPageSize' => 10,
                'forcePageParam' => false,
            ]
        ]);
        $dataProvider->query->andWhere(["client_id"=>Yii::$app->user->id]);
        $dataProvider->query->andWhere("(start_cleaning IS NOT NULL AND finish_cleaning IS NOT NULL) or (status=1)");
        $dataProvider->query->orderBy("date_cleaning DESC");
        return $this->render('index', [
            //'dataProvider' => $dataProvider,
            "arrAdditional"=> $arrAdditional,
            "dataProvider"=>$dataProvider
        ]);
    }


    public function actionPlan()
    {
        $arrAdditional = Additional::find()->all();

        $queryOrders= Order::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $queryOrders,
            'pagination' => [
                "pageSize" => 10,
                'defaultPageSize' => 10,
                'forcePageParam' => false,
            ]
        ]);
        $dataProvider->query->andWhere(["client_id"=>Yii::$app->user->id]);
        $dataProvider->query->andWhere("start_cleaning IS  NULL AND finish_cleaning IS  NULL");
        $dataProvider->query->andWhere(["status"=>"10"]);

        return $this->render('index', [
            //'dataProvider' => $dataProvider,
            "arrAdditional"=> $arrAdditional,
            "dataProvider"=>$dataProvider
        ]);
    }


    public function actionBonuses()
    {
        $modelUser =User::findone(Yii::$app->user->id);

        return $this->render('bonuses', [
            "modelUser"  => $modelUser ,

        ]);
    }


    public function actionOrderChange()
    {
//        $numbetPhone;
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post= Yii::$app->request->post();

            if(($post["id"])!=null) {
                $modelOrder = $this->findModel($post["id"]);
                if (isset($post["orderCoupon"])) {


                    $modelCoupon=new Coupons;
                    $modelCoupon=$modelCoupon->getCheckActiveCoupons($post["orderCoupon"]);
                    if(isset($modelCoupon["coupon"]) && $modelCoupon["use"]==1)
                        $modelOrder->coupon =$modelCoupon["coupon"]->id;

//                    $modelCoupon=Coupons::find()->where(["code"=>$post["orderCoupon"]])->one();
//                    if(isset($modelCoupon))
//                        $modelCouponUsed = CouponsUsed::find()->where(["user_id" => Yii::$app->getUser()->id, "coupon_id" => $modelCoupon->id])->one();
//                            if(!isset($modelCouponUsed))
//                                $modelOrder->coupon =$modelCoupon->id;
                }
            }else{
                $modelOrder = new Order();

                $modelOrder->load($post);
                $modelOrder->coupon=null;
                if (isset($post["Order"]["coupon"])) {
                    $modelCoupon=new Coupons;
                    $modelCoupon=$modelCoupon->getCheckActiveCoupons($post["Order"]["coupon"]);
                    if(isset($modelCoupon["coupon"]) && $modelCoupon["use"]==1)
                        $modelOrder->coupon =$modelCoupon["coupon"]->id;


                }



                $modelOrder->date = date("Y-m-d  H:i:s");
                $modelOrder->client_id = Yii::$app->user->id;


                if($post["Order"]["dateCleaningDate"]!=null && $post["Order"]["dateCleaningTime"]!=null) {
                    $modelOrder->dateCleaningDate=$post["Order"]["dateCleaningDate"];
                    $modelOrder->dateCleaningTime=$post["Order"]["dateCleaningTime"];
                    $modelOrder->date_cleaning = date("Y-m-d H:i",strtotime( $modelOrder->dateCleaningDate." ".$modelOrder->dateCleaningTime));
                    $modelOrder->dayweek_cleaning = date("N",strtotime( $modelOrder->date_cleaning ));
                }

                if($post["Adresses"]["rayon_id"]!=null) {
                    $modelOrder->inTheCountry=$post["Adresses"]["rayon_id"];
                }


                $modelOrder->sum_order = 0;


                    $arrOrderAdditionals= [];
                    foreach ($post["arrAdditinal"] as $keyAdditional =>$valueAdditonoal){
                        if($valueAdditonoal>0){
                            $ordAdd = new OrderAdditional();
                            $ordAdd->order_id=$modelOrder->id;
                            $ordAdd->additional_id =$keyAdditional;
                            $ordAdd->count =$valueAdditonoal;
                            $arrOrderAdditionals[] = $ordAdd;

                        }
                    }
                $modelOrder->allTimeClear=$modelOrder->timeAllClean($arrOrderAdditionals);
               // Yii::trace($modelOrder->allTimeClear);
                    if (!$modelOrder->validate()) {

//                        Yii::trace($modelOrder->hasErrors("area"));
//                        Yii::trace($modelOrder->hasErrors("packet_id"));
//                        Yii::trace($modelOrder->area>=200);
//                        Yii::trace($modelOrder->area=='');
                        if( $modelOrder->hasErrors("packet_id") &&
                            $modelOrder->area>=200
                        ){
                            $header='<div class="totalBlock_title">ВНИМАНИЕ!</div>'.
                                '<div class="hidden_error" type="error"></div>'.
                                '<div class="totalBlock_stringList"><div class="totalBlock_string">';
                        }else{
                            $header='<div class="totalBlock_title">Исправьте следующие ошибки:</div>'.
                                '<div class="hidden_error" type="error"></div>'.
                                '<div class="totalBlock_stringList"><div class="totalBlock_string">';
                        }

                        Yii::trace($modelOrder->getErrors() );
                        $footer="</div></div>";
                                            return  json_encode([
                                                "total" => "",
                                                "errors"=>Html::errorSummary($modelOrder,[
                                                    "header"=>$header,
                                                    "footer"=>$footer
                                                ])
                                            ]);
                    }


            }


//            $numbetPhone=$post["UserPhoneForm"]["phone"];
//            str_replace("+","",$numbetPhone);
//            $modelVerTel=VerificationTelephone::find()
//                ->where(
//                    ["and",
//                        ["like","telephone",$numbetPhone],
//                        ["like","confirm","1"],
//                    ]
//                )
//                ->one();



            $arrOrderAdditionals= [];

            $isSaveOrder =  filter_var($post["saveOrder"], FILTER_VALIDATE_BOOLEAN);

            if($isSaveOrder ){
                OrderAdditional::deleteAll(["order_id"=>$modelOrder->id]);
                if ($modelOrder->coupon!=null){
                    $modelCoupon = \common\models\Coupon::find()
                        ->where("name=:coupon_name",["coupon_name"=>$modelOrder->coupon])
                        ->andWhere("order_id IS NULL")
                        ->one();


                    if($modelCoupon !=null){
                        if($modelCoupon->first_clean){
                            $modelCoupon=null;
                        }
                        else {
                            Coupon::updateAll(["order_id"=>null],["order_id"=>$modelOrder->id]);
                        $modelCoupon->order_id = $modelOrder->id;
                            if ($modelCoupon->save()) $modelOrder->coupon=null;

                        }
                    }else{

                    }
                }
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
            if($isSaveOrder ) {
                $modelOrder->sum_order = $modelOrder->sumOrder($modelOrder->orderAdditionals);
                $modelOrder->save();
            }

            return  json_encode([
                "total" => $this->renderAjax("item/_result",[
                    "modelOrder"=>$modelOrder,
                    "arrOrderAdditionals"=>$arrOrderAdditionals,
                    "modelVerTel"=>$modelVerTel
                ]),

            ]);
        } else  throw new BadRequestHttpException;
    }


    public function actionCheckCoupon()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $post= Yii::$app->request->post();
            if(isset($post["coupon"])){
//                $modelCoupon = Coupons::find()
//                    ->where("code=:coupon_name",["coupon_name"=>$post["coupon"]])
//                    ->andWhere("(count is NULL or count > used) and now() > start_date and now() < finish_date")
//
//                    ->one();

                $modelCoupon =new Coupons;
                $modelCoupon=$modelCoupon->getCheckActiveCoupons($post["coupon"]);

                Yii::trace($modelCoupon["use"]);
                Yii::trace($modelCoupon["reason"]);
                if(isset($modelCoupon["coupon"])){
                    if($modelCoupon["use"]==1){
                        $result["result" ]=true;
                    }else{
                        $result["result" ]=false;
                        $result["reason" ]=$modelCoupon["reason"];
                    }
                }else{
                    $result["result" ]=false;
                    $result["reason" ]=$modelCoupon["reason"];
                }




//
//                if(isset($modelCoupon)){
//                    $modelCouponUsed=CouponsUsed::find()->where(["user_id"=>Yii::$app->getUser()->id,"coupon_id"=>$modelCoupon->id])->one();
//
//                    if(isset($modelCouponUsed))
//                        $result["result" ]=false;
//                    else
//                        $result["result" ]=true;
//                }else{
//                    $result["result" ]=false;
//                }
//                $cntOrder = Order::find()->where(["client_id"=>Yii::$app->user->id])->count();
//                if($modelCoupon !=null){
//                    if($modelCoupon->first_clean  && $cntOrder ==0)$result["result" ]="first";
//                    else $result["result" ]=true;
//                }else{
//                    $result["result" ]=false;
//                }
                return  json_encode($result);
            }
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
//            $model->dateCleaningDate = date("d.m.Y", strtotime(date("d.m.Y")."+1 day"));

          //  $model->dateCleaningDate = date("d.m.Y", time()+60*60*27);
//            Yii::trace(date("d.m.Y G i",time()+60*60*27));
    //        Yii::trace(date("d.m.Y G i"));
//            Yii::trace(time());
            $model->dateCleaningTime = "";
            $model->packet_id = $packet_id;
            $model->area = $area;
            $model->sanuzel = $sanuzel;
        }
//        else{
//
//
//            $model  = $this->findModel($id);
//            if($model->date_cleaning!=null ){
//                $model->dateCleaningDate = date("d.m.Y",strtotime( $model->date_cleaning));
//                $model->dateCleaningTime = date("H:i",strtotime( $model->date_cleaning));
//
//            }
//        }

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

            if (!isset($post["Order"]["keySmsOrder"]))$post["Order"]["keySmsOrder"] =null;


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


                if ($model->coupon!=null){
                    $modelCoupon = \common\models\Coupon::find()
                        ->where("name=:coupon_name",["coupon_name"=>$model->coupon])
                        ->andWhere("order_id IS NULL")
                        ->one();


                    if($modelCoupon !=null){
                        if($modelCoupon->first_clean){
                            $modelCoupon=null;
                        }
                        else {
                            Coupon::updateAll(["order_id"=>null],["order_id"=>$model->id]);
                            $modelCoupon->order_id = $model->id;
                            if ($modelCoupon->save()) $model->coupon=null;

                        }
                    }else{

                    }
                }


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
            //Yii::trace($post);
            $model = $this->findModel($post["Order"]["id"]);

            if($model->modered==1) return  json_encode([
                "result" => "Заказ нельзя редактироваться, обратитесь к диспетчеру",
            ]);
            if(isset($post["Order"]["regular_id"])) {
                if ($model->regular_id == 1) {
                    if ($post["Order"]["regular_id"] != 1)
                        return json_encode([
                            "result" => "У одиночного заказа нельзя менять регулярность",
                        ]);
                } else {
                    if ($post["Order"]["regular_id"] == 1)
                        return json_encode([
                            "result" => "Подписку нельзя сделать одиночным заказом",
                        ]);
                }
            }



            $modelSubsrip = new UpdateSubscription;

            $oldRegular=$model->regular_id;
            $oldPacket=$model->packet_id;
//            Yii::trace($oldPacket);
//            Yii::trace($model->packet_id);

            if ($model->load(Yii::$app->request->post())) {
                if ($model->dateCleaningDate != null && $model->dateCleaningTime != null) {
                    $model->date_cleaning = date("Y-m-d H:i:00", strtotime($model->dateCleaningDate . " " . $model->dateCleaningTime));
                    $model->dayweek_cleaning = date("N", strtotime($model->date_cleaning));

                    $modelReminderMobile=ReminderMobile::find()->where(["order_id"=>$model->id])->one();
                    $modelReminderMobile->initial_field($model);
                    if($modelReminderMobile->validate())
                        $modelReminderMobile->save();
                    else
                        Yii::trace("error model reminder mobile");
                }
//                Yii::trace($oldPacket);
//                Yii::trace($model->packet_id);
//                if(isset($post["Order"]["area"]))
//                    if($model->regular_id==1) {
//                        $modelSubsrip = true;
//                    }else {
//                        $modelSubsrip = $modelSubsrip->updateVolume($model);
//                    }

//                if(isset($post["Order"]["packet_id"]) || isset($post["Order"]["regular_id"])) {
//                    Yii::trace("5----");
//                    if($model->packet_id != $oldPacket) {
//                        Yii::trace("4----");
//                        //изменяется пакет
//                        if ($model->regular_id == 1)
//                            $modelSubsrip = true;
//                        else
//                            $modelSubsrip = $modelSubsrip->updatePacket($model);
//                    }
//                    }else{
//                        //изменяется регулярность
//                        if($oldRegular==1){
//                            Yii::trace("3----");
//                            return  json_encode([
//                                "result" => "Нельзя изменить регулярность в одноразовом заказе "
//                            ]);
//                        }else{
//                            if($model->regular_id==1){
//                                Yii::trace("2----");
//                                return  json_encode([
//                                    "result" => "Нельзя из подписки сделать одноразовый заказ "
//                                ]);
//                            }else{
//                                $modelSubsrip = $modelSubsrip->updateRegular($model);
//                                Yii::trace($modelSubsrip);
//                                Yii::trace("1----");
//                            }
//                        }
//                    }

  //              }



//                    if ($model->regular_id == 1) {
//                        if($oldRegular==1)
//                            $modelSubsrip = true;
//                        else
//                            $modelSubsrip = false;
//                    } else {
//                        $modelSubsrip = $modelSubsrip->updateDetail($model);
//                    }
                if(isset($post["Order"]["regular_id"]) && $model->regular_id != $oldRegular) {
                    if($oldRegular==1) {
                        return json_encode([
                            "result" => "Нельзя изменить регулярность в одноразовом заказе"
                        ]);
                    }

                    if($model->regular_id==1) {
                        return json_encode([
                            "result" => "Нельзя из подписки сделать одноразовый заказ "
                        ]);
                    }

                    $modelSubsrip = $modelSubsrip->updateRegular($model);
                }


                if(isset($post["Order"]["packet_id"]) && $model->packet_id != $oldPacket) {

                    $modelAddional=$model->orderAdditionals;
                    foreach ($modelAddional as $valueAdditonoal) {
                        if ($valueAdditonoal > 0) {
                            $ordAdd = new OrderAdditional();
                            $ordAdd->order_id = $model->id;
                            $ordAdd->additional_id = $valueAdditonoal->additional_id;
                            $ordAdd->count = $valueAdditonoal->count;
                            $arrOrderAdditionals[] = $ordAdd;
                        }

                    }

                   // Yii::trace($model->timeAllClean($arrOrderAdditionals));

                    $timeClear=$model->timeAllClean($arrOrderAdditionals);
                    if($timeClear > 13){
                        return  json_encode([
                            "result" => "Невозможно изменить заказ на пакет \"".$model->packet->name."\".<br>".
                                        "Уборка c данным пакетом будет длиться ".$timeClear." часов,<br> что превышает рабочий день (13 часов)",
                            "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$this->findModel($post["Order"]["id"])])
                        ]);
                    }else{
                        if($oldRegular==1) {
                            $modelSubsrip = true;
                        }else {
                            $modelSubsrip = $modelSubsrip->updatePacket($model);
                        }
                    }

                }


                if($model->validate() && $modelSubsrip){

                    $model->save();
                    if($model->regular_id!=1)
                        foreach($modelSubsrip as $value){
                            $value->save();
                        }
                    return  json_encode([
                        "result" => "Заказ успешно изменен",
                        "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$model])
                    ]);
                }else{
                    Yii::trace($model->getErrors());
                    Yii::trace($modelSubsrip);
                    return  json_encode([
                        "result" => "Не удалось сохранить изменения",
                    ]);
                }
            }else{
                Yii::trace($model->getErrors());
                return  json_encode([
                    "result" => "Данные не переданы",

                ]);
            }
        } else  throw new BadRequestHttpException;
    }


//
//    public function actionAdressChange()
//    {
//
//        if (Yii::$app->request->isAjax) {
//            $post =  Yii::$app->request->post();
//            $modelOrder = $this->findModel($post["Order"]["id"]);
//            $model = new Adresses();
//            $model->load(Yii::$app->request->post());
//            $model->user_id= Yii::$app->user->id;
//            if ( $model->validate()) {
//                $modelAdress = Adresses::find()->where([
//                    "city"=>$model->city,
//                    "rayon_id"=>$model->rayon_id,
//                    "street"=>$model->street,
//                    "home"=>$model->home,
//                    "korpus"=>$model->korpus,
//                    "kvartira"=>$model->kvartira,
//                ])->limit(1)->one();
//                if($modelAdress!=null){
//
//                }else{
//                    if($model->save()){
//                        $modelAdress = $model;
//                    }else{
//                        return  json_encode([
//                            "result" => "Адрес не добавлен",
//
//
//                        ]);
//                    };
//
//                }
//                $modelOrder->adress_id = $modelAdress->id;
//                if($modelOrder->save()){
//                    return  json_encode([
//                        "result" => "Адрес Успешно изменен",
//                        "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$modelOrder])
//                    ]);
//                }else{
//                    return  json_encode([
//                        "result" => "Адрес не изменен"
//                    ]);
//                }
//            }else{
//
//                return  json_encode([
//                    "result" => "Не правильный адрес"
//                ]);
//            }
//        } else  throw new BadRequestHttpException;
//    }

public function actionRefuseOrder($id=null,$subcrb=null){
    //поправить
    if(isset($id) && !isset($subcrb)) {
        $model = Order::find()->where(["id" => $id, "client_id" => Yii::$app->user->id, "status" => 0])->one();
        if(isset($model) && $model->checkTwentyFourHour) {
            if ($model->regular_id == 1) {
                if ($model->start_cleaning == null && $model->finish_cleaning == null) {
                    $model->status = -1;
                    $model->save();
                    $model->unbindFineUser($model->id);

                    //проверка чтоб прицепить отцепленный штраф
                    $model->attachFineNextOrder();
                }
                $this->redirect("/orders");
            } else {
                if ($model->id == $model->id_subscription &&
                    $model->start_cleaning == null &&
                    $model->finish_cleaning == null &&
                    $model->status == 0  //если отменяем 1 заказ из подписки
                ) {
                    Order::updateAll(['status' => -1], ["and",
                        ['id_subscription' => $model->id_subscription],
                        ['id' => $model->id]
                    ]);

                    $allModels = Order::find()
                        ->where('id_subscription = ' . $model->id_subscription . ' and status = 10 ')
                        ->orderBy("date_cleaning")
                        ->all();

                    //создаем копию последнего заказа подписки
                    $newModel = new SaveOrder;

                    //отвязываем штрафы
                    $model->unbindFineUser($model->id);

                    //делаем первый планируемый заказ актуальным
                    $allModels[0]->status = 0;
                    $allModels[0]->save();


                    $newModel->CreateCopyOrder($allModels[count($allModels) - 1]);

                    //проверка чтоб прицепить отцепленный штраф
                    $model->attachFineNextOrder();

                    $this->redirect("/orders");
                } else {
                    $this->redirect("/orders");
                }
            }
        }
    }

    if(!isset($id) && isset($subcrb)) {
        $model = Order::find()->where("id_subscription=".$subcrb." and client_id=".Yii::$app->user->id." and (status=10 or (status=0 and start_cleaning is null ))")->all();

        foreach($model as $modelValue){
            $modelValue->status=-1;
            $modelValue->save();

            //отвязываем штрафы
            $modelValue->unbindFineUser($modelValue->id);
        }
        foreach($model as $modelValue) {
            //проверка чтоб прицепить отцепленный штраф
            $modelValue->attachFineNextOrder();
        }
        $this->redirect("/orders");

    }else{
        $this->redirect("/orders");
    }
  //  Yii::trace($model);
   // Yii::trace();
//    OrderAdditional::deleteAll(["order_id"=>$model->id]);
//    OrderAddCleaner::deleteAll(["order_id"=>$model->id]);
//    Order::deleteAll(["id"=>$model->id]);


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
           // var_dump($model->packet_id);

            $sumOrder = $model->sumOrder([],true);
            $packetTimePerArea = $model->packetTimePerArea->hours_max." ".pluralForm($model->packetTimePerArea->hours_max,"час","часа","часов");
        }
        if ($sumOrder<=0) {
            $sumOrder="";
            $packetTimePerArea ="";
        }else $sumOrder.= '<span class="ruble"> е</span>';
        return  json_encode(["sumOrder"=>$sumOrder.'',"packetTimePerArea"=>$packetTimePerArea]);
    }


    protected function findModel($id)
    {
        if (($model = Order::find()
                ->where (["id"=>$id])
            ->andWhere(["client_id"=>Yii::$app->user->id])->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Заказ не найден');
        }
    }

    public function actionSaveOrder(){
        $post =  Yii::$app->request->post();

        $model=new SaveOrder();

       // return json_encode(false);

        $result=$model->SaveOneOrder($post,0,0);
        //Yii::trace($result);
//        $model2 = new SaveOrder();
//        for($i=0;$i<25;$i++)
//
//            $model2->SaveOneOrder($post, 7, $result);
        if($result!=false) {
            $model2 = new SaveOrder();
            //$step=0;
            switch ($post["Order"]["regular_id"]) {

                case 2:
                    //$step=7;
                    //$model2->SaveOneOrder($post, 7, $result);
                    $model2->SaveOneOrder($post, 7, $result);
                    $model2->SaveOneOrder($post, 14, $result);
                    $model2->SaveOneOrder($post, 21, $result);
                    break;
                case 3:
                   // $step=14;
                   // $model2->SaveOneOrder($post, 14, $result);
                    $model2->SaveOneOrder($post, 14, $result);
                    $model2->SaveOneOrder($post, 28, $result);
                    $model2->SaveOneOrder($post, 42, $result);
                    break;
                case 4:
                    //просчет при месячной подписке 28 дней вмесяце шаг 28 дней
                    //если в месяце 29-31 день, шаг 35 дней
                    $step=$model2->addDateIntervalFourRegular($post["Order"]["dateCleaningDate"],2);
                    $model2->SaveOneOrder($post, $step, $result);

                    $step=$model2->addDateIntervalFourRegular($post["Order"]["dateCleaningDate"],3);
                    $model2->SaveOneOrder($post, $step, $result);

                    $step=$model2->addDateIntervalFourRegular($post["Order"]["dateCleaningDate"],4);
                    $model2->SaveOneOrder($post, $step, $result);

                    break;
            }
//            if($post["Order"]["regular_id"]!=1)
//                for($plan=1;$plan<=3;$plan++){
              //      $model2->SaveOneOrder($post, $plan*$step, $result);
//                }
            $modelOrder=Order::findOne($result);
            if(isset($modelOrder)) {
                try {
                    Yii::trace($model->sendInfoEmailDetailOrder($modelOrder));
                //    Yii::trace($model->sendInfoSmsDetailOrder($modelOrder));
                }catch (Exception $e) {
                    Yii::trace('Exseption ',  $e->getMessage(), "\n");
                }
            }
        }






        return json_encode($result);
       // return json_encode(true);

    }


    public function actionSendSms($telephone){

      //  $keyRand= rand(1000,9999);
        $keyRand= 1111;
        $modelVarTel=new VerificationTelephone();

        $telephone=str_replace("+","",$telephone);
        $telephone=str_replace(" ","",$telephone);
        $telephone=str_replace("(","",$telephone);
        $telephone=str_replace(")","",$telephone);
        $telephone=str_replace("-","",$telephone);

        $modelVarTel->telephone=$telephone;
        $modelVarTel->code=strval($keyRand);
        $model=new Order();



        //отправка смсок

       // if($modelVarTel->validate() && $model->sendUnisenderSms($keyRand,$telephone)){
        if($modelVarTel->validate() ){
       // if($modelVarTel->validate() ){
            $modelVarTel->save();
             return json_encode(true);
        } else{
            return json_encode(false);
        }

    }

    public function actionCheckTelephone($code,$telephone){

        $telephone=str_replace("+","",$telephone);
        $telephone=str_replace(" ","",$telephone);
        $telephone=str_replace("(","",$telephone);
        $telephone=str_replace(")","",$telephone);
        $telephone=str_replace("-","",$telephone);

        if($code!=null || $code!="") {
            $modelVarTel = VerificationTelephone::find()
                ->where(
                    ["and",
                        ["<", "(now()-date_start)", "300"],
                        [
                            "and",
                            ["and",
                                ["like", "code", $code,false],
                                ["like", "telephone", $telephone]
                            ],
                            ["and",
                                ["order_id" => null],
                                ["confirm" => "0"]
                            ]
                        ]
                    ]
                )
//                ->where("(now()-date_start)<300 )
                ->one();

            if($modelVarTel!=null){
                $modelVarTel->confirm=1;
                $modelVarTel->save();
                Yii::trace($modelVarTel->id);
                return  json_encode($modelVarTel->id);
            }

        }
        return  json_encode(false);

    }

    public function actionEndCreateOrder($order_id){

        $modelOrder=Order::find()->where(["id"=>$order_id])->one();
        if(Yii::$app->getUser()->id!=$modelOrder->client_id || !isset($modelOrder)) return $this->redirect("index");

     //   if(isset($modelOrder) && $modelOrder->IsCashbox){
          //  return $this->redirect("index");
//            $nowTime=time()+60*60*Yii::$app->params["timeDifference"];
//            if(strval($nowTime)-strval(strtotime($modelOrder->date))>3600 || $modelOrder->client_id!=Yii::$app->getUser()->id){
//                Yii::trace(strval($nowTime)-strval(strtotime($modelOrder->date)));
//                return $this->redirect("index");
//            }
     //   }

        $dataProvider= Order::find()->joinWith("packet")->joinWith("orderAdditionals")
            ->andWhere(["order.id"=>$order_id])->andWhere(["client_id"=>Yii::$app->getUser()->id])->one();

        if($dataProvider==null)
            return $this->redirect("/");

        if($modelOrder->pay_form_id==1 && !$modelOrder->IsCashbox){
            return $this->render('goYandex', ["modelOrder"=>$dataProvider]);
        }

        return $this->render("endcreateorder", [

            "modelOrder"=>$dataProvider
        ]);
    }


    public function actionSetRating(){




        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();

            $modelOrderRating=OrderRating::find()->where(["id_order"=>$post["Order"]["id"]])->one();
            //Yii::trace($modelOrderRating);
            if($modelOrderRating==null) {

                $modelOrderRating = new OrderRating();
                $modelOrderRating->id_order = $post["Order"]["id"];
                $modelOrderRating->rating = $post["rating"];
                if ($modelOrderRating->save()) {
                    return json_encode([
                        "result" => "Cпасибо за оценку"
                    ]);
                } else {
                    return json_encode([
                        "result" => "Оценка не сохранена",
                    ]);
                }
            }else{
                return json_encode([
                    "result" => "Вы уже оценивали эту уборку",
                ]);
            }




        } else  throw new BadRequestHttpException;
    }


    public function actionCheckRating(){

        $post=Yii::$app->request->post();

        $sql = Yii::$app->db->createCommand(''.
       'SELECT  \'2\' modd FROM order_complaint where order_id='.$post['id'].' '.
        'UNION '.
        'select  \'1\' modd from order_rating where id_order='.$post['id'].' ')->queryAll();

        //Yii::trace($sql);
        $array='';
        foreach ($sql as $value) {
            $array[]=$value['modd'];

        }
        if($array=='') return json_encode(false);
        else return json_encode($array);
    }




    public function actionUpdateInfo()
    {

        $post =  Yii::$app->request->post();
        $modelOrder = $this->findModel($post["id"]);

        if($modelOrder->modered==1) return  json_encode([
            "result" => "Заказ нельзя редактироваться, обратитесь к диспетчеру",
        ]);

        if($post["mod"]==1) {
            $numberValidate=0;
            $numberAllAdd=0;


            if(!empty($post["coupon"])){
                $modelCoupon = Coupons::find()
                    ->where("code=:coupon_name",["coupon_name"=>$post["coupon"]])
                    ->andWhere("(count is NULL or count > used) and now() > start_date and now() < finish_date")

                    ->one();
                if(isset($modelCoupon)) {
                    $modelCouponUsed = CouponsUsed::find()->where(["user_id" => Yii::$app->getUser()->id, "coupon_id" => $modelCoupon->id])->one();
                    if(!isset($modelCouponUsed)){
                        $modelCoupon->bindCoupon($modelOrder);
                        $flagDeleteBlockCoupon=true;
                    }
                }
            }

            if(isset($post["arrAdditinal"]))
                foreach ($post["arrAdditinal"] as $keyAdditional => $valueAdditonoal) {
                    if ($valueAdditonoal > 0) {
                        $ordAdd = new OrderAdditional();
                        $ordAdd->order_id = $post["id"];
                        $ordAdd->additional_id = $keyAdditional;
                        $ordAdd->count = $valueAdditonoal;
                        $arrOrderAdditionals[] = $ordAdd;
                        $numberAllAdd++;
                        if($ordAdd->validate()) $numberValidate++;
                    }

                }

            if($numberValidate==$numberAllAdd && $modelOrder->timeAllClean($arrOrderAdditionals)<=13) {

                if($modelOrder->checkIntervalTime($modelOrder->date_cleaning,$modelOrder->timeAllClean($arrOrderAdditionals))) {
                    OrderAdditional::deleteAll(["order_id" => $post["id"]]);
                    if(isset($arrOrderAdditionals))
                        foreach ($arrOrderAdditionals as $keyAdditional => $valueAdditonoal) {
                            $valueAdditonoal->save();
                        }
                    $modelOrder->save();
                }else{
                    return  json_encode([
                        "result" => "Внимание!<br> С дополнительными работами уборка закончится позже 21:00. Пожалуйста, назначьте уборку пораньше, на другой день или запланируйте дополнительные работы на следующий раз.",
                        "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$modelOrder])
                    ]);
                }
            }else{
                return  json_encode([
                    "result" => "Внимание!!!<br> С дополнительными работами уборка закончится позже 21:00. Пожалуйста, назначьте уборку пораньше, на другой день или запланируйте дополнительные работы на следующий раз.",
                    "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$modelOrder])
                ]);
            }
        }else{
            $id_subscrib=$modelOrder->id_subscription;
            $modelOrderSubs=Order::find()
                ->where("id_subscription = ".$id_subscrib." and start_cleaning is null and status <> 1 and status <> -1 ")
                ->all();

            foreach($modelOrderSubs as $valueOder){
               // Yii::trace($valueOder->id);
                $numberValidate=0;
                $numberAllAdd=0;
                $arrOrderAdditionals=[];
                 foreach ($post["arrAdditinal"] as $keyAdditional => $valueAdditonoal) {
                    if ($valueAdditonoal > 0) {
                        $ordAdd = new OrderAdditional();
                        $ordAdd->order_id = $valueOder->id;
                        $ordAdd->additional_id = $keyAdditional;
                        $ordAdd->count = $valueAdditonoal;
                        $arrOrderAdditionals[] = $ordAdd;
                        $numberAllAdd++;
                        if($ordAdd->validate()) $numberValidate++;

                    }
                 }
//                Yii::trace($numberValidate);
//                Yii::trace($numberAllAdd);
//                Yii::trace($modelOrder->timeAllClean($arrOrderAdditionals));
                if($numberValidate==$numberAllAdd && $valueOder->timeAllClean($arrOrderAdditionals)<=13) {

                    if($modelOrder->checkIntervalTime($modelOrder->date_cleaning,$modelOrder->timeAllClean($arrOrderAdditionals))) {
                        OrderAdditional::deleteAll(["order_id" => $valueOder->id]);
                        foreach ($arrOrderAdditionals as $keyAdditional => $valueAdditonoal) {
                            $valueAdditonoal->save();
                        }
                    }else{
                        return  json_encode([
                            "result" => "Внимание!<br> С дополнительными работами уборка закончится позже 21:00. Пожалуйста, назначьте уборку пораньше, на другой день или запланируйте дополнительные работы на следующий раз.",
                            "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$modelOrder])
                        ]);
                    }



                }else{

                    return  json_encode([
                        "result" => "Внимание!!!<br> С дополнительными работами уборка закончится позже 21:00. Пожалуйста, назначьте уборку пораньше, на другой день или запланируйте дополнительные работы на следующий раз.",
                        "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$modelOrder])
                    ]);
                }

            }
            foreach ($modelOrderSubs as $valueOder) {
                $valueOder->save();
            }

        }
        return  json_encode([
            "result" => "Изменения сохранены",
            "detail"=>$this->renderAjax("item/_detail",["modelOrder"=>$modelOrder]),
            "flagDeleteBlockCoupon"=>$flagDeleteBlockCoupon
        ]);

    }

    public function actionChoiceOfPayment($id){

        $post =  Yii::$app->request->post();
        $order = Order::find()->where(["id"=>$id,"client_id"=>Yii::$app->getUser()->id])->one();

        if(  !isset($order)
            || ($order->pay_form_id==1  && $order->status==10 && $order->IsCashbox )
            || $order->status==-1
            || ($order->status==0 && $order->pay_form_id==2)
            ||  ($order->pay_form_id==1  && $order->status==0 && $order->IsCashbox )
        ){
            return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['orders/index']));
        }
        if(isset ($post["Order"]["pay_form_id"]) && isset($order) && $order->modered==0){

            $order->pay_form_id = $post["Order"]["pay_form_id"];
            $order->update();

            return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['orders/end-create-order',"order_id" => $order->id]));
        }


        $dataProvider= Order::find()->joinWith("packet")->joinWith("orderAdditionals")
            ->andWhere(["order.id"=>$id])->andWhere(["client_id"=>Yii::$app->getUser()->id])->one();

        if($dataProvider==null)
            return $this->redirect("/");

        return $this->render("choice-of-payment", [
            "modelOrder"=>$dataProvider
        ]);
    }
    public function actionClear(){

        CouponsUsed::deleteAll();
        CashBackCliner::deleteAll();
        Cashbox::deleteAll();
        ReminderMobile::deleteAll();
        ArrivalExpense::deleteAll();
        OrderAdditional::deleteAll();
        OrderAddCleaner::deleteAll();
        VerificationTelephone::deleteAll();
        FineUser::deleteAll();
        Shopping::deleteAll();

        Journal::deleteAll();
        Order::deleteAll();
        return json_encode(true);
    }

    public function actionModer(){

        Order::updateAll(["modered"=>1],"status <> 10 and status <>-1");
        return json_encode(true);
    }


    public function actionGetAdditionalInfo($id)
    {
        $model=Order::findOne($id);

        $modelAdditional=$model->orderAdditionals;


        foreach($modelAdditional as $addional){
            $massiv[]=[$addional->additional->id,$addional->count];
        }

        return json_encode($massiv);
    }


    public function actionTest()
    {
//        $model = new Order;
//        $model->test();

        $date1="16.12.2017";
//        $date2="06.06.2017";
//        $date2=$date1;
//        $date=strtotime($date);
        $model = new SaveOrder;


        //$model->addDateIntervalFourRegular(,2);

        $date=strtotime($date2);
//        return json_encode([
//            $date2,
//            date("d.m.Y", mktime(0, 0, 0, date("m", $date), date("d", $date)+ $model->addDateIntervalFourRegular($date2,2), date("Y", $date))),
//            date("d.m.Y", mktime(0, 0, 0, date("m", $date), date("d", $date)+ $model->addDateIntervalFourRegular($date2,3), date("Y", $date))),
//            date("d.m.Y", mktime(0, 0, 0, date("m", $date), date("d", $date)+ $model->addDateIntervalFourRegular($date2,4), date("Y", $date))),
//            date("d.m.Y", mktime(0, 0, 0, date("m", $date), date("d", $date)+ $model->addDateIntervalFourRegular($date2,5), date("Y", $date))),
//            date("d.m.Y", mktime(0, 0, 0, date("m", $date), date("d", $date)+ $model->addDateIntervalFourRegular($date2,6), date("Y", $date))),
//             date("d.m.Y", mktime(0, 0, 0, date("m", $date), date("d", $date)+ $model->addDateIntervalFourRegular($date2,7), date("Y", $date))),
//        $model->addDateIntervalFourRegular($date2,2),
//            $model->addDateIntervalFourRegular($date2,3),
//                $model->addDateIntervalFourRegular($date2,4),
//                    $model->addDateIntervalFourRegular($date2,5),
//                        $model->addDateIntervalFourRegular($date2,6),
//                            $model->addDateIntervalFourRegular($date2,7),
//
//
//        ]);

        $masDate=[];

        $i=0;
        $masDate[]="22.12.2017";

        for($step=0;$step<12;$step++){
            Yii::trace("+++");Yii::trace($step);
            $masDate[]= date("d.m.Y",strtotime($masDate[$step]." +".$model->addDateIntervalFourRegular($masDate[$step],2)." day"));


        }


        return json_encode([$masDate]);

    }
    public function actionTest2()
    {


        $text = "   dsvdsv    sdvds  ";

      return json_encode(explode(" ", preg_replace('|\s+|', ' ', trim($text))));
    }

    public function actionTest3($id)
    {

        $dataProvider= Order::find()->joinWith("packet")->joinWith("orderAdditionals")
            ->andWhere(["order.id"=>$id])->andWhere(["client_id"=>Yii::$app->getUser()->id])->one();

        if($dataProvider==null)
            return $this->redirect("/");
        return $this->render('goYandex', ["modelOrder"=>$dataProvider]);
    }

    public function actionTestEmail()
    {

        $model=Order::find()->where(["id"=>8])->one();



        Yii::$app
            ->mailer
            ->compose(
                ['html' => 'Rating-html', 'text' => 'Rating-text'],
                [
                    'modelOrder' => $model,
                    'arrOrderAdditionals' => $model->orderAdditionals,
                ]
            )
            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
            ->setTo($model->client->email)
            ->setSubject('Оцените наш сервис')
            ->send();

        $model=Order::find()->where(["id"=>827])->one();



        Yii::$app
            ->mailer
            ->compose(
                ['html' => 'Rating-html', 'text' => 'Rating-text'],
                [
                    'modelOrder' => $model,
                    'arrOrderAdditionals' => $model->orderAdditionals,
                ]
            )
            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
            ->setTo($model->client->email)
            ->setSubject('Оцените наш сервис')
            ->send();


//        if($model->regular_id==1)
//            $subject="Вы успешно заказали уборку" ;
//        else
//            $subject="Вы оформили подписку на уборку" ;
//
//
//        Yii::$app
//            ->mailer
//            ->compose(
//                ['html' => 'detailOrder-html', 'text' => 'detailOrder-text'],
//                [
//                    'modelOrder' => $model,
//                    'arrOrderAdditionals' => $model->orderAdditionals,
//                ]
//            )
//            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
//            ->setTo($model->client->email)
//            ->setSubject($subject)
//            ->send();
//
//        Yii::$app
//            ->mailer
//            ->compose(
//                ['html' => 'nextDayOrder-html', 'text' => 'nextDayOrder-text'],
//                [
//                    'modelOrder' => $model,
//                    'arrOrderAdditionals' => $model->orderAdditionals,
//                ]
//            )
//            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
//            ->setTo($model->client->email)
//            ->setSubject('Напоминаем, у вас завтра уборка')
//            ->send();
//
//
//        $model=Order::find()->where(["id"=>875])->one();
//
//
//        Yii::$app
//            ->mailer
//            ->compose(
//                ['html' => 'Rating-html', 'text' => 'Rating-text'],
//                [
//                    'modelOrder' => $model,
//                    'arrOrderAdditionals' => $model->orderAdditionals,
//                ]
//            )
//            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
//            ->setTo($model->client->email)
//            ->setSubject('Оцените наш сервис')
//            ->send();
//
//
//        if($model->regular_id==1)
//            $subject="Вы успешно заказали уборку" ;
//        else
//            $subject="Вы оформили подписку на уборку" ;
//
//
//        Yii::$app
//            ->mailer
//            ->compose(
//                ['html' => 'detailOrder-html', 'text' => 'detailOrder-text'],
//                [
//                    'modelOrder' => $model,
//                    'arrOrderAdditionals' => $model->orderAdditionals,
//                ]
//            )
//            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
//            ->setTo($model->client->email)
//            ->setSubject($subject)
//            ->send();
//
//
//        Yii::$app
//            ->mailer
//            ->compose(
//                ['html' => 'passwordNew-html', 'text' => 'passwordNew-text'],
//                ['user' => $model->client,'password'=>'1dvrebe']
//            )
//            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
//            ->setTo($model->client->email)
//            
//            ->setSubject('Новый пароль')
//            ->send();
//
//        Yii::$app
//            ->mailer
//            ->compose(
//                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
//                ['user' => $model->client]
//            )
//            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
//            ->setTo($model->client->email)
//            
//            ->setSubject('Восстановление пароля')
//            ->send();
//
//        Yii::$app->mailer->compose(['html' => 'SignupToken-html', 'text' => 'SignupToken-text'], ['user' => $model->client])
//            ->setFrom([\Yii::$app->params['infoEmail'] => \Yii::$app->name . ''])
//            ->setTo($model->client->email)
//           
//            ->setSubject('Теперь вы с нами на '.\Yii::$app->name)
//            ->send();

    }


        public function actionPayCardOrder($id){
            $model=Order::find()->where(["id"=>$id,"client_id"=>Yii::$app->getUser()->id])->one();

            if(isset($model))
                return json_encode("yes");
            else
                return json_encode("no");
        }


}
