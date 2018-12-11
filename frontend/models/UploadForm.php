<?php
namespace frontend\models;

use yii\base\Model;
use Yii;

use common\models\File;

/**
 * Signup form
 */
class UploadForm extends Model
{
    public $imageFile1;
    public $imageFile2;

    public function rules()
    {
        return [
           [['imageFile1','imageFile2'], 'file', 'skipOnEmpty' => true, 'extensions' => 'jpg,jpeg,png'],
            [['imageFile1','imageFile2'], 'safe'],
        ];
    }
    public function attributeLabels()
    {
        return [
            'imageFile1' => 'Фото жалоб 1',
            'imageFile2' => 'Фото жалоб 2'
        ];
    }
    public function upload()
    {

        $idImage=[
            'first'=>null,
            'second'=>null
        ];

        if ($this->validate()) {


            if($_FILES['UploadForm']['name']['imageFile1']!='') {
                $idImage['first']=$this->save($this->imageFile1);
            }
            if($_FILES['UploadForm']['name']['imageFile2']!='')
                $idImage['second']=$this->save($this->imageFile2);



            return $idImage;
        } else {
            return false;
        }
    }


    function save($file){

        //$file->saveAs(Yii::getAlias('@app').'\files\complaint\\' . $this->translit($file->baseName) . '.' . $file->extension);

        if($file->saveAs(Yii::getAlias('@app').'/files/complaint/' . $this->translit($file->baseName) . '.' . $file->extension)) {
            Yii::trace("++++++");
            $imageFile = new File();

            // $imageFile->path=Yii::getAlias('@app').'\files\complaint\\';
            $imageFile->path = Yii::getAlias('@app') . '/files/complaint/';
            $imageFile->name = $this->translit($file->baseName);
            $imageFile->format = $file->extension;
            $imageFile->type = "cmt";
            $imageFile->save();

            return $imageFile->id;
        }else{
            Yii::trace("++++++");
            return false;
        }
    }

    function translit($str) {
        $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($rus, $lat, $str);
    }
}
