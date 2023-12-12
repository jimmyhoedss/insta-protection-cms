<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\UserReward */

$this->title = 'Create User Reward';
$this->params['breadcrumbs'][] = ['label' => 'User Rewards', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-reward-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
