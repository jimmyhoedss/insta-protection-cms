<?php
namespace backend\controllers;

use Yii;
use Aws\S3\S3Client; 
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\UserToken;
use common\models\UserProfile; 
use common\models\UserActionHistory;
use common\models\SysSettings;
use common\models\fcm\SysFcmMessage;
use common\models\TimelineEvent;
use common\models\SysOAuthAccessToken;
use common\models\SysOAuthAuthorizationCode;
use common\models\form\LoginForm;
use common\models\form\AccountForm;
use common\commands\AddToTimelineCommand;
use common\commands\SendFcmCommand;
use common\components\MyCustomActiveRecord;
use common\components\keyStorage\FormModel;
use common\rbac\rule\AccessRule;


/**
 * Site controller
 */
class ToolsController extends \yii\web\Controller
{
    public $layout = "common";
    //public $layout = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        //allow this access for one map guys
                        'actions' => ['test-draw-route-pcn', 'test-point'],
                        'allow' => true,
                        //'roles' => ['@'],
                    ],
                    [
                       'allow' => true,
                       'roles' => [User::ROLE_ADMINISTRATOR],
                    ],
                ],
            ],
        ];
    }
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionTestPoint()
    {
        $this->layout = "base";
        return $this->render('test-point');
    }

    public function actionTestDrawRoute()
    {
        Yii::$app->log->targets['debug'] = null;
        $this->layout = "base";
        return $this->render('test-draw-route');
    }

    public function actionTestDrawRoutePcn()
    {
        Yii::$app->log->targets['debug'] = null;
        $this->layout = "base";
        return $this->render('test-draw-route-pcn');
    }

    public function actionTestCluster()
    {
        return $this->render('test-cluster');
    }

    public function actionTestZone()
    {
        return $this->render('test-zone');
    }
    /*
    public function actionUpdateCredits()
    {
        $debugMode = Yii::$app->keyStorage->get(SysSettings::DEBUG_MODE);
        if($debugMode == MyCustomActiveRecord::STATUS_ENABLED){
            //UserProfile::updateAll(['credit' => 30]);
            SysOAuthAuthorizationCode::deleteAll();

            Yii::$app->session->setFlash('success', "deleted");
            return $this->render('index');
        }else{
            Yii::$app->session->setFlash('error', "This only works in debug mode");
            return $this->render('index');
        }   
    }
    */

    #########################
    ##  LAUNCH EVENT 2019  ##
    #########################
    //for 30th Mar vip event starting - 0755 hrs. STATUS:DOWN -> LIVE
    /*
    public function actionLaunchEvent2019PreviewStarting(){        
        $history = TimelineEvent::find()->where(['category' => 'custom'])->andwhere(['event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_STARTING])->one();

        if ($history == null) {
            
            $transaction = Yii::$app->db->beginTransaction();
            try{
            
                //add to timeline to confirm function is called
                Yii::$app->commandBus->handle(new AddToTimelineCommand([
                    'category' => 'custom',
                    'event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_STARTING,
                    'data' => [
                        'for custom check once logic',
                        'created_at' => strtotime('now')
                    ]
                ]));

                //enable debug mode
                $debugMode = SysSettings::find()->where(['keyword' => SysSettings::DEBUG_MODE])->one();
                $debugMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_ENABLED]);

                //set popup message (prep)
                $popupMessageMessage = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP_MESSAGE])->one();
                $msg = "This is a preview of the NParks Coast-to-Coast app from now till 30 Mar 2019, 1100 hrs. Any Rewards points accumulated before 1100 hrs will be reset.";
                $popupMessageMessage->updateAttributes(['value' => $msg]);

                //enable popup message
                $popupMessageMode = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP])->one();
                $popupMessageMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_ENABLED]);

                //enable popup message banner
                $popupMessageBannerMode = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP_HAS_THUMBNAIL])->one();
                $popupMessageBannerMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_ENABLED]);

                //disable popup message link
                $popupMessageLinkMode = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP_HAS_LINK])->one();
                $popupMessageLinkMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                //disable system maintenance mode
                $sysMaintenanceMode = SysSettings::find()->where(['keyword' => SysSettings::SYSTEM_UNDER_MAINTENANCE])->one();
                $sysMaintenanceMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                
                //send FCM for start preview
                // Yii::$app->commandBus->handle(new SendFcmCommand([
                //     //'to' => SysFcmMessage::BROADCAST_ID,
                //     //EDDIE'S COMMENT: CHANGE TO BROADCAST_ID AFTER TESTING
                //     'to' => "cvLU_XKtTyc:APA91bFPexdysw2G1rk4LTtauOsA6hyjAenUvr9PQMMkPVy8Niv5KkfaZibHjhe20y_An4QPOwm0SmFAdJ7GrmW4GQORESbegJbsAep7ogVN4HDvugOFkUf4mnSymutn3x0BfMylObh4",
                //     'title' => "Annoucement",
                //     'body' => "Preview event has started!",
                //     'action' => SysFcmMessage::ACTION_INBOX,
                //     'detailed_title' => "Annoucement",
                //     'detailed_description' => "This is a preview of the NParks Coast-to-Coast app from now till 30 Mar 2019, 1100 hrs. Any Rewards points accumulated before 1100 hrs will be reset.",
                //     'link_desc' => "",
                //     'link_url' => "",
                // ]));

                $transaction->commit(); 
                Yii::$app->session->setFlash('success', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_STARTING . " - Successful, transaction committed");
                return $this->render('index');
            
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_STARTING . " - Error Exception, transaction rolled back");
                return $this->render('index');
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_STARTING . " - Error Throwable, transaction rolled back");
                return $this->render('index');
            }
        } else {
            Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_STARTING . " - Already did once");
            return $this->render('index');            
        }
    }

    //for 30th Mar vip event end patch - 1100 hrs. STATUS:LIVE -> DOWN
    public function actionLaunchEvent2019PreviewEnded(){
        $history = TimelineEvent::find()->where(['category' => 'custom'])->andwhere(['event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_ENDED])->one();

        if ($history == null) {
            
            $transaction = Yii::$app->db->beginTransaction();
            try{
            
                //add to timeline to confirm function is called
                Yii::$app->commandBus->handle(new AddToTimelineCommand([
                    'category' => 'custom',
                    'event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_ENDED,
                    'data' => [
                        'for custom check once logic',
                        'created_at' => strtotime('now')
                    ]
                ]));

                //disable debug mode
                $debugMode = SysSettings::find()->where(['keyword' => SysSettings::DEBUG_MODE])->one();
                $debugMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                //set system maintenance message (prep)
                $sysMaintenanceMessage = SysSettings::find()->where(['keyword' => SysSettings::SYSTEM_UNDER_MAINTENANCE_MESSAGE])->one();
                $msg = "The app is currently off-line as we are dressing up for the 36-hour C2C Challenge happening at 12 noon today! Are you getting ready too?";
                $sysMaintenanceMessage->updateAttributes(['value' => $msg]);

                //enable system maintenance mode
                $sysMaintenanceMode = SysSettings::find()->where(['keyword' => SysSettings::SYSTEM_UNDER_MAINTENANCE])->one();
                $sysMaintenanceMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_ENABLED]);

                //truncate UserActionHistory
                UserActionHistory::deleteAll();
                
                //truncate authorization and access codes tbl
                SysOAuthAccessToken::deleteAll();
                SysOAuthAuthorizationCode::deleteAll();

                //need to regenerate guest token for guest user to access map info
                UserToken::regenerateGuestToken();

                //send FCM for force logout
                Yii::$app->commandBus->handle(new SendFcmCommand([
                    'to' => SysFcmMessage::BROADCAST_ID,
                    'title' => "",
                    'body' => "",
                    'action' => SysFcmMessage::ACTION_FORCE_LOGOUT_SILENT
                ]));


                //log "Event Reset" action history
                $users = User::find()->all();
                foreach ($users as $user) {
                    $userActionHistory = new UserActionHistory;
                    $userActionHistory->doEventResetLog($user->id, UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_ENDED, $user->userProfile->credit);
                    $userActionHistory->save();
                }

                //reset credit to 0
                UserProfile::updateAll(['credit' => 0]);

                //enable special event mode to launch event 2019 contest
                $specialEventMode = SysSettings::find()->where(['keyword' => SysSettings::SPECIAL_EVENT_MODE])->one();
                $specialEventMode->updateAttributes(['value' => UserActionHistory::PARAM_LAUNCH_EVENT_2019_CONTEST]);

                $transaction->commit();            
                Yii::$app->session->setFlash('success', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_ENDED . " - Successful, transaction committed");
                return $this->render('index');
            
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_ENDED . " - Error Exception, transaction rolled back");
                return $this->render('index');
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_ENDED . " - Error Throwable, transaction rolled back");
                return $this->render('index');
            }
        } else {
            Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_PREVIEW_ENDED . " - Already did once");
            return $this->render('index');            
        }
        
    }

    //for 30th Mar pre phone contest patch - 1155 hrs. STATUS:DOWN -> LIVE
    public function actionLaunchEvent2019ContestStarting(){

        $history = TimelineEvent::find()->where(['category' => 'custom'])->andwhere(['event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_STARTING])->one();

        if ($history == null) {
            
            $transaction = Yii::$app->db->beginTransaction();
            try{
                //add to timeline to confirm function is called
                Yii::$app->commandBus->handle(new AddToTimelineCommand([
                    'category' => 'custom',
                    'event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_STARTING,
                    'data' => [
                        'for custom check once logic',
                        'created_at' => strtotime('now')
                    ]
                ]));

                $debugMode = SysSettings::find()->where(['keyword' => SysSettings::DEBUG_MODE])->one();
                $debugMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                //set popup message (prep)
                $popupMessageMessage = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP_MESSAGE])->one();
                $msg = "Walk your way towards a chance to win an iPhone XS or Samsung Galaxy S10+! ";
                $popupMessageMessage->updateAttributes(['value' => $msg]);

                //enable popup message banner
                $popupMessageBannerMode = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP_HAS_THUMBNAIL])->one();
                $popupMessageBannerMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_ENABLED]);

                //enable popup message link
                $popupMessageLinkMode = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP_HAS_LINK])->one();
                $popupMessageLinkMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_ENABLED]);

                //set popup message link (prep)
                $popupMessageLink = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP_LINK])->one();
                $link = "https://www.nparks.gov.sg/c2c";
                $popupMessageLink->updateAttributes(['value' => $link]);

                //enable popup message
                $popupMessageMode = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP])->one();
                $popupMessageMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_ENABLED]);

                //disable system maintenance mode
                $sysMaintenanceMode = SysSettings::find()->where(['keyword' => SysSettings::SYSTEM_UNDER_MAINTENANCE])->one();
                $sysMaintenanceMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                //send FCM for start contest
                Yii::$app->commandBus->handle(new SendFcmCommand([
                    'to' => SysFcmMessage::BROADCAST_ID,
                    //EDDIE'S COMMENT: CHANGE TO BROADCAST_ID AFTER TESTING
                    //'to' => "cpLYAHS-QGU:APA91bHkVwkxv-5pAn1NIJQY5g_8mEszJPVQ1Q3-TDYeV3YqeLaV0YyKwjLreF-hzXODG-W1jeapTarmL4F5YpPwAmkTFxaz0gAqejpyRJmIK3UDmrb4MlERmC49zv6r1I5ok078HGd0",
                    'title' => "‘Appening Right Now – The 36-Hour C2C Challenge",
                    'body' => "",
                    'action' => SysFcmMessage::ACTION_INBOX,
                    'detailed_title' => "‘Appening Right Now – The 36-Hour C2C Challenge",
                    'detailed_description' => "The 36hr C2C Challenge starts now! From now till 31 Mar 2019, 2359 hrs, get busy exploring the NParks Coast-to-Coast Trail with the app and walk your way towards a chance to win an iPhone XS or Samsung Galaxy S10+!",
                    'link_desc' => "Find out more",
                    'link_url' => "https://www.nparks.gov.sg/c2c",
                ]));  

                $transaction->commit();            
                Yii::$app->session->setFlash('success', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_STARTING . " - Successful, transaction committed.");
                return $this->render('index');
            
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_STARTING . " - Error Exception, transaction rolled back");
                return $this->render('index');
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_STARTING . " - Error Throwable, transaction rolled back");
                return $this->render('index');
            }
        } else {
            Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_STARTING . " - Already did once");
            return $this->render('index');            
        }
    }

    //for 31st Mar contest end patch - 2359 hrs. STATUS:LIVE -> DOWN
    public function actionLaunchEvent2019ContestEnded(){        

        $history = TimelineEvent::find()->where(['category' => 'custom'])->andwhere(['event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_ENDED])->one();

        if ($history == null) {
            
            $transaction = Yii::$app->db->beginTransaction();
            try{
                //add to timeline to confirm function is called
                Yii::$app->commandBus->handle(new AddToTimelineCommand([
                    'category' => 'custom',
                    'event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_ENDED,
                    'data' => [
                        'for custom check once logic',
                        'created_at' => strtotime('now')
                    ]
                ]));
                
                $debugMode = SysSettings::find()->where(['keyword' => SysSettings::DEBUG_MODE])->one();
                $debugMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                //set system maintenance message (prep)
                $sysMaintenanceMessage = SysSettings::find()->where(['keyword' => SysSettings::SYSTEM_UNDER_MAINTENANCE_MESSAGE])->one();
                $msg = "The NParks Coast-to-Coast app is offline for system updates. Check back again on 1 Apr 2019, after 0400 hrs!";
                $sysMaintenanceMessage->updateAttributes(['value' => $msg]);

                //enabled system maintenance mode
                $sysMaintenanceMode = SysSettings::find()->where(['keyword' => SysSettings::SYSTEM_UNDER_MAINTENANCE])->one();
                $sysMaintenanceMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_ENABLED]);

                //disable popup message
                $popupMessageMode = SysSettings::find()->where(['keyword' => SysSettings::APP_POPUP])->one();
                $popupMessageMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);
                
                //truncate authorization and access codes tbl
                SysOAuthAccessToken::deleteAll();
                SysOAuthAuthorizationCode::deleteAll();
                //need to regenerate guest token for guest user to access map info
                UserToken::regenerateGuestToken();

                //send FCM for force logout
                Yii::$app->commandBus->handle(new SendFcmCommand([
                    'to' => SysFcmMessage::BROADCAST_ID,
                    'title' => "",
                    'body' => "",
                    'action' => SysFcmMessage::ACTION_FORCE_LOGOUT_SILENT
                ]));

                //log "Event Reset" action history
                $users = User::find()->all();
                foreach ($users as $user) {
                    $userActionHistory = new UserActionHistory;
                    $userActionHistory->doEventResetLog($user->id, UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_ENDED, $user->userProfile->credit);
                    $userActionHistory->save();
                }

                //reset credit to 0
                UserProfile::updateAll(['credit' => 0]);

                $transaction->commit();            
                Yii::$app->session->setFlash('success', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_ENDED . " - Successful, transaction committed.");
                return $this->render('index');
            
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_ENDED . " - Error Exception, transaction rolled back");
                return $this->render('index');
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_ENDED . " - Error Throwable, transaction rolled back");
                return $this->render('index');
            }
        } else {
            Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_CONTEST_ENDED . " - Already did once");
            return $this->render('index');            
        }
    }

    //for 1st Apr launch event end patch - 0400 hrs. STATUS:DOWN -> LIVE
    public function actionLaunchEvent2019Ended(){        

        $history = TimelineEvent::find()->where(['category' => 'custom'])->andwhere(['event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_ENDED])->one();

        if ($history == null) {
            
            $transaction = Yii::$app->db->beginTransaction();
            try{
                //add to timeline to confirm function is called
                Yii::$app->commandBus->handle(new AddToTimelineCommand([
                    'category' => 'custom',
                    'event' => UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_ENDED,
                    'data' => [
                        'for custom check once logic',
                        'created_at' => strtotime('now')
                    ]
                ]));

                $debugMode = SysSettings::find()->where(['keyword' => SysSettings::DEBUG_MODE])->one();
                $debugMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                //disable special event mode
                $specialEventMode = SysSettings::find()->where(['keyword' => SysSettings::SPECIAL_EVENT_MODE])->one();
                $specialEventMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                //disable system maintenance mode
                $sysMaintenanceMode = SysSettings::find()->where(['keyword' => SysSettings::SYSTEM_UNDER_MAINTENANCE])->one();
                $sysMaintenanceMode->updateAttributes(['value' => MyCustomActiveRecord::STATUS_DISABLED]);

                //send FCM for end of launch event
                Yii::$app->commandBus->handle(new SendFcmCommand([
                    'to' => SysFcmMessage::BROADCAST_ID,
                    //EDDIE'S COMMENT: CHANGE TO BROADCAST_ID AFTER TESTING
                    //'to' => "fse4axpYRHw:APA91bHuGUNGQrZ3NdtfM2EO6ZT50J7t1Q6wesPUia1GbfmidVTnxoQuHSPnru2lGSlmksRCcmbReIvKvDmDyoRhrwJq_wnn3Jj34DYRZ-4LPA_a1EXw1k88Aks1QKNe2nt4mita_xfI",
                    'title' => "Thank You! – The 36-Hour C2C Challenge",
                    'body' => "",
                    'action' => SysFcmMessage::ACTION_INBOX,
                    'detailed_title' => "Thank You! – The 36-Hour C2C Challenge",
                    'detailed_description' => "The 36-Hour C2C Challenge event has ended. All Rewards points accumulated during the event has been reset. If you missed this event, do not fret, keep using the NParks C2C Mobile App. and stay tuned for more upcoming events!",
                    'link_desc' => "",
                    'link_url' => "",
                ]));

                $transaction->commit();            
                Yii::$app->session->setFlash('success', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_ENDED . " - Successful, transaction committed.");
                return $this->render('index');
            
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_ENDED . " - Error Exception, transaction rolled back");
                return $this->render('index');
            } catch (\Throwable $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_ENDED . " - Error Throwable, transaction rolled back");
                return $this->render('index');
            }
        } else {
            Yii::$app->session->setFlash('error', UserActionHistory::LOG_PARAM_LAUNCH_EVENT_2019_ENDED . " - Already did once");
            return $this->render('index');            
        }
    }

    /* 
    TODO:
    - Force logout, need to truncate OAUTH_ACCESS_TOKEN table
    - add new enum for "EVENT RESET" (constant in php & db)

    EDDIE'S COMMENT:
    - add check access token when auto login??


    Itinerary
    ==============
    30th vip event end and phone contest
    11am: down
    - backup db
    - off debug mode
    - set system down message (prep)
    - set system to maintenance mode    
    - send FCM for force logout
    - run db updates in transactions
    - reset credit to 0
    - log "Event Reset" action history (run once)
    - comment off prepared procedures codes in controller & view files
    - upload & test

    11:50pm: live
    - Set popup message with contest info (prep)
    - enable popup message
    - disable system maintenance mode
    - FCM all to start contest (use TimelineEvent tbl to determine send once only)

    12:10pm: remove store procedures
    - comment off all controller & view code
    - upload to "live" instance with beyond compare from stage to live 
    - create aws image



    31th office
    22:00
    - prepare message for contest end annoucement

    EDDIE'S COMMENT:
    - need to show alert to user to let them know what contest is ending in 1 hour?
    
    31th after contest
    23:59
    - backup db
    - set system to maintenance mode    
    - set system down message (prep) 
    - send FCM for force logout 
    - reset credit to 0 (run once)
    - log "Event Reset" action history (run once)
    - return reward logic to 500
    - comment off 1600 logic
    - comment off prepared procedures codes in controller & view files
    - upload & test

    04:00
    - send FCM
    - Loy to follow up
    */
    
}

