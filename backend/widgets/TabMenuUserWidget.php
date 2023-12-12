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
class TabMenuUserWidget extends Widget
{
    public $page = "active";

    public function init()
    {
        parent::init();        
    }

    public function run()
    {
        $active1 = $active2 = $active3 = "";
        if ($this->page == "index") {
            $active1 = "active";
        } else if ($this->page == "ip_staff") {
            $active2 = "active";
        } else if ($this->page == "disabled") {
            $active3 = "active";
        }
        
        $link1 =  Url::to(["user/index"]);
        $link2 =  Url::to(["user/ip-staff"]);
        $link3 =  Url::to(["user/disabled"]);
        
        $content = <<<HEREDOC
<ul class="nav nav-tabs">
  <li class="$active1"><a href="$link1">All</a></li>
  <li class="$active2 "><a href="$link2">IP Staffs</a></li>
  <li class="$active3 pull-right"><a href="$link3">Disabled/Suspended</a></li>
</ul>
<br>
HEREDOC;

//

        //$this->registerClientScript();
        return $content;
    }
}
