<?php
namespace frontend\models;
use Yii;
use yii\base\Model;


use common\models\Order;

/**
 * Signup form
 */
class UpdateSubscription extends Model
{


    public function rules()
    {
        return [

        ];
    }


    public function updateVolume($modelSelectOrder){
        $id_subscrib=$modelSelectOrder->id_subscription;
        $date_cleaning=$modelSelectOrder->date_cleaning;
        $model= Order::find()
            ->where("id_subscription = ".$id_subscrib." and date_cleaning > '".$date_cleaning."'")
            ->all();

        foreach($model as $value){
            $value->area=$modelSelectOrder->area;
            $value->sanuzel=$modelSelectOrder->sanuzel;
            if(!$value->validate()){
                return false;
            }
        }
        return $model;
    }


    public function updatePacket($modelSelectOrder){
        $id_subscrib=$modelSelectOrder->id_subscription;
        $date_cleaning=$modelSelectOrder->date_cleaning;
        $model= Order::find()
            ->where("client_id=".Yii::$app->user->id." and  id_subscription = ".$id_subscrib." and ( status=10 or ( status=0 and start_cleaning is null ) )")
            ->all();
        $numberOrder=1;
        foreach($model as $value){
            $value->packet_id=$modelSelectOrder->packet_id;
            $numberOrder++;
            if(!$value->validate()){
                return false;
            }
        }
        return $model;

    }



    public function updateRegular($modelSelectOrder){
        $id_subscrib=$modelSelectOrder->id_subscription;
        $date_cleaning=$modelSelectOrder->date_cleaning;
        $model= Order::find()
            ->where("client_id=".Yii::$app->user->id." and id_subscription = ".$id_subscrib."  and ".
                "( status=10 or ( status=0 and start_cleaning is null ) ) and id<>".$modelSelectOrder->id)
            ->all();

        switch ($modelSelectOrder->regular_id) {
            case 2:
//                Yii::trace("+1");
//                Yii::trace($this->helpFuncAddDay($model,$modelSelectOrder,35,strtotime($modelSelectOrder->date_cleaning)));
                return $this->helpFuncAddDay($model,$modelSelectOrder,7,strtotime($modelSelectOrder->date_cleaning));
                break;
            case 3:
//                Yii::trace("+2");
//                Yii::trace($this->helpFuncAddDay($model,$modelSelectOrder,35,strtotime($modelSelectOrder->date_cleaning)));
                return $this->helpFuncAddDay($model,$modelSelectOrder,14,strtotime($modelSelectOrder->date_cleaning));
                break;
            case 4:
             //   Yii::trace("+3");
              //  Yii::trace($this->helpFuncAddDay($model,$modelSelectOrder,35,strtotime($modelSelectOrder->date_cleaning)));
                return $this->helpFuncAddDay($model,$modelSelectOrder,35,strtotime($modelSelectOrder->date_cleaning));
                break;
        }


        return false;

    }
    //помогает изменить даты для послудующих заказов
    public function helpFuncAddDay($model,$modelSelectOrder,$step, $oldDate){
        $numberOrder=1;

        $dayStep=1;
        $modelSaveOrder= new SaveOrder;
        foreach($model as $value){
            if($step==7 || $step==14){
                $dayAdd=$step*$dayStep;
            }else{
                $dayAdd=$modelSaveOrder->addDateIntervalFourRegular($dayStep,$dayStep+1);
            }

            $value->regular_id=$modelSelectOrder->regular_id;
            $value->date_cleaning=date("Y-m-d H:i",mktime(
                date("H",$oldDate),
                date("i",$oldDate),
                date("s",$oldDate),
                date("m",$oldDate),
                date("d",$oldDate)+$dayAdd,
                date("Y",$oldDate)
            ));
            $value->dayweek_cleaning = date("N", strtotime($value->date_cleaning));
            //Yii::trace($value->date_cleaning);
            $numberOrder++;
            if(!$value->validate()){
                Yii::trace($value->getErrors());
                return false;
            }
            $dayStep++;
        }
        return $model;
    }

    public function getTimeAdd($oldDate,$differenceDay=0,$differenceMonth=0){
        return mktime(
            date("H",$oldDate),
            date("i",$oldDate),
            date("s",$oldDate),
            date("m",$oldDate)+$differenceMonth,
            date("d",$oldDate)+$differenceDay,
            date("Y",$oldDate)
        );
    }
}
