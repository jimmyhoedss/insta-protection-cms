<?php
namespace backend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\InstapPlanPool;
use common\models\UserPlanDetailEdit;

/**
 * Class Menu
 * @package backend\components\widget
 */
class TabMenuPlanWidget extends Widget
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
        } else if ($this->page == "pending_approval") {
            $active2 = "active";
        } else if ($this->page == "pending_edit_approval") {
            $active3 = "active";
        }

        
        $link1 =  Url::to(["instap-plan-pool/index"]);
        $link2 =  Url::to(["instap-plan-pool/pending-approval"]);
        $link3 =  Url::to(["instap-plan-pool/pending-edit-approval"]);

        //use to display badge
        $countPA = InstapPlanPool::countTotalPendingApproval();
        $countPEA = UserPlanDetailEdit::countTotalPendingEditApproval();
        $badgeColorPA = $countPA > 0 ? "background-color:red !important" : "";
        $badgeColorPEA = $countPEA > 0 ? "background-color:red !important" : "";
        
        $content = <<<HEREDOC
<ul class="nav nav-tabs">
  <li class="$active1"><a href="$link1">All </a></li>
  <li class="$active2"><a href="$link2">Pending Approval  <span class="badge" style="$badgeColorPA;">$countPA</span></a></li>
  <li class="$active3 pull-right"><a href="$link3">Pending Edit Approval  <span class="badge" style="$badgeColorPEA;">$countPEA</span></a></li>
</ul>
<br>
HEREDOC;

//

        //$this->registerClientScript();
        return $content;
    }
}
