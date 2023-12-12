<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\FileStorageItem;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use Intervention\Image\ImageManagerStatic;
use trntv\filekit\events;
use common\components\MyUploadFileValidator;
use common\components\MyUploadAction;
/**
 * FileStorageController implements the CRUD actions for FileStorageItem model.
 */
class FileStorageController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'upload-delete' => ['delete']
                ]
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                       //'actions' => ['index'],
                       'allow' => true,
                       'roles' => [User::ROLE_ADMINISTRATOR, User::ROLE_IP_ADMINISTRATOR, User::ROLE_IP_MANAGER, User::ROLE_IP_ADMIN_ASSISTANT],
                    ],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
      
      return parent::beforeAction($action);
    }

    public function afterAction($action, $result) {
        $result['files'][0]['path'] = str_replace('\\', '/', $result['files'][0]['path']);
        return parent::afterAction($action, $result);
    }


    //create thumbnail with aws lambda
    //ref https://github.com/sailyapp/aws-s3-lambda-thumbnail-generator/blob/master/README.md
    public function actions()
    {
        $extRule = [['file'], 'file', 'extensions' => 'png, jpg, jpeg', 'maxSize'=>1024 * 1024 * 5];
        $extRulePdf = [['file'], 'file', 'extensions' => 'pdf', 'maxSize'=>1024 * 1024 * 15];


        return [
            'upload' => [
                'class' => MyUploadAction::class,
                //'uploadDirectory' => "media",
                'uploadPath' => "media",                
                'deleteRoute' => 'upload-delete',
                'validationRules' => [ $extRule ], 
            ],
            'upload-avatar' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                //'uploadDirectory' => "media/user",
                'uploadPath' => "media/user",
                'deleteRoute' => 'upload-delete',
                'validationRules' => [ $extRule ],
            ],
            'upload-test' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                //'uploadDirectory' => "media/test",
                'uploadPath' => "media/test",
                'deleteRoute' => 'upload-delete',
                'validationRules' => [ $extRule ],
            ],
            'upload-delete' => [
                'class' => 'trntv\filekit\actions\DeleteAction',
                'on afterDelete' => function ($event) {
                    /* @var $file \League\Flysystem\File */
                    /**/
                    $file = $event->file;
                    $fs = $event->filesystem;
                    $path = $event->path;
                    //$path = str_replace("media", "media/thumbnail", $path);
                    $path = "thumbnail/" . $path;
                    //print_r($path);
                    $fs->delete($path);
                    
                },   
            ],
            'upload-imperavi' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                'fileparam' => 'file',
                'responseUrlParam'=> 'filelink',
                'multiple' => false,
                'disableCsrf' => true
            ],
            'upload-pdf' => [
                'class' => 'trntv\filekit\actions\UploadAction',
                'uploadPath'=>'media',
                'deleteRoute' => 'delete-pdf',
                'validationRules' => [ $extRulePdf ]
               
            ],
            'delete-pdf' => [
                'class' => 'trntv\filekit\actions\DeleteAction'
            ]
        ];
    }
    public static function orientateImage($event) {
        /* @var trntv\filekit\events\BeforeUploadEvent */
        /*
        ImageManagerStatic::configure([
            'driver' => 'imagick'
        ]);
        $img->orientate();
        //$img->getCore()->stripImage(); //strip exif
        //print_r($event->uploadedFile);
        //exit();
        */
        $img = ImageManagerStatic::make($event->uploadedFile->tempName);
        $img->orientate();
        $img->save($event->uploadedFile->tempName);
    }
    public static function createThumbnail($event) {
        /* @var $file \League\Flysystem\File */
        /**/
        $file = $event->file;
        $img = ImageManagerStatic::make($file->read())->fit(600, 400);
        //$file->put($img->encode());
        //loyhack fuck shit
        //print_r($file->getMimetype());
        $mimeType = $file->getMimetype();
        $fs = $event->filesystem;
        $path = $event->path;
        $size = $file->getSize();
        //$path = str_replace("media", "media/thumbnail", $path);
        $path = "thumbnail/" . $path;
        //print_r($path);
        $fs->put($path, $img->encode(), ['ContentType' => $mimeType, 'ContentLength' => $size]);
    }

    public static function createCircleThumbnail($event) {
        /* @var $file \League\Flysystem\File */
        /**/
        $width = $height = 256;

        $file = $event->file;
        $img = ImageManagerStatic::make($file->read())->fit($width, $height);
        $img->encode('png');
        $mimeType = "image/png";
        
        $mask = ImageManagerStatic::canvas($width, $height);
        $mask->circle($width, $width/2, $height/2, function ($draw) {
            $draw->background('#fff');
        });
        $img->mask($mask, false);

        $fs = $event->filesystem;
        $path = $event->path;
        $size = $file->getSize();
        $path = "circle/" . $path;
        $fs->put($path, $img->encode(), ['ContentType' => $mimeType, 'ContentLength' => $size]);
    }
    public static function createRoundedCornerThumbnail($event) {
        /* @var $file \League\Flysystem\File */
        /**/
        $width = $height = 256;

        $file = $event->file;
        $img = ImageManagerStatic::make($file->read())->fit($width, $height);
        $img->encode('png');
        $mimeType = "image/png";
        
        $path = Yii::getAlias('@backend/web/img/rounded-corner.png');
        $contents = file_get_contents($path);
        $mask = ImageManagerStatic::make($contents)->fit($width, $height);
        $img->mask($mask, false);

        $fs = $event->filesystem;
        $path = $event->path;
        $size = $file->getSize();
        $path = "rounded_corner/" . $path;
        $fs->put($path, $img->encode(), ['ContentType' => $mimeType, 'ContentLength' => $size]);
    }
    /**
     * Displays a single FileStorageItem model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id)
        ]);
    }

    /**
     * Deletes an existing FileStorageItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the FileStorageItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FileStorageItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FileStorageItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
