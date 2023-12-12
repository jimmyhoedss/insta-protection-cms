<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\log\Logger;
use backend\modules\system\models\SystemLog;


$this->title = Yii::t('backend', 'Otp List By Mobile Number');



?>
<p class="text-muted"><i>OTP will not display in the list if user has successfully login to the App.</i></p>
<div class="box">
    <div class="box-body">
        <?php

            $html = "<div class='log-list'>";
            if(!empty($models)) {
                foreach($models as $model) {
                    $mobile_no = isset($model->userData->mobile_number_full) ? $model->userData->mobile_number_full : "";
                    $token = $model->token;
                    $date = Yii::$app->formatter->asDatetime($model->created_at);
                    $html .= "<i style = 'font-size : 20px' >" . $date . "&nbsp; :  mobile number: " .$mobile_no . "&nbsp; otp: <b>" . $token ."</b> <br>";
                }

            } else {
                $html .= "no record ";
            }
            $html .= "</div>";
            echo $html;

        ?>
    </div>

</div>
