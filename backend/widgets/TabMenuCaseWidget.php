<?php
namespace backend\widgets;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\UserCase;

/**
 * Class Menu
 * @package backend\components\widget
 */
class TabMenuCaseWidget extends Widget
{
    public $page = "index";

    public function init()
    {
        parent::init();        
    }

    public function run()
    {
        $active1 = $active2 = $active3 ="";
        if ($this->page == "index") {
            $active1 = "active";
        } else if ($this->page == "claim_pending") {
            $active2 = "active";
        }else if ($this->page == "claim_reject") {
            $active3 = "active";
        }

        
        $link1 =  Url::to(["user-case/index"]);
        $link2 =  Url::to(["user-case/claim-pending"]);
        $link3 =  Url::to(["user-case/claim-reject"]);

        //use to display badge
        $countPA = UserCase::countTotalPendingApproval();
        $badgeColorPA = $countPA > 0 ? "background-color:red !important" : "";


        $content = <<<HEREDOC
<ul class="nav nav-tabs">
  <li class="$active1"><a href="$link1">All</a></li>
  <li class="$active2"><a href="$link2">Claim Pending  <span class="badge badge-primary" style="$badgeColorPA;">$countPA</span></a></li>
  <li class="$active3 pull-right"><a href="$link3">Claim Rejected/ Cancelled</a></li>
</ul>
<br>
HEREDOC;

//

        //$this->registerClientScript();
        return $content;
    }
}
