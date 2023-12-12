<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\export\ExportMenu;
use common\components\Utility;
use common\models\User;
use backend\widgets\TabMenuReportWidget;

        


$this->title = Yii::t('backend', 'Report Generation History');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="instap-report-index">


    <p>
        <?php echo Html::a(Yii::t('backend', 'Generate Report'), ['report'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php 
        echo TabMenuReportWidget::widget(['page'=>$page]);
    ?>

    <?php

        $html = "<div class='log-list'>";
        $models =$dataProvider->models;

        echo \yii\widgets\LinkPager::widget([
            'pagination'=>$dataProvider->pagination,
        ]);

        foreach($models as $m) {
            $name = "";
            $email = "";
            if ($m->user) {
                $roleArr = $m->user->getRoleArrayById($m->created_by);
                foreach ($roleArr as $v) {
                    if($v == User::ROLE_IP_SUPER_ADMINISTRATOR){$name = User::allRoleNames()[User::ROLE_IP_SUPER_ADMINISTRATOR];} 
                    else if ($v == User::ROLE_IP_MANAGER) {$name = User::allRoleNames()[User::ROLE_IP_MANAGER];}
                    else if ($v == User::ROLE_IP_ADMIN_ASSISTANT) {$name = User::allRoleNames()[User::ROLE_IP_ADMIN_ASSISTANT];}
                    else if ($v == User::ROLE_IP_ADMINISTRATOR) {$name = User::allRoleNames()[User::ROLE_IP_ADMINISTRATOR];}
                    else if ($v == User::ROLE_ADMINISTRATOR) {$name = User::allRoleNames()[User::ROLE_ADMINISTRATOR];}
                }
            } 
            $by = "<b><i>" . $m->user->getPublicIdentity() .($name != "" ? " (".utf8_decode($name) . ")" : "") ." </i></b>";
            //$by = "dog";
            $d = Yii::$app->formatter->asDatetime($m->created_at);
            $d_start = Yii::$app->formatter->asDate($m->date_start);
            $d_end = Yii::$app->formatter->asDate($m->date_end);
            $filename = $m->type."(".$d_start."-".$d_end.")";

            $link = Utility::preSignedS3UrlDocDownload($m->document_path, $filename, ".".$m->file_type);
            $html .= "<i class='text-muted small'>" . $d . "</i> - <span class='text-success'>" . $d_start . "-" . $d_end ."</span> by " . $by . " <a href='" . $link . "'> <i class='fa fa-link'></i></a><br>";
        }
        $html .= "</div>";
        echo \yii\helpers\HtmlPurifier::process($html);

        echo \yii\widgets\LinkPager::widget([
            'pagination'=>$dataProvider->pagination,
        ]);
    ?>

</div>
