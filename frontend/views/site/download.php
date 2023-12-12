<?php

use common\models\SysSettings;

$link1 = SysSettings::getValue(SysSettings::BETA_DROID_LINK);
$link2 = SysSettings::getValue(SysSettings::BETA_IOS_LINK);

//$token = Yii::$app->keyStorage->get(SysSettings::ONE_MAP_TOKEN);
//$tokenPcn = Yii::$app->keyStorage->get(SysSettings::ONE_MAP_TOKEN_PCN);

?>
<h1 class="text-center"><u>Beta Testers</u></h1>
<h2 class="text-center">Click <a href="<?= $link1 ?>">here</a> to download NPARKS-C2C (Android)</h2>
<h2 class="text-center">Click <a href="<?= $link2 ?>">here</a> to download NPARKS-C2C (iOS)</h2>