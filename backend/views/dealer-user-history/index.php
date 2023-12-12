<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\User;
use common\models\DealerCompany;
use common\models\DealerUser;
use common\models\DealerUserHistory;
use yii\helpers\Url;


$this->title = Yii::t('backend', 'Staff Movement Logs');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dealer-user-history-index">

    <div class="dealer_history">
    <?php

        $html = "<div class='log-list'>";
        $models =$dataProvider->models;

        echo \yii\widgets\LinkPager::widget([
            'pagination'=>$dataProvider->pagination,
        ]);
        if(empty($models)) {
             $html .= "<i> No logs found </i>";
        } else {
            $html .= DealerUserHistory::getDealerUserHistoryLayout($models);
        }
        $html .= "</div>";
        echo $html;

        echo \yii\widgets\LinkPager::widget([
            'pagination'=>$dataProvider->pagination,
        ]);

    ?>
    </div>

</div>
