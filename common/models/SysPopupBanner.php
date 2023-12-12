<?php

namespace common\models;
use yii\helpers\ArrayHelper;
use common\models\query\SysPopupBannerQuery;
use common\components\MyCustomActiveRecord;

use Yii;

/**
 * This is the model class for table "sys_popup_banner".
 *
 * @property int $id
 * @property string $thumbnail_path
 * @property string $thumbnail_base_url
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 * @property int $updated_by
 */
class SysPopupBanner extends MyCustomActiveRecord
{
    public function init() {
        parent::init();
        $this->detachBehavior('latlngPicker');   
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sys_popup_banner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        //important to merge parent rules for upload rules
        return ArrayHelper::merge([
            [['id', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['thumbnail_path', 'thumbnail_base_url', 'status'], 'string', 'max' => 1024],
            [['id'], 'unique'],
        ], parent::rules());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'thumbnail_path' => 'Thumbnail Path',
            'thumbnail_base_url' => 'Thumbnail Base Url',
            'status' => 'Status',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * {@inheritdoc}
     * @return SysPopupBannerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SysPopupBannerQuery(get_called_class());
    }
}
