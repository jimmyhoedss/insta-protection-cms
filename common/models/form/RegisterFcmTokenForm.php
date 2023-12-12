<?php
namespace common\models\form;

use common\models\User;
use common\models\SysFcmTokenHistory;
use common\commands\SendFcmCommand;
use common\models\fcm\SysFcmMessage;
use common\models\fcm\FcmCustomSystem;

use yii\base\Model;
use Yii;
use yii\web\JsExpression;

class RegisterFcmTokenForm extends Model
{
    public $token;

    public function rules()
    {
        return [
            [['token'], 'required'],
            ['token', 'string'],        
        ];
    }

    public function attributeLabels(){
        return [
            'token'=>Yii::t('common', 'Token')
        ];
    }

    public function registerFcmToken() {
        $user = Yii::$app->user->identity;

        if ($user != null) {
            $currentToken = $user->fcm_token;            
            if ($currentToken != null && $currentToken != $this->token){
                //forcelogout if user's current token is not same as the new token
                //SELF::sendForceLogoutFcm($user->id);
            }

            $user->updateAttributes(['fcm_token' => $this->token]);

            $exist = SysFcmTokenHistory::find()->Where(['user_id'=>$user->id])->andWhere(['token'=>$this->token])->one();
            if ($exist == null) {
                $history = SysFcmTokenHistory::makeModel($user->id , $this->token);
                $history->save();
            }
            return true;           
            
        } else {
            $this->addError('token', Yii::t('common', 'No such user.'));
            return false;
        }
    }

/*
    public function registerFcmToken(){
        $user = Yii::$app->user->identity;

        if ($user != null) {
            //forcelogout if user's current token is not same as the new token
            if (SELF::isCurrentTokenNotEqualNewToken($user->fcm_token, $this->token)){
                SELF::sendForceLogoutFcm($user->id);

                if (SELF::isUserSuspicious($user->id, $this->token)) {
                    $user->triggerSuspiciousFlag($user->id);
                }
            }

            $user->updateAttributes(['fcm_token' => $this->token]);
            $sameToken = SysFcmTokenHistory::find()->Where(['user_id'=>$user->id])->andWhere(['token'=>$this->token])->one();
            if ($sameToken == null) {
                $tokenHistory = new SysFcmTokenHistory();
                $tokenHistory->user_id = $user->id;
                $tokenHistory->token = $this->token;
                if ($tokenHistory->save()){
                    return true;
                } else {
                    $this->addError('token', Yii::t('app', 'Error saving FCM token history.'));
                    return false;
                }
            } else {
                //registered same token before
                Yii::$app->api->sendSuccessResponse("[token] FCM token already registered previously"); //for repeated upload of same user & same device
            }
            
        } else {
            $this->addError('token', Yii::t('app', 'No such user.'));
            return false;
        }
    }

    

    private function isCurrentTokenNotEqualNewToken($currentToken, $newToken){
        return $currentToken != null && $currentToken != $newToken;
    }

    private function isUserSuspicious($user_id, $token){
        $checkUser = SysFcmTokenHistory::find()->Where(['user_id'=>$user_id])->all();
        $numberOfFcm = count($checkUser);

        if ($numberOfFcm >= 3) {
            return true;
        }
        return false;
    }

    private function sendForceLogoutFcm($user_id) {
        $fcmModel = new FcmCustomSystem();
        $fcmValues = [
            'title' => "System Message",
            'body' => "This account has been logged in from another device and will be logged out from this device",
            'action' => SysFcmMessage::ACTION_FORCE_LOGOUT
        ];
        $fcmModel->customLoad($user_id, $fcmValues);
        $fcmModel->send();
    }
*/
}