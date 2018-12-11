<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '';
    public $baseUrl = '@web';
    public $css = [
        '/css/reset.css',
        '/css/fonts.css',
        '/css/jquery.formstyler.css',
        '/css/jquery-ui-1.10.4.custom.min.css',
        '/css/dialog.css',
        '/css/jquery.bxslider.css',
        '/css/style.css',





    ];
    public $js = [
        "/js/jquery-ui-1.10.4.custom.min.js",
        "/js/jquery.ui.datepicker-ru.js",
        "/js/jquery.bxslider.min.js",
        "/js/jquery.formstyler.min.js",
        "/js/maskedinput.js",
        "/js/modernizr.custom.js",
        "/js/classie.js",
        "/js/dialogFx.js",
        "/js/main.js"
    ];


    public $depends = [
        'yii\web\YiiAsset',
      //  'yii\bootstrap\BootstrapAsset',
    ];

    public $publishOptions = [
        'forceCopy' => true,
        //you can also make it work only in debug mode: 'forceCopy' => YII_DEBUG
    ];

}
