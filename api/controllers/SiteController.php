<?php
namespace api\controllers;

use Yii;
use api\behaviours\ApiAuth;
use api\behaviours\VerbCheck;
use api\components\HttpBearerAuth;
use api\components\CustomHttpException;

use common\components\Utility;
use common\models\User;
use common\models\SysSesTrace;
use common\models\SysRegion;
use common\commands\SendEmailCommand;
use common\jobs\EmailQueueJob;



/**
 * Site controller
 */

//curl -i -H "Accept:application/json" -H "Content-Type:application/json" -XPOST "http://api.tag.localhost/v1/authorize" -d '{"username": "tester1", "password": "123123"}'
//curl -H "Authorization: Bearer 3f7f570940d3c3ed80a56c63c0e0c23a" http://api.tag.localhost/v1/me

//ref: http://www.yiiframework.com/doc-2.0/guide-rest-quick-start.html
//ref: http://www.yiiframework.com/doc-2.0/guide-rest-authentication.html

class SiteController extends RestControllerBase
{
    //public $layout = '@app/views/layouts/main';
    public $layout = false;

    public function behaviors() {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'document' => ['GET'],
                    'terms' => ['GET'],
                    'privacy' => ['GET'],
                    'whatsapp-business' => ['GET'],
                ],
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::className(),
                'except' => ['index', 'document', 'terms','whatsapp-business','privacy','cache-test', 'email'],
            ],
        ]);       
    }

    public function actionIndex() {
        $o = (object) array("app"=>Yii::$app->name, "version"=>Yii::$app->params["apiVersion"]);

        Yii::$app->api->sendSuccessResponse($o);
    }

    public function actionTerms($region_id) {
        $url = "";
        $path = "/terms"; 
        // if(!is_string($region_id)) {
        //     $str =  Utility::jsonifyError("region_id", "invalid input.");
        //     throw new CustomHttpException($str, CustomHttpException::UNPROCESSABLE_ENTITY);
        // }

        if($region_id == SysRegion::SINGAPORE) {
            $path = "/terms";
        } else if ($region_id == SysRegion::MALAYSIA) {
            $path = "/my/terms";

        } else if ($region_id == SysRegion::VIETNAM) {
            $path = "vn/terms";

        } else if ($region_id == SysRegion::THAILAND) {
            $path = "th/terms";
        } else if($region_id == SysRegion::INDONESIA){
            $path = "id/terms";
        } 
        $url = Yii::$app->urlManagerFrontend->createUrl($path);
        Yii::$app->api->sendSuccessResponse($url);
    }

    public function actionPrivacy($region_id) {
        $url = "";
        $path = "/privacy"; 

        if($region_id == SysRegion::SINGAPORE) {
            $path = "/privacy";
        } else if ($region_id == SysRegion::MALAYSIA) {
            $path = "/my/privacy";

        } else if ($region_id == SysRegion::VIETNAM) {
            $path = "vn/privacy";

        } else if ($region_id == SysRegion::THAILAND) {
            $path = "th/privacy";
        } else if($region_id == SysRegion::INDONESIA){
            $path = "id/privacy";
        } 
        $url = Yii::$app->urlManagerFrontend->createUrl($path);
        Yii::$app->api->sendSuccessResponse($url);
    }

    public function actionWhatsappBusiness($region_id) {
        $url = SysRegion::getWhatsappBusinessUrl($region_id);
        Yii::$app->api->sendSuccessResponse($url);
    }


    public function actionDocument($path) {
        $signedUrl = Utility::getPreSignedS3Url($path);
        echo $signedUrl;
        return;
        $url = $signedUrl;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
        $res = curl_exec($ch);
        $rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch) ;

        //header("Content-Type: image/jpeg");
        header("Content-Type: " . $contentType);        
        echo $res;
    }

    public function actionCacheTest() {
        // $cache_key = "m";
        // $d = Yii::$app->cache->set($cache_key, "123");
        // $a = Yii::$app->cache->get($cache_key);
        // if($a) {
        //     Yii::$app->api->sendSuccessResponse($a);
        // }
	        date_default_timezone_set('Asia/Singapore');

        // $time = time();
        // $d = date("Y-m-d", mktime(0,0,0,date("n", $time),date("j",$time) -2 ,date("Y", $time)));
        // print_r($d);exit();
        print_r(strtotime("today midnight"));//start date 12:00am
        print_r("\n");
        print_r(strtotime("today midnight") + (60*60*24) - 1);//end date 23:59pm
        print_r("\n");
print_r(date("d/m",strtotime("today midnight") - (60*60*8)));
print_r("\n");
        print_r(strtotime("today -1day midnight"));
        print_r("\n");
        print_r(strtotime("today -1day midnight")+ (60*60*16) - 1);
        print_r(date("d/m", strtotime("today -1day midnight")+ (60*60*16) - 1));
        exit();

       $image = [
        'image_file' => [
            'name' => '123.jpg',
          ],
        'image_file1' => [
            'name' => '456.jpg',
          ]
        ];
        var_dump($image['image_file']);
        // print_r($image);

    }

    


    //CLOUDFRONT_URL     = d1ysynm7e5rbq.cloudfront.net
    /*
	public function actionImage($path) {
		//TODO: check if user own the image before serving

		//$url = env('API_GATEWAY') . '/image/?bucket=' . env('S3_BUCKET') . '&key=' . $path;
        $url = env('CLOUDFRONT_URL') . '/' . $path;
        //echo $url;
        //exit();

		$token = env('X_CUSTOM_AUTH');
		$headers = ["X-Custom-Auth: " . $token, "Accept-Encoding: gzip, deflate"];        


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11');
		$res = curl_exec($ch);
		$rescode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
		$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch) ;

		//header("Content-Type: image/jpeg");
		header("Content-Type: " . $contentType);		
		echo $res;
    }
    */

    public function actionEmail($email) {
       $emailStatus = Yii::$app->commandBus->handle(new SendEmailCommand([
        //$emailStatus = Yii::$app->queue->delay(0)->push(new EmailQueueJob([
            'subject' => 'loytest',
            'view' => 'loytest',
            'to' => $email,
            'params' => [],
        ]));
        print_r("Huiyo send ~ : " .$emailStatus);
        // print_r(Yii::$app->controller->id);exit();
        // print_r(Yii::$app->id);exit();
        
        // $emailTrace = SysSesTrace::makeModel($email);
        // $emailTrace->save();
        // print_r(Yii::$app->getRequest()->getUserIP());
        // User::sendTelegramBotMessage("Failed to send verification Email");
        // User::sendTelegramBotMessage(json_encode("testing"));

        // print_r($emailStatus);
    }

}
