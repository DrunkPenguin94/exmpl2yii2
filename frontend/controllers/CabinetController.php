<?php
namespace frontend\controllers;

use Yii;
use frontend\models\UpdateDataUserForm;
use common\models\User;

use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\UploadedFile;
use frontend\models\UpdateUserInfo;
use frontend\models\UpdateUserPassword;
/**
 * Site controller
 */
class CabinetController extends Controller
{


    /**
     * Displays homepage.
     *
     * @return mixed
     */
//    public function actionIndex()
//    {
//        if(Yii::$app->user->isGuest)
//            return $this->goHome();
//        $modelUpdateDataUser = new UpdateDataUserForm();
//        $userData = User::find()->where([id=>Yii::$app->user->getId()])->one();
//        $request = Yii::$app->request->post();
//
//        if (isset($request["UpdateDataUserForm"]) && !isset($request["UpdateDataUserForm"]["password_old"]) && $userData->validate()) {
//            if($request["UpdateDataUserForm"]["name"]!="" && $request["UpdateDataUserForm"]["name"]!=$userData["name"]){
//                $userData['name']=$request["UpdateDataUserForm"]["name"];
//            }
//            if($request["UpdateDataUserForm"]["family"]!="" && $request["UpdateDataUserForm"]["family"]!=$userData["family"]){
//                $userData['family']=$request["UpdateDataUserForm"]["family"];
//            }
//            if($request["UpdateDataUserForm"]["email"]!="" && $request["UpdateDataUserForm"]["email"]!=$userData["email"]){
//                $userData['email']=$request["UpdateDataUserForm"]["email"];
//            }
//            if($request["UpdateDataUserForm"]["phone"]!="" && $request["UpdateDataUserForm"]["phone"]!=$userData["phone"]){
//                $userData['phone']=$request["UpdateDataUserForm"]["phone"];
//            }
//            $userData->save();
//        }
//
//        $differentInfo="";
//        if (isset($request["UpdateDataUserForm"]) && isset($request["UpdateDataUserForm"]["password_old"])){
//            $userData->setPassword($request["UpdateDataUserForm"]["password_new"]);
//            $userData->generatePasswordResetToken();
//            $userData->save();
//        }
//
//
//
//
//        //Yii::trace($user_data);
//        return $this->render('index',[
//            "modelUpdateDataUser" => $modelUpdateDataUser,
//            "userData" =>$userData,
//            "differentInfo"=>$differentInfo,
//        ]);
//   }


    public function actionIndex()
    {
        if(Yii::$app->user->isGuest)
            return $this->goHome();
//        $modelUpdateDataUser = new UpdateDataUserForm();
        $post = Yii::$app->request->post();
        $updateUser=new UpdateUserInfo;
        $updatePassword=new UpdateUserPassword;

        if($updateUser->load($post)){
            $updateUser->id=Yii::$app->getUser()->id;
            $flag=$updateUser->changeInfo();
            if(isset($flag)) {
                Yii::$app->session->addFlash('success', 'Изменение сохранены');
            }
        }elseif($updatePassword->load($post)) {
            $updatePassword->id=Yii::$app->getUser()->id;
            if($updatePassword->validate())
                $flag=$updatePassword->changePassword();

            if(isset($flag)){
                 Yii::$app->session->addFlash('success', 'Пароль обновлен');
            }
        }
        $userData = User::find()->where(['id'=>Yii::$app->user->getId()])->one();


        $updateUser->id=$userData->id;
        $updateUser->email=$userData->email;
        $updateUser->name=$userData->name;
        $updateUser->family=$userData->family;
        $updateUser->phone=$userData->phone;

        $updatePassword->password="";
        $updatePassword->password_new="";
        $updatePassword->password_new_repeat="";



        //Yii::trace($user_data);
        if($userData->is_cleaner==1)
            return $this->render('index',[
                 "updateUser" =>$updateUser,
                 "updatePassword"=>$updatePassword,
                 "userData"=>$userData
            ]);
        else
            return $this->render('index_user',[
                "updateUser" =>$updateUser,
                "updatePassword"=>$updatePassword,
                "userData"=>$userData
            ]);
    }

}
