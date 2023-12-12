<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\web\View;
use yii\helpers\Url;

$this->title = "Admin Dashboard";
$this->params['subtitle'] = "v 1.0";
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-backend-index">

    <div class="row">
        <ul>
            <li>show overview of top plans sold (eg. top 3 plans)</li>>
            <li>show overview of top dealer company (eg. top 3 company)</li>>
            <li>show overview of top associate (eg. top 3 sales associate)</li>>
            <li>show number of register users</li>
            <li>show number of claims</li>
            <li>show dealers with low inventory</li>
        </ul>

        <?php 
            // echo MyInfoBox::widget(['style'=>'bg-blue', 'type'=>MyInfoBox::INFO_TYPE_USER]);
            // echo MyInfoBox::widget(['style'=>'bg-teal', 'type'=>MyInfoBox::INFO_TYPE_USER_ACTIVE]);
            // // echo MyInfoBox::widget(['style'=>'bg-green', 'type'=>MyInfoBox::INFO_TYPE_USER_SUSPICIOUS]);
            // echo MyInfoBox::widget(['style'=>'bg-lime', 'type'=>MyInfoBox::INFO_TYPE_USER_POST]);
            // // echo MyInfoBox::widget(['style'=>'bg-yellow', 'type'=>MyInfoBox::INFO_TYPE_NEW_FEEDBACK]);
            // echo MyInfoBox::widget(['style'=>'bg-fuchsia', 'type'=>MyInfoBox::INFO_TYPE_REWARDS_ALLOCATED]);
            // echo MyInfoBox::widget(['style'=>'bg-maroon', 'type'=>MyInfoBox::INFO_TYPE_REWARDS_REMAINING]);
            // echo MyInfoBox::widget(['style'=>'bg-purple', 'type'=>MyInfoBox::INFO_TYPE_REWARDS_REDEEMED]);
        ?>

    </div>

</div>



<?php