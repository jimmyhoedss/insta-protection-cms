<?php

namespace common\models\fcm;

use Yii;
use common\models\SysSendMessageError;

class SysFcmMessageError extends SysSendMessageError
{
    public function logError($category, $recipient, $param1, $param2)
    {
        $this->type = SysFcmMessageError::TYPE_FCM;
        $this->category = $category;
        $this->recipient = $recipient;
        $this->param1 = $param1;
        $this->param2 = $param2;

        if (!$this->save()) {
            echo('Error logging.');
        }
        // throw new BadRequestHttpException($e);
    }
}