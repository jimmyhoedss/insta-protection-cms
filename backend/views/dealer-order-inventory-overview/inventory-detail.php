<?php

use yii\helpers\Url;
use yii\helpers\Html;
use common\models\DealerCompanyDealer;
use common\models\DealerOrderInventory;
use common\models\DealerOrderInventoryOverview;

/* @var $this yii\web\View */
/* @var $model common\models\DealerOrderInventoryOverview */

$this->title = Yii::t('backend', 'Inventory Detail');
$this->params['breadcrumbs'][] = ['label' => Yii::t('backend', 'Inventories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$inv = $model;
$plan = $inv->plan;

$total_activated = DealerOrderInventoryOverview::getActivatedStock($inv->plan_id, $inv->dealer_company_id);
$total_unsold = DealerOrderInventoryOverview::getRemainingStock($inv->plan_id, $inv->dealer_company_id);

$downline_arr = DealerCompanyDealer::getDownlineArray($inv->dealer_company_id);
?>
<div class="" style="margin:auto; width: 50%;">
	<h2><a href="<?= Url::to(['dealer-company/view', 'id'=> $inv->dealer_company_id]) ?>"><?= $inv->dealer->business_name ?></a></h2>
	<div class="bg-yellow text-black text-bold text-center" style="margin:auto; font-size: 20px;">
		<?= $plan->name ?>
		<br>
		<?= $plan->sku ?>
	</div>
	<div style="margin:auto; border-radius: 15px; border-color: black; border-width: 1px; border-style: solid; width: 90%; margin-top: 10px;">
		<ul style="list-style: none; padding: 0;">
			<li class="row text-bold" style="color: green; margin: 0;">
				<div class="col-md-6" style="">Available Stocks (Non-Activated)</div>
				<div class="col-md-6" style=""><?= $inv->quota ?></div>
			</li>
			<li class="row" style="margin: 0;">
				<div class="col-md-6" style="">Allocated Stocks to downline(s)</div>
				<div class="col-md-6" style=""><?= DealerOrderInventoryOverview::countDownlineAllocatedStock($inv->dealer_company_id, $inv->plan_id) ?></div>
			</li>
			<li class="row" style="margin: 0;">
				<div class="col-md-6" style="">Total Activated</div>
				<div class="col-md-6" style=""><?= $total_activated ?></div>
			</li>
			<li>
				<ul>
					<li style="list-style-type: '-';">Total sold(<?= $total_activated - $total_unsold ?>)</li>
					<li style="list-style-type: '-';">Total unsold(<?= $total_unsold ?>)</li>
				</ul>
			</li>
			<li class="row" style="margin: 0; font-weight: bold;">
				<div class="col-md-6" style="">Overall</div>
				<div class="col-md-6" style=""><?= $inv->overall ?></div>
			</li>
		</ul>
	</div>
	<?php if(!empty($downline_arr)): ?>
		<hr>
		<div style="margin:auto; font-size: 20px; font-weight: bold; width: 90%;">
			Downline Company
		</div>
	<?php endif; ?>
	<?php foreach($downline_arr as $downline): ?>
		<?php 
        	$downline_company_inv = DealerOrderInventoryOverview::find()->where(['dealer_company_id' => $downline['dealer_company_downline_id']])->andWhere(['plan_id'=> $plan->id])->one();
        	if($downline_company_inv):
			$downline_company_plan = $downline_company_inv->plan;

			$downline_company_total_activated = DealerOrderInventoryOverview::getActivatedStock($downline_company_inv->plan_id, $downline_company_inv->dealer_company_id);
			$downline_company_total_unsold = DealerOrderInventoryOverview::getRemainingStock($downline_company_inv->plan_id, $downline_company_inv->dealer_company_id);
		?>
		<div style="margin:auto; border-radius: 15px; border-color: black; border-width: 1px; border-style: solid; width: 90%; margin-top: 10px;">
			<ul style="list-style: none; padding: 0;">
				<li class="row" style="margin: 0;">
					<div class="col-md-6">
						<?= $downline_company_inv->dealer->getContactSmallLayout(Url::to(['dealer-company/view', 'id'=> $downline_company_inv->dealer_company_id])) ?>
					</div>
				</li>
				<li class="row text-bold" style="color: green; margin: 0;">
					<div class="col-md-6" style="">Available Stocks (Non-Activated)</div>
					<div class="col-md-6" style=""><?= $downline_company_inv->quota ?></div>
				</li>
				<li class="row" style="margin: 0;">
					<div class="col-md-6" style="">Total Activated</div>
					<div class="col-md-6" style=""><?= $downline_company_total_activated ?></div>
				</li>
				<li>
					<ul>
						<li style="list-style-type: '-';">Total sold(<?= $downline_company_total_activated - $downline_company_total_unsold ?>)</li>
						<li style="list-style-type: '-';">Total unsold(<?= $downline_company_total_unsold ?>)</li>
					</ul>
				</li>
				<li class="row" style="margin: 0; font-weight: bold;">
					<div class="col-md-6" style="">Overall</div>
					<div class="col-md-6" style=""><?= $downline_company_inv->overall ?></div>
				</li>
			</ul>
			</ul>
		</div>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
