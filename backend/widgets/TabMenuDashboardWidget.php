<?php
namespace backend\widgets;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\UserCase;
use common\models\form\DashboardForm;

/**
 * Class Menu
 * @package backend\components\widget
 */
class TabMenuDashboardWidget extends Widget
{
    public $page = "index";

    public function init()
    {
        parent::init();        
    }

    public function run()
    {
        $active1 = $active2 = $active3 ="";
        if ($this->page == DashboardForm::THIS_WEEK) {
            $active1 = "active";
        } else if ($this->page == DashboardForm::THIS_MONTH) {
            $active2 = "active";
        }else if ($this->page == DashboardForm::THIS_WEEK) {
            $active3 = "active";
        }

        
        $link1 =  Url::to(["dashboard/time", "time" => DashboardForm::THIS_WEEK]);
        $link2 =  Url::to(["dashboard/time", "time" => DashboardForm::THIS_MONTH]);
        $link3 =  Url::to(["dashboard/time"]);


        $content = <<<HEREDOC
<ul class="nav nav-tabs">
  <li class="$active1"><a href="$link1">This Week</a></li>
  <li class="$active2"><a href="$link2">This Month</a></li>
</ul>
<br>
HEREDOC;

//

        //$this->registerClientScript();
        return $content;
    }
}
