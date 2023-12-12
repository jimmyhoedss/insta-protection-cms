<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;
use yii\web\JsExpression;
use trntv\filekit\widget\Upload;
use common\models\DealerCompany;
use common\models\UserProfile;
use common\models\InstapPlanPool;
use common\models\UserCase;
use common\models\QcdClaimRegistration;
use common\models\QcdRepairCentre;
use common\models\UserPlanDetail;
use dashboard\widgets\accordionWidget;
use kartik\widgets\DateTimePicker;


/* @var $this yii\web\View */
/* @var $model common\models\UserCase */

$this->title = Yii::t('dashboard', 'Submit a Claim');
$this->params['breadcrumbs'][] = ['label' => $model->policy_number, 'url' => ['view', 'id'=>$model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-case-create">

    <h4 class="sub-title"><?=Yii::t('dashboard','Policy Details')?></h4>
    <?php echo $model->getPolicyDetailLayout(); ?>

    <hr>
    <h4 class="sub-title"><?=Yii::t('dashboard','Plan Details')?></h4>
    <?php
        $userPlanDetail = $model->userPlan->details;
        if($userPlanDetail) {
            echo UserPlanDetail::getPlanDetailLayoutByModel($userPlanDetail);     
        } else {
            echo Yii::t('dashboard',"No plan details"); 
        }
    ?>
    <hr>

    <div class="user-case-form">

        <?php
            $html = "";

            $repair_centres = QcdRepairCentre::listRepairCentre($modelBrand, $model->region_id);
            if(is_null($repair_centres)){
                $html .=  '<center><strong><h2 style="color:red;">'.Yii::t("dashboard","There is currently no service centres found for your phone model.").'<br>'.Yii::t("dashboard","Please contact InstaProtection.").'</h2></strong></center>';
                echo $html;
            } else {
                $form = ActiveForm::begin(['enableClientValidation'=>false, 'options' => ['enctype' => 'multipart/form-data']]); 

                $html .= '<div id="repair_centre_address_wrapper" class="box" style="padding:10px; background-color:lightgrey; border-color:green; display:none;"><b><p id="repair_centre_address"></p></b></div>';

                $html .=  $form->field($m, 'repair_centre_id')->dropDownList(ArrayHelper::map( $repair_centres , 'id', 'repair_centre'), ['id' => 'repair_centre', 'prompt' => Yii::t('dashboard', 'Select repair centre')])->hint(Yii::t("dashboard","Please select from the dropdown list for our partner repair centers to visit for your repairs."));

                $html .=  $form->field($m, 'device_issue')->textarea(['rows' => 6, 'placeholder'=> Yii::t("dashboard", "Eg. On the 12th of Jan 2020, I dropped my phone onto the pavement while crossing the street at Yishun Park..."), 'maxlength'=>500])->hint(Yii::t("dashboard", "Please describe the events of incident saying the date and time of the incident, where and how it happened. You are also recommended to attach photos of the damaged phone and provide an alternative phone number for contact.")."<br><b>".Yii::t("dashboard", "Your description should not more than 500 characters."));

                $html .=  $form->field($m, 'contact_alt')->textarea(['rows' => 1, 'placeholder'=>"Eg. 91234567"])->label(Yii::t("dashboard","Alternate Number"));

                $html .= $form->field($m, 'occurred_at')->widget(DateTimePicker::class, [
                    'name' => 'dp_2',
                    'type' => DateTimePicker::TYPE_COMPONENT_PREPEND,
                    'pluginOptions' => [
                        'autoclose'=>true,
                        'format' => 'dd-M-yyyy hh:ii'
                    ]
                ]);

                $html .=  $form->field($m, 'location')->textarea(['rows' => 1, 'placeholder'=>"Eg. Yishun Park"]);

                $html .=  $form->field($m, 'image_file[]')->fileInput(['multiple' => true, 'accept' => 'image/*'])->label(Yii::t("dashboard", "Upload Photo"))->hint(Yii::t("dashboard","Max 5 images")); 

                $html .= "<div>";
                $html .= "<h3>".Yii::t("dashboard", "Other charges:")."</h3>";
                $html .= Yii::t("dashboard","- In the event if extra repairs are required apart from your screen crack, additional charges will be applied.").'<br>';
                $html .= Yii::t("dashboard"," - You are advised to back up your device before visiting the authorised service center.");
                $html .= "</div>";
                $html .= "<hr>";

                $html .= $form->field($m, 'check')->checkBoxList([Yii::t("dashboard", "I have read and agree to the Terms and Conditions"),Yii::t("dashboard","I understand that there will be additional charges if the repairs goes beyond my plan coverage"),Yii::t("dashboard","I have backed up my device")])->label(Yii::t("dashboard", "Disclaimers"))->hint("<a target='_blank' href='".$terms_url."'>".Yii::t("dashboard","View Terms and Conditions")."</a>");

                $html .= $form->field($m, 'plan_pool_id')->hiddenInput()->label("");

                $html .= Html::tag('div', Html::submitButton(Yii::t('dashboard', 'Submit'), [
                    'class' => 'btn btn-success',
                    'data' => [
                        'confirm' => Yii::t('dashboard', 'Are you sure you want to submit?'),
                        'method' => 'post',
                    ],
                ]), ['class' => 'form-group']);
                
                echo $html;
                ActiveForm::end();
            }

        ?>

    </div>

</div>

<?php

$repairCentreHours = '[]';
$repairCentreAddress = '[]';

if(!is_null($repair_centres)){
    $repairCentreHours = json_encode(ArrayHelper::map( $repair_centres , 'id', 'opening_hours'));
    $repairCentreAddress = json_encode(ArrayHelper::map( $repair_centres , 'id', 'address'));
}

$script = <<< JS

const repairCentreHours = $repairCentreHours;
const repairCentreAddress = $repairCentreAddress;

$(document).ready(function () {
    console.log(repairCentreHours)
    console.log(repairCentreAddress)
    
    $('#repair_centre').on('change', function (e) {
        var centreId = $(this).val();
        $("#repair_centre_address_wrapper").fadeOut(function(){
            var centreDetail = "Address: " + repairCentreAddress[centreId];
            centreDetail += "<br>";
            centreDetail += "Opening hours: " + repairCentreHours[centreId];
            $("#repair_centre_address").html(centreDetail);
            $("#repair_centre_address_wrapper").fadeIn();});
        });
    });

JS;
$this->registerJs($script);

?>
