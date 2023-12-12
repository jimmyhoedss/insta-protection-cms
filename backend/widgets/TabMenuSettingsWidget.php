<?php
namespace backend\widgets;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Class Menu
 * @package backend\components\widget
 */
class TabMenuSettingsWidget extends Widget
{
    public $page = "website";

    public function init()
    {
        parent::init();        
    }

    public function run()
    {
        $active1 = $active2 = "";
        if ($this->page == "app") {
            $active1 = "active";
        } else if ($this->page == "website") {
            $active2 = "active";
        }

        
        $link1 =  Url::to(["settings/index"]);
        $link2 =  Url::to(["settings/website"]);

        $content = <<<HEREDOC
<ul class="nav nav-tabs">
  <li class="$active1"><a href="$link1">APP Settings</a></li>
  
</ul>
<br>
HEREDOC;

//<li class="$active2"><a href="$link2">Website Settings</a></li>

        //$this->registerClientScript();
        return $content;
    }
}
