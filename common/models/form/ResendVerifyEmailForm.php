<?php
namespace common\models\form;

use common\models\User;
use common\models\SysSesTrace;
use common\models\SysUserToken;
use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use api\components\CustomHttpException;
use common\components\Utility;

class ResendVerifyEmailForm extends Model
{
    public $email;
    public $user_id;

    public function rules()
    {
        return [
            [['email', 'user_id'], 'required'],
            ['email', 'string'],        
            ['user_id', 'integer'],        
        ];
    }

    public function resendEmail(){
        $model = User::find()->andWhere(['email'=>$this->email])->andWhere(['id'=>$this->user_id])->one();
        if ($model->email_status == User::EMAIL_STATUS_NOT_VERIFIED) {
            if ($model !== null){
                $cooldown = $this->checkCooldown($model);
                if($cooldown !== true){
                    $str= Utility::jsonifyError("email", Yii::t('common', 'Try again in {token} minutes for a new email', ['token' => round(2 - $cooldown)]), CustomHttpException::KEY_WAIT_FOR_COOLDOWN);
                    throw new CustomHttpException($str,CustomHttpException::UNPROCESSABLE_ENTITY );
                }
                $sendEmail = $model->createAndSendActiviationTokenEmail();
                if($sendEmail) {
                    $emailTrace = SysSesTrace::makeModel($this->email);
                    $emailTrace->save();
                }
                return true;
            }
        } else {
            throw new CustomHttpException(Utility::jsonifyError("email", Yii::t('common',"This account is verified") , CustomHttpException::KEY_EMAIL_ALREADY_VERIFIED), CustomHttpException::UNPROCESSABLE_ENTITY);
        }
        
        return false;
    }

    private function checkCooldown($model){
        $m = SysUserToken::find()->orderBy(['created_at'=>SORT_DESC])->andWhere(['user_id'=>$model->id])->andWhere(['type'=> SysUserToken::TYPE_EMAIL_ACTIVATION])->one();
        //skip countdown if model not found
        if($m) {
            $previousCreatedAt = $m->created_at;
            $date = date_create();
            $timeDifference = (date_timestamp_get($date) - $previousCreatedAt)/60; //in mins

            if ($timeDifference >= 1){
                $m->delete();
                return true;
            } else {
                return $timeDifference;  
            } 

        } else {
            return true;
        }
    }

    public function attributeLabels()
    {
        return [
            'email'=>Yii::t('frontend', 'Email')
        ];
    }
}