<?php

namespace frontend\controllers;

use cheatsheet\Time;
use common\sitemap\UrlsIterator;
use frontend\models\ContactForm;
use common\models\UserPlanDetail;
use common\models\InstapPlanPool;
use Sitemaped\Element\Urlset\Urlset;
use Sitemaped\Sitemap;
use Yii;
use yii\filters\PageCache;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\web\View;


/**
 * Site controller
 */
class SiteController extends Controller
{
    public $layout = '@app/views/layouts/default';
    
    public function behaviors()
    {
        return [
            [
                'class' => PageCache::class,
                'only' => ['sitemap'],
                'duration' => Time::SECONDS_IN_AN_HOUR,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        //Yii::$app->language = "vn-VN";
        return $this->render('app');
    }

    public function actionApp()
    {
        //$this->layout = false;
        return $this->render('app');
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
    
    public function actionActivate()
    {
        return $this->render('activate');
    }

    public function actionActivateWebcam()
    {
        $this->getView()->registerJsVar("_text_activate", Yii::t("dashboard", 'No QR code detected.'), View::POS_BEGIN);
        return $this->render('activate-webcam');
    }

    public function actionFeedback($name = null, $email = null)
    {
        $model = new FeedbackForm();
        $model->name = $name;
        $model->email = $email;

        if ($model->load(Yii::$app->request->post()) && $model->submitFeedback()) {
            return $this->render("thank-you", ["msg"=>"Your feedback has been successfully submitted <span class='nobr'>to the system.</span>"]);
        }

        return $this->render('feedback', [
            'model' => $model
        ]);
    }
    public function actionLanguage()
    {
        //$this->layout = false;
        return $this->render('language');
    }

    public function actionTerms()
    {
        $this->layout = '@app/views/layouts/default';
        $lang = Yii::$app->language;
        // print_r($lang);exit();
        $view = "terms";
        // if ($lang == "zh-MY") {
        //     $view = "terms/term_my";
        // } else if($lang == "th-TH") {
        //     $view = "terms/term_my";
        // } else {
        //     $view = "terms/term_sg";
        // }
        return $this->render("terms");
    }

 
    public function actionPrivacy()
    {
        $lang = Yii::$app->language; //differentiate also
        $this->layout = '@app/views/layouts/default';
        return $this->render('privacy');
    }

    public function actionCredits() {
        $this->layout = '@app/views/layouts/default';
        return $this->render('credits');
    }

    public function actionFaqs() {
        return $this->render('faqs');
    }

    public function actionClear() {
        Yii::$app->cache->flush();
        return "cleared database cache";
    }

    public function actionImei() {
        $msg = "";
        $all_pool_id = [];
        $model = new UserPlanDetail();
        if ($model->load(Yii::$app->request->post())) {
            $all_imei = UserPlanDetail::find()->andWhere(['sp_imei' => $model->sp_imei])->all();
            if($all_imei != null) {
                foreach ($all_imei as $key => $value) {
                    array_push($all_pool_id, $value["plan_pool_id"]); 
                }
                $msg = "Your imei has covered by these plan: ";
            }else {
                $msg = "Imei not registered for any plan!";
            }         
        }
        $plan_pools = InstapPlanPool::find()->where(['in', 'id', $all_pool_id])->all();
        if($plan_pools) {
            foreach ($plan_pools as $key => $value) {
                $msg .= $value->plan->name. ", ";
            }
        }
        $msg = rtrim($msg, ", ");

        return $this->render('imei', [
            'msg' => $msg,
            'model' => $model
        ]);
    }

}
