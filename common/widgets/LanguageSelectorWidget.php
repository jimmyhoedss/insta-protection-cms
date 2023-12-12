<?php

namespace common\widgets;

use yii\helpers\Url;
use yii\base\Widget;
use Yii;


class LanguageSelectorWidget extends Widget
{
    public $layout;

    public function run()
    {
        $lang = "en-US";
        if (isset(Yii::$app->language)){
            $lang = Yii::$app->language;
        }

        $en = "International Site";
        $cn = "简体中文";
        $id = "Bahasa Indonesia";

        if ($this->layout == "compact") {
            $en = "EN";
            $cn = "简体中文";
            $id = "ID";
        }


        $icon = "-";
        if ($lang == "en-US") {
            $icon = '<i class="fa fa-globe" aria-hidden="true"></i> <span class="small">'.$en.'</span>';
        } else if ($lang == "zh-CN") {
            $icon = '<i class="flag-icon flag-icon-cn"></i> <span class="small">'.$cn.'<span>';
        } else if ($lang == "id-ID") {
            $icon = '<i class="flag-icon flag-icon-id"></i> <span class="small">'.$id.'<span>';
        }
        $html = '<a href="">' . $icon . '</a>';
        //$html = '<a href="' . Url::to(["/choose-your-language"]) . '">' . $icon . '</a>';

        //

        return $html;
    }
}

