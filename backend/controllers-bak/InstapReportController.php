<?php

namespace backend\controllers;

use Yii;
use common\models\InstapReport;
use common\models\User;
use common\models\UserPlan;
use common\models\UserPlanAction;
use common\models\InstapPlanPool;
use common\models\DealerOrder;
use common\components\UploadDocument;
use common\components\Utility;

use common\models\search\InstapReportSearch;
use common\models\search\InstapPlanPoolSearch;
use common\models\search\DealerOrderSearch;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii2tech\csvgrid\CsvGrid;
use yii2tech\csvgrid\SerialColumn;
use yii\data\ArrayDataProvider;

use yii\filters\AccessControl;

use yii\helpers\FileHelper;

/**
 * InstapReportController implements the CRUD actions for InstapReport model.
 */
class InstapReportController extends Controller
{


    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => true,
                       'roles' => [User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER, User::ROLE_IP_ADMIN_ASSISTANT, User::ROLE_IP_SUPER_ADMINISTRATOR],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all InstapReport models.
     * @return mixed
     */
    public function actionDeclarationReport()
    {
        $searchModel = new InstapReportSearch();
        $searchModel->setDeclarationReport();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>InstapReport::TYPE_DECLARATION_REPORT
        ]);
    }
    
    public function actionDistributorActivationReport(){
        $searchModel = new InstapReportSearch();
        $searchModel->setDistActivationReport();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>InstapReport::TYPE_DISTRIBUTOR_ACTIVATION_REPORT
        ]);
    }
    
    public function actionAmTransactionReport(){
        $searchModel = new InstapReportSearch();
        $searchModel->setAmTransactionReport();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>InstapReport::TYPE_AM_TRANSACTION_REPORT
        ]);
    }
    
    public function actionRetailTransactionReport(){
        $searchModel = new InstapReportSearch();
        $searchModel->setRetailTransactionReport();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>InstapReport::TYPE_RETAIL_TRANSACTION_REPORT
        ]);
    }
    
    public function actionSohReport(){
        $searchModel = new InstapReportSearch();
        $searchModel->setSohReport();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>InstapReport::TYPE_SOH_REPORT
        ]);
    }
    
    public function actionClaimSubmissionReport(){
        $searchModel = new InstapReportSearch();
        $searchModel->setClaimSubmissionReport();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'page'=>InstapReport::TYPE_CLAIM_SUBMISSION_REPORT
        ]);
    }

    /**
     * Displays a single InstapReport model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new InstapReport model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionReport()
    {
        $model = new InstapReport();
        $report_name = "default";
        $report_column = "";
        $view = "";
        $useDataProvider = false;
        $folder_name = null;

        if ($model->load(Yii::$app->request->post())) {
            $model->region_id = Yii::$app->session->get('region_id');
            //toDo : need to calculate from different region?
            $model->date_start = strtotime($model->date_start) - (60*60*7); //start date 12:00am
            $model->date_end = strtotime($model->date_end) + (60*60*17) - 1; //end date 23:59pm
            $report_name = $model->type;
            
            switch($model->type) {
                case InstapReport::TYPE_DECLARATION_REPORT:  
                    $report_column = InstapReport::getDeclarationColumn();
                    $plan_pool_id_arr = InstapReport::getPlanPoolIdByReportType(InstapReport::TYPE_DECLARATION_REPORT, $model->date_start, $model->date_end);
                    $searchModel = new InstapPlanPoolSearch();
                    $searchModel->setPlanController();
                    $searchModel->setDeclarationReportId($plan_pool_id_arr);
                    $useDataProvider = true;
                break;

                case InstapReport::TYPE_DISTRIBUTOR_ACTIVATION_REPORT:
                    $report_column = InstapReport::getDistributorActivationColumn();
                    $plan_pool_id_arr = InstapReport::getPlanPoolIdByReportType(InstapReport::TYPE_DISTRIBUTOR_ACTIVATION_REPORT, $model->date_start, $model->date_end);
                    $searchModel = new DealerOrderSearch();
                    $searchModel->setDistributorActivationReportId($plan_pool_id_arr);
                    $useDataProvider = true;
                break;

                case InstapReport::TYPE_AM_TRANSACTION_REPORT:
                    $useDataProvider = false;
                    $folder_name = $model->generateAmTransactionReport();
                break;
                
                case InstapReport::TYPE_RETAIL_TRANSACTION_REPORT:
                    $useDataProvider = false;
                    $folder_name = $model->generateRetailerTransactionReport();
                break;
                
                case InstapReport::TYPE_SOH_REPORT:
                    $useDataProvider = false;
                    $folder_name = $model->generateSohReport();
                break;
                
                case InstapReport::TYPE_CLAIM_SUBMISSION_REPORT:
                    $useDataProvider = false;
                    $folder_name = $model->generateClaimSubmissionReport();
                break;
            }

            if($useDataProvider){
                $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
                
                $exporter = new CsvGrid([
                    'dataProvider' => new ArrayDataProvider([
                        'allModels' => $dataProvider->getModels(),
                        'pagination' => [
                            'pageSize' => 100, // export batch size
                        ],
                    ]),
                    'columns' => $report_column,
                ]);
                //save csv to s3
                $filename = $report_name.time().'.csv';
                $successExport = $exporter->export()->saveAs('@backend/web/report/'. $filename);

                if($successExport) {
                    if($this->postDocument(Yii::getAlias('@backend/web/report/'.$filename))) {
                        $data = UploadDocument::uploadDocuments("media/report/".$report_name, "document_file", $this);
                        $model->document_base_url = $data[0]['base_url'];
                        $model->document_path = $data[0]['path'];
                        if($model->save()) {
                            FileHelper::unlink(Utility::replacePathAccordingToOS(Yii::getAlias('@backend/web/report/'.$filename)));
                            Yii::$app->session->setFlash('success', "Report generate successful, click <i class='fa fa-link'></i> to download");   
                        } else {
                            Yii::$app->session->setFlash('error', "Report generate fail");
                        }
                    }
                } else {
                    Yii::$app->session->setFlash('error', "Fail to export CSV file");
                }

            } else {
                if($folder_name){
                    $directory = '@backend/web/report/'.$model->today;
                    $zip_file_name = "report/".$model->today." ".$model->region_id."/1.zip";
                    // $zip_file_name = Yii::getAlias($directory."/1.zip");
                    // print_r($zip_file_name);print_r($folder_name); exit();
                    $successZip = static::zipDir(Utility::replacePathAccordingToOS($folder_name), Utility::replacePathAccordingToOS($zip_file_name));
                    if($successZip){
                        if($this->postDocument($zip_file_name, 'zip')) {
                            $data = UploadDocument::uploadDocuments("media/report/".$report_name, "document_file", $this);
                            $model->document_base_url = $data[0]['base_url'];
                            $model->document_path = $data[0]['path'];
                            $model->file_type = 'zip';
                            
                            if($model->save()) {
                                $unlink_result = FileHelper::removeDirectory((Yii::getAlias($directory)));
                                Yii::$app->session->setFlash('success', "Report generate successful, click <i class='fa fa-link'></i> to download. File unlink status: ".($unlink_result ? "success" :"failed"));   
                            } else {
                                Yii::$app->session->setFlash('error', "Report generate fail");
                            }
                        }
                    } else {
                        Yii::$app->session->setFlash('error', "Failed to zipped");
                    }
                } else {
                    Yii::$app->session->setFlash('error', "No folder generated");
                }
            }
            return $this->redirect(str_replace("_", "-", $model->type));
        }

        return $this->render('report', [
            'model' => $model
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->date_start = Yii::$app->formatter->asDate($model->date_start);
        $model->date_end = Yii::$app->formatter->asDate($model->date_end);
        $model->dateRange = 1; //to display date range value in form.

        if ($model->load(Yii::$app->request->post())) {
            $model->date_start = strtotime($model->date_start);
            $model->date_end = strtotime($model->date_end);
            $model->save();
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = InstapReport::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    private function postDocument($filepath, $file_type = "csv"){
        try{
            $document_file = [];
            $document_file['name'] = "1." . $file_type;
            $document_file['type'] = "application/" . $file_type;
            $document_file['tmp_name'] = Utility::replacePathAccordingToOS($filepath);
            $document_file['error'] = 0;
            $document_file['size'] = filesize(Utility::replacePathAccordingToOS($filepath));
            $_FILES['document_file'] = $document_file;
            return true;
        } catch (Exception $e){
            return false;
        }
    } 

    public static function zipDir($sourcePath, $outZipPath){
        // try {
            $pathInfo = pathInfo($sourcePath);
            $parentPath = $pathInfo['dirname'];
            $dirName = $pathInfo['basename'];
            // print_r($sourcePath);print_r($outZipPath); exit();
            
            $z = new \ZipArchive();
            $z->open($outZipPath, \ZIPARCHIVE::CREATE);
            $z->addEmptyDir($dirName);
            self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
            $z->close();
            return true;
        // } catch (\Exception $e) {
            // return false;
        // }
    }

    private static function folderToZip($folder, &$zipFile, $exclusiveLength) {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
          if ($f != '.' && $f != '..') {
            $filePath = "$folder/$f";
            // Remove prefix from file path before add to zip.
            $localPath = substr($filePath, $exclusiveLength);
            if (is_file($filePath)) {
              $zipFile->addFile($filePath, $localPath);
            } elseif (is_dir($filePath)) {
              // Add sub-directory.
              $zipFile->addEmptyDir($localPath);
              self::folderToZip($filePath, $zipFile, $exclusiveLength);
            }
          }
        }
        closedir($handle);
      }
}
