<?php
namespace frontend\controllers;

use common\models\Question;
use Yii;
use common\models\LoginForm;
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
use yii\data\ActiveDataProvider;
/**
 * Site controller
 */
class QuestionController extends Controller
{


    /**
     * Displays homepage.
     *
     * @return mixed
     */



    public function actionIndex($page=null)
    {


//        $sql_req=Question::find()
//            ->select(['count(question.id) as count_type','question.id_question_type'])
//            ->innerJoinWith("questionType")->orderBy("question_type.sort")
//            ->groupBy('question.id_question_type')->all();
//        Yii::trace($sql_req);
//        $array_sql_req=[];
//        $array_page=0;
//        foreach($sql_req as $model){
//            $array_page++;
//            $array_sql_req[$array_page]=$model['count_type'];
//        }

//            $dataProvider = new ActiveDataProvider([
//            'query' => Question::find()->innerJoinWith("questionType")->orderBy("question_type.sort,id"),
//            'pagination' => [
//                "pageSize" => 30,
//                //'defaultPageSize' => 10,
//
//                'forcePageParam' => false,
//            ]
//        ]);
        $sort="";
        if ($page==null) {$sort=1;$page=1;}
        else $sort=$page;

        $dataProvider=Question::find()
            ->innerJoinWith("questionType")
            ->where(['question_type.sort'=>$sort])
            ->orderBy("question_type.sort,id")
            ->all();

        return $this->render('index',[

            "dataProvider" =>$dataProvider,
            "page"=>$page,

        ]);

    }

    public function actionOneQuestion($text="")
    {




        $dataProvider =  Question::find()->innerJoinWith("questionType")->where(["like","question.name",$text])->orderBy("question_type.sort,id")->one();

        Yii::trace($dataProvider);
        Yii::trace($dataProvider["name"]);
        return $this->render('one',[

            "dataProvider" =>$dataProvider,

        ]);

    }
}
