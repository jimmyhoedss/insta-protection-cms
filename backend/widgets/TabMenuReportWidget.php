<?php
namespace backend\widgets;

use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use common\models\InstapReport;

/**
 * Class Menu
 * @package backend\components\widget
 */
class TabMenuReportWidget extends Widget
{
    public $page = "declaration_report";

    public function init()
    {
        parent::init();        
    }

    public function run()
    {
        $active1 = $active2 = $active3 = $active4 = $active5 = $active6 = "";
        if ($this->page == InstapReport::TYPE_DECLARATION_REPORT) {
            $active1 = "active";
        } else if ($this->page == InstapReport::TYPE_DISTRIBUTOR_ACTIVATION_REPORT) {
            $active2 = "active";
        } else if ($this->page == InstapReport::TYPE_AM_TRANSACTION_REPORT) {
            $active3 = "active";
        } else if ($this->page == InstapReport::TYPE_RETAIL_TRANSACTION_REPORT) {
            $active4 = "active";
        } else if ($this->page == InstapReport::TYPE_SOH_REPORT) {
            $active5 = "active";
        } else if ($this->page == InstapReport::TYPE_CLAIM_SUBMISSION_REPORT) {
            $active6 = "active";
        }

        
        $link1 =  Url::to(["instap-report/declaration-report"]);
        $link2 =  Url::to(["instap-report/distributor-activation-report"]);
        $link3 =  Url::to(["instap-report/am-transaction-report"]);
        $link4 =  Url::to(["instap-report/retail-transaction-report"]);
        $link5 =  Url::to(["instap-report/soh-report"]);
        $link6 =  Url::to(["instap-report/claim-submission-report"]);

        $content = <<<HEREDOC
<ul class="nav nav-tabs">
  <li class="$active1"><a href="$link1">Declaration Report</a></li>
  <li class="$active2"><a href="$link2">Distributor Activation Report</a></li>
  <li class="$active3"><a href="$link3">Account Manager Transaction Report</a></li>
  <li class="$active4"><a href="$link4">Retail Transaction Report</a></li>
  <li class="$active5"><a href="$link5">Stock On Hand Report</a></li>
  <li class="$active6"><a href="$link6">Claim Submission Report</a></li>
</ul>
<br>
HEREDOC;

//

        //$this->registerClientScript();
        return $content;
    }
}
