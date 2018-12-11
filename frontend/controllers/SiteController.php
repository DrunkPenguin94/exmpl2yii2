<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\BackCall;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
/**
 * Site controller
 */
use common\models\User;
use common\models\Coupon;
use yii\web\Response;
use yii\helpers\Url;
use frontend\models\ESignupForm;
use yii\widgets\ActiveForm;
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'eauth' => array(
                // required to disable csrf validation on OpenID requests
                'class' => \nodge\eauth\openid\ControllerBehavior::className(),
                'only' => array('login'),
            ),
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'login'],
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                  //  'logout' => ['post'],
                ],
            ],
        ];
    }



    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }


    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex($r=null)
    {
        $post=Yii::$app->request->post();

        $modelBackCall=new BackCall;
        if($modelBackCall->load($post) && $modelBackCall->validate()){
            $modelBackCall->sendEmail();
            return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['',"flagBackCall" => "true"]));
        }

        $modelUser = \common\models\User::findOne(Yii::$app->user->id);
        if($modelUser->is_cleaner)
            return $this->redirect("/cleaner");

        if($r!=null){
            $modelRef =  User::find()
                ->where("id=:referal",["referal"=>$r])
                ->one();
           if($modelRef!=null  ){
                Yii::$app->session->set("referalUserId",$modelRef->id);

            }
        }


        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {

        $serviceName = Yii::$app->getRequest()->getQueryParam('service');

        if (isset($serviceName)) {
            /** @var $eauth \nodge\eauth\ServiceBase */
            $eauth = Yii::$app->get('eauth')->getIdentity($serviceName);
            $eauth->setRedirectUrl(Yii::$app->getUser()->getReturnUrl());
            $eauth->setCancelUrl(Yii::$app->getUrlManager()->createAbsoluteUrl('site/login'));

            try {
                if ($eauth->authenticate()) {
                    //  var_dump($eauth->getIsAuthenticated(), $eauth->getAttributes()); exit;

                    $identity = User::findByEAuth($eauth);
                    Yii::$app->getUser()->login($identity);


                    if(isset(Yii::$app->user->identity->attributes['id'])){
                        $service_id= Yii::$app->user->identity->attributes['id'];


                        // var_dump(Yii::$app->user->identity["profile"]["name"]); exit;
                        $modelUser= User::find()->where(["username"=>$service_id])->one();
                        if($modelUser==null){
                            $modelESignupForm = new ESignupForm();
                            $modelESignupForm->username =$service_id;
                            $modelESignupForm->password= Yii::$app->security->generateRandomKey(10);
                            $modelESignupForm->email= Yii::$app->user->identity["profile"]["email"];

                            if(isset(Yii::$app->user->identity["profile"]["name"])){

                                $nameAndFamily=Yii::$app->user->identity["profile"]["name"];
                                $nameAndFamily=explode(" ", preg_replace('|\s+|', ' ', trim($nameAndFamily)));
                                $modelESignupForm->name =$nameAndFamily[0];
                                $modelESignupForm->family =$nameAndFamily[1];
                            }else if(isset(Yii::$app->user->identity["profile"]["full_name"])){

                                $nameAndFamily=Yii::$app->user->identity["profile"]["full_name"];
                                $nameAndFamily=explode(" ",$nameAndFamily);
                                $modelESignupForm->name =$nameAndFamily[0];
                                $modelESignupForm->family =$nameAndFamily[1];

                            }
                            else{
                                $modelESignupForm->name ="";
                            }

                            $modelESignupForm->signup();
                            $modelUser= User::find()->where(["username"=>$service_id])->one();
                        }else{

                        }
                        if($modelUser!=null){
                            if(! Yii::$app->user->login($modelUser, 0)) Yii::$app->user->logout();
                        }




                    }else Yii::$app->user->logout();



                    //            if (User::find()->where(["username"=>Yii::$app->user->identity->_attributes])
                    // special redirect with closing popup window
                    //    return $this->redirect(Url::previous("auth"));
                    $eauth->redirect(Url::to("/orders"));

                }
                else {
                    // close popup window and redirect to cancelUrl
                    $eauth->cancel();
                }


            }
            catch (\nodge\eauth\ErrorException $e) {
                // save error to show it later
                Yii::$app->getSession()->setFlash('error', 'EAuthException: '.$e->getMessage());

                // close popup window and redirect to cancelUrl
//              $eauth->cancel();
                Yii::$app->user->logout();
                $eauth->redirect($eauth->getCancelUrl());
            }
        }


        if (!\Yii::$app->user->isGuest) {
            return $this->goBack();
        }

        $model = new LoginForm();

        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            //Yii::trace(Yii::$app->request->post());
            return ActiveForm::validate($model);
        }
        else{
            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                //var_dump(Yii::$app->request);die();
//                return $this->redirect(Url::to("/orders"));
                return $this->goBack();
            } else {
             //   return $this->redirect("/?login");
                 return  $this->redirect("index");
            }

        }

    }


    /*public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }*/

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {


            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }



    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {

       // Yii::trace('llololo');
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }
        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();

        if ($model->load(Yii::$app->request->post())) {
            if ($model->sendEmail()) {
//                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                   // return $this->goHome();
                return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['site/index','windowRequestPass'=>'show_1']));
            } else {
//                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['site/index','windowRequestPass'=>'false']));
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
            if ($model->resetPassword()) {
                Yii::$app->session->setFlash('success', 'New password was saved.');

                return $this->redirect(Yii::$app->urlManager->createAbsoluteUrl(['site/index','windowRequestPass'=>'show_2']));
            }
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }



        return $this->goHome();

    }




    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */

    public function actionActivateUser($token)
    {
        $modelUser  = User::findByUserActiveToken($token);
        if ($modelUser !=null) {
            $modelUser->status=10;

            $modelUser->save();

            $modelCoupon = new Coupon;
            $modelCoupon->createFirstClean(Yii::$app->user->id);

           // Yii::$app->getUser()->login($modelUser);

        }
        return $this->redirect("/cabinet");
    }



    public function actionGetTimeServer(){
        $timestamp = time();
        Yii::trace(date("Y m d H i",$timestamp));
        $timestamp += Yii::$app->params["timeDifference"]*60*60;
        $year=date("Y",$timestamp);
        $month=date("m",$timestamp);
        $day=date("d",$timestamp);
        $hour=date("H",$timestamp);
        $minute=date("i",$timestamp);
        Yii::trace(date("Y m d H i",$timestamp));
        return  json_encode([
            "y" =>$year,
            "m" =>$month-1,
            "d" =>$day,
            "h" =>$hour,
            "i" =>$minute
        ]);
    }


}
