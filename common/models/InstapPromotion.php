<?php

namespace common\models;

use Yii;
use common\components\MyCustomActiveRecord;
use common\components\Utility;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "instap_promotion".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $thumbnail_base_url
 * @property string $thumbnail_path
 * @property string $status
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 */
class InstapPromotion extends MyCustomActiveRecord
{
    public static function tableName()
    {
        return 'instap_promotion';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
      return ArrayHelper::merge([
            [['title', 'description'], 'required'],
            [['description', 'status','region_id','webview_url'], 'string'],
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['title'], 'string', 'max' => 255],
            [['thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
            ['status', 'default', 'value' => MyCustomActiveRecord::STATUS_ENABLED],
        ], parent::rules());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'title' => Yii::t('common', 'Title'),
            'region_id' => Yii::t('common', 'Region id'),
            'description' => Yii::t('common', 'Description'),
            'thumbnail_base_url' => Yii::t('common', 'Thumbnail Base Url'),
            'thumbnail_path' => Yii::t('common', 'Thumbnail Path'),
            'status' => Yii::t('common', 'Status'),
            'webview_url'=>Yii::t('common', 'Hyperlink URL'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }


    public function toObject() {
        $m = $this;
        $preSignImage = "";

        $o = (object) [];
        $o->id = $m->id;
        $o->title = $m->title;
        $o->region_id = $m->region_id;
        $o->description = $m->description;
        $o->webview_url = $m->webview_url;
        //presign image url
        if(isset($m->thumbnail_path)) {
            $path = Utility::replacePath($m->thumbnail_path);
            $preSignImage = Utility::getPreSignedS3Url($path);
        }
        $o->thumbnail_presigned = $preSignImage;
        return $o;
    }

    // public function toObject() {
    //     $m = $this;
    //     $o = (object) [];
    //     $o->user_id = $m->user_id;
    //     $o->dealer_company_id = $m->dealer_company_id;
    //     return $o;
    // }

        public static function find()
    {
        return new \common\models\query\InstapPromotionQuery(get_called_class());
    }

}
