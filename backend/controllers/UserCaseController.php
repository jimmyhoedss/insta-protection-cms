<?php

namespace backend\controllers;

use Yii;
use common\models\UserCase;
use common\models\SysRegion;
use common\models\UserCaseAction;
use common\models\UserCaseActionLog;
use common\models\InstapPlanPool;
use common\models\UserCaseActionDocument;
use common\models\UserCaseRepairCentre;
use common\models\search\UserCaseSearch;
use common\models\search\UserCaseActionSearch;
use common\models\form\ClaimActionForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\fcm\FcmCaseStatusChanged;
use kartik\mpdf\Pdf;
use common\components\Utility;
use common\jobs\EmailQueueJob;

/**
 * UserCaseController implements the CRUD actions for UserCase model.
 */
class UserCaseController extends Controller
{
    public $filename = "";
    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new UserCaseSearch();
        $searchModel->setClaimActive();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"index"
        ]);
    }

    public function actionClaimReject()
    {
        $searchModel = new UserCaseSearch();
        $searchModel->setClaimReject();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"claim_reject"
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionUpdate($id) {
        //TODO:: Prevent form resubmission from refreshing page.
        // print_r(Yii::$app->params['carbonCopyEmailList']['IP_ADMIN_EMAIL']);exit();
        $model = $this->findModel($id);
        $searchModel = new UserCaseActionSearch();
        $searchModel->setCaseId($id);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $pool = $model->planPool;

        $modelCase = new UserCase();
        $modelAction = new UserCaseAction();
        $claimActionForm = new ClaimActionForm();

        if ($modelAction->load(Yii::$app->request->post()) && $modelCase->load(Yii::$app->request->post())  && $claimActionForm->load(Yii::$app->request->post())) {

            $transaction = Yii::$app->db->beginTransaction();
            $success = false;
            try{
                $modelAction->case_id = $model->id;
                //check plan status not null for dropdown select
                if($modelAction->action_status === null) {
                    Yii::$app->session->setFlash('error', "Case status cannot be empty, please select a status before update");
                    return $this->redirect(['update', 'id' => $id]);
                }

                if(in_array($modelAction->action_status, UserCase::statusNotReject())) {
                    $exist = UserCase::find()->andWhere(['in', 'current_case_status', UserCase::statusNotReject()])->andWhere(['not in', 'id', $model->id])->andWhere(['plan_pool_id' => $model->plan_pool_id])->all();
                    if (!empty($exist)) {
                        Yii::$app->session->setFlash('error', "There is another activated claim, please ensure there only one claim for each policy");
                        return $this->redirect(['update', 'id' => $id]);
                    }
                }

                $caseStatus = UserCase::mapActionToStatus()[$modelAction->action_status];
                $actionLog = UserCaseActionLog::makeModel($model, $modelAction->action_status);
                $model->current_case_status = $caseStatus;
                //update plan pool status when action_status is closed
                $pool->plan_status = UserCase::mapCaseActionToPlanStatus()[$caseStatus];
                //save into notes(user)
                if($modelAction->action_status == UserCaseAction::ACTION_CLAIM_CLOSED) {
                    if(empty($claimActionForm->flag_skip_doc)){
                        $model->cost_repair = $modelAction->notes_user = $modelCase->cost_repair;
                    } else {
                        $model->cost_repair = $modelCase->cost_repair = 0;
                        $modelAction->notes_user = "out-of-coverage cases";
                    }
                }

                if ($modelAction->save() && $model->save() && $pool->save() && $actionLog->save()) {
                    if($modelAction->action_status == UserCaseAction::ACTION_CLAIM_PROCESSING) {
                        $incidentReport = $this->generateIncidentReport($pool->id);
                        UserCaseActionDocument::uploadDocument($modelAction, $incidentReport, UserCaseActionDocument::TYPE_INCIDENT_REPORT);
                    }
                    //upload document accordingly if checkbox not checked
                    if($modelAction->action_status == UserCaseAction::ACTION_CLAIM_CLOSED && empty($claimActionForm->flag_skip_doc)) {
                        $claimActionForm->scenario = ClaimActionForm::SCENARIO_CLAIM_CLOSE;
                        UserCaseActionDocument::uploadDocument($modelAction,  $claimActionForm->quotation,UserCaseActionDocument::TYPE_QUOTATION);
                        UserCaseActionDocument::uploadDocument($modelAction,  $claimActionForm->photo_pre,UserCaseActionDocument::TYPE_PRE);
                        UserCaseActionDocument::uploadDocument($modelAction,  $claimActionForm->photo_post,UserCaseActionDocument::TYPE_POST);
                        UserCaseActionDocument::uploadDocument($modelAction,  $claimActionForm->service_report,UserCaseActionDocument::TYPE_SERVICE_REPORT);
                        UserCaseActionDocument::uploadDocument($modelAction,  $claimActionForm->discharge_voucher,UserCaseActionDocument::TYPE_DISCHARGE_VOUCHER);
                    }

                    if($claimActionForm->validate() && !$modelAction->hasErrors() && !$model->hasErrors() && !$pool->hasErrors() && !$actionLog->hasErrors()) {
                        $success = true;
                    }

                }
            } catch (yii\db\IntegrityException $e) {
                $success = false;                
                $str = Utility::jsonifyError("", "database error", CustomHttpException::KEY_UNEXPECTED_ERROR);
                throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
            }


            if ($success) {
                $transaction->commit();
                $fcm = new FcmCaseStatusChanged($model);
                $fcm->send();
                if ($modelAction->action_status == UserCaseAction::ACTION_CLAIM_REQUIRE_CLARIFICATION) {
                    //ToDo: sent email to user when case status is require clarification
                    //get language session and change the email language accordingly
                    Yii::$app->queue->delay(0)->push(new EmailQueueJob([
                        'subject' => Yii::t('frontend', '[InstaProtection] Submitted Claim Require Clarification'),
                        'view' => 'claimRequireClarification',
                        'language' => SysRegion::mapCountryToNativeLanguage($pool->user->region_id),
                        'to' => $pool->user->email,
                        'params' => [
                            'user' => 'Sir/ Madam',
                        ]
                    ]));
                } else {
                    Yii::$app->queue->delay(0)->push(new EmailQueueJob([
                        'subject' => Yii::t('frontend', '[InstaProtection] Claim Status {0}', [UserCase::allCaseStatus()[$model->current_case_status]]),
                        'view' => 'claimStatusChanged',
                        'language' => SysRegion::mapCountryToNativeLanguage($pool->user->region_id),
                        'to' => $pool->user->email,
                        'params' => [
                            'current_case_status' => $model->current_case_status,
                            'policy_number' => $model->planPool->policy_number,
                        ]
                    ]));
                }
                //sent email to repair centre.
                if ($modelAction->action_status == UserCaseAction::ACTION_CLAIM_PROCESSING) {
                    //get user repair center
                    $user_repair_centre = UserCaseRepairCentre::find()->where(['case_id' => $model->id])->one();
                    //merge email
                    $email_cc = array_merge(Yii::$app->params['carbonCopyEmailList']['IP_ADMIN_EMAIL'], Yii::$app->params['carbonCopyEmailList'][Yii::$app->session->get('region_id')]);

                     Yii::$app->queue->delay(0)->push(new EmailQueueJob([
                        'subject' => Yii::t('frontend', 'New Service Request from InstaProtection Claim ID {0}', [UserCase::formUpClaimNumber($model)]),
                        'view' => 'claimSubmitToRepairCentre',
                        'language' => SysRegion::mapCountryToNativeLanguage($pool->user->region_id),
                        'to' => $user_repair_centre->repairCentre->email,
                        'cc' => $email_cc,
                        'params' => [
                            'repair_centre' => $user_repair_centre->repairCentre->repair_centre,
                            'claim_id' => UserCase::formUpClaimNumber($model),
                            'user_name' => $pool->user->userProfile->first_name." ".$pool->user->userProfile->last_name,
                            'device_model' => $pool->userPlan->details->sp_model_number,
                            'device_imei' => $pool->userPlan->details->sp_imei,
                            'device_serial' => $pool->userPlan->details->sp_serial,
                            'plan_name' => $pool->plan->name,
                        ]
                    ]));
                }

                $modelAction = new UserCaseAction();
                $modelCase = new UserCase();
                Yii::$app->session->setFlash('success', "Case status update successful!");
                return $this->redirect(Yii::$app->request->referrer); //prevent resubmit by clicking refresh btn
            } else {  
                $transaction->rollback();
                $msg = "";
                if ($modelAction->hasErrors()){
                    //oh: set current_case_status to empty to prevent disappear of dropdown select when error occur
                    $model->current_case_status = "";
                    foreach ($modelAction->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                if($model->hasErrors()){
                    foreach ($model->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                if($pool->hasErrors()) {
                    foreach ($pool->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                if($claimActionForm->hasErrors()) {
                    foreach ($claimActionForm->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                if($actionLog->hasErrors()) {
                    foreach ($actionLog->getFirstErrors() as $name => $error) {
                        $msg .= $error . "<br>";
                    }
                }
                Yii::$app->session->setFlash('error', $msg);
            }
            
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_HTML;
        return $this->render('update', [
            'model' => $model,
            'modelAction' => $modelAction,
            'claimActionForm' => $claimActionForm,
            'modelCase' => $modelCase,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    protected function findModel($id)
    {
        if (($model = UserCase::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function generateIncidentReport($id)
    {
        $this->filename = 'tmp/incident_report'.time().'-'.$id.'.pdf';
        // Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $pdf = new Pdf([
            'mode' => Pdf::MODE_UTF8, // leaner size using standard fonts
            'filename'=>$this->filename,
            'destination' => Pdf::DEST_FILE,
            'content' => $this->renderPartial('/instap-report/report_incident', ['id'=> $id]),
            'options' => [
            'showImageErrors' => true,
                // any mpdf options you wish to set

            ],
            'methods' => [
                'SetTitle' => 'Incident Report',
                // 'SetSubject' => 'Activations by care plans',
                // 'SetHeader' => ['•  No. of Activations by care plans on a daily basis by respective partners<br>Generated On: ' . date("r")],
                // 'SetFooter' => ['|Page {PAGENO}|'],
                'SetAuthor' => 'Instaprotection',
                'SetCreator' => 'Instaprotection',
                'SetKeywords' => 'instaprotection, instaprotect, protection, ip, care plan',
            ]
        ]);
        $pdf->render();
        $this->postDocument();
        $data = $this->upload();
        // $presignedUrl = Utility::getPreSignedS3Url($data[0]['path']);
        // print_r(urldecode($presignedUrl));
        // exit();
        unlink(Utility::replacePathAccordingToOS(Yii::getAlias('@backend/web/'.$this->filename)));
        return $data;
    }

    private function postDocument(){
        try{
            $filepath = Yii::getAlias('@backend/web/'.$this->filename);
            $document_file = [];
            $document_file['name'] = "1.pdf";
            $document_file['type'] = "application/pdf";
            $document_file['tmp_name'] = Utility::replacePathAccordingToOS($filepath);
            $document_file['error'] = 0;
            $document_file['size'] = filesize(Utility::replacePathAccordingToOS($filepath));
            $_FILES['document_file'] = $document_file;
            return true;
        } catch (Exception $e){
            return false;
        }
    } 

    private function upload() {
        $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$this);
        $uploadAction->uploadPath = "media/case/claim_processing/incident_report";
        $uploadAction->fileparam = "document_file";

        $data = [];

        $res = $uploadAction->run();
        $files = $res['files'];
        $files_count = count($files);
        for ($i=0; $i < $files_count; $i++) { 
            $path = $files[$i]['path'];
            $path =  str_replace('\\', '/', $path);                
            $temp = [
                'base_url' => $files[$i]['base_url'],
                'path' => $path
            ];
            array_push($data, $temp);
        }

        return $data;
    }

    public function actionClaimPending(){
        $searchModel = new UserCaseSearch();
        $searchModel->setClaimPending();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>"claim_pending"
        ]);
    }
}
