<?php
  namespace common\components;

  use common\components\Utility;
  use common\components\MyCustomActiveRecord;  
  use common\models\SysSettings;
  use yii\validators\Validator;

  class WithinC2cBorderValidator extends Validator
  {
      public function validateAttribute($model, $attribute)
      {

    
          $debug_mode = SysSettings::getValue(SysSettings::DEBUG_MODE) == MyCustomActiveRecord::STATUS_ENABLED;
          if ($debug_mode) {
          //if (false) {
              return true;
          }
          if (!Utility::isWithinC2c($model->latitude, $model->longitude) ) {
              $this->addError($model, 'latitude', 'longlat is not within c2c.');
              $this->addError($model, 'longitude', 'longlat is not within c2c.');
          }

      }
  }
    
?>