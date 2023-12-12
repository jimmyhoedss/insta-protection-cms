<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\SysAuditTrail;

/* @var $this yii\web\View */
/* @var $searchModel common\models\search\SysAuditTrailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Audit Trail Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sys-audit-trail-index">

    <div class="audit-log">
    <?php

        $html = "<div class='log-list'>";
        $models =$dataProvider->models;

        echo \yii\widgets\LinkPager::widget([
            'pagination'=>$dataProvider->pagination,
        ]);

        foreach($models as $m) {
            $name = $m->created_by;
            $email = "";
            if ($m->user) {
                $name = $m->user->getPublicIdentity();
                $email = $m->user->email;
            } 

            $by = "<b><i>" . utf8_decode($name) . "</i></b> &lt;".$email."&gt;";
            //$by = "dog";
            $d = Yii::$app->formatter->asDatetime($m->created_at);

            $link = '/activity-log/audit-trail-log-detail?id=' . $m->id;
            $html .= "<i class='text-muted small'>" . $d . "</i> - <span class='text-success'>[" . $m->controller . "/" . $m->action ."]</span> by " . $by . " <a href='" . $link . "'> <i class='fa fa-link'></i></a><br>";
        }
        $html .= "</div>";
        echo \yii\helpers\HtmlPurifier::process($html);

        echo \yii\widgets\LinkPager::widget([
            'pagination'=>$dataProvider->pagination,
        ]);
    ?>
    </div>


</div>