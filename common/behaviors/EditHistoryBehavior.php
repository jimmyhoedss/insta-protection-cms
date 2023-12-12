<?php
namespace common\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use common\models\UserPlanDetailEditHistory;
use common\components\MyCustomActiveRecord;
use common\components\MyCustomActiveRecordQuery;
use Exception;

/**
 * Class MyLatlngPickerBehavior
 * @package common\behaviors
 * @author Loy
 */
class EditHistoryBehavior extends Behavior
{
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }   
    public function afterInsert($event)
    {
        $this->leaveTrail();
    }
    public function afterUpdate($event)
    {
        $this->leaveTrail();
    }
    public function afterDelete($event)
    {
        $this->leaveTrail();
    }

    

    public function leaveTrail() {
        //echo "leaveTrail";


        $m = new UserPlanDetailEditHistory();

        $id = isset($this->owner->id) ? $this->owner->id : -1;

        // $m->row_id = $id;
        $m->row_id = $this->owner->attributes['plan_pool_id']; //store plan pool id
        $m->model = $this->owner->className();
        $m->controller = Yii::$app->controller->id;
        $m->action = Yii::$app->controller->action->id;
        $m->value = json_encode($this->owner->attributes);
        $m->created_at = time();

        $user = Yii::$app->get('user', false);
        $m->created_by = $user && !$user->isGuest ? $user->id : null;

        return $m->save(false);
    }

}
