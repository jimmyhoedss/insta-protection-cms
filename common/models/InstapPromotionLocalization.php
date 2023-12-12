<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\components\MyLocalization;

/**
 * This is the model class for table "instap_promotion_localization".
 *
 * @property int $id
 * @property int|null $promotion_id
 * @property string $language_code language-country (ISO 639-ISO 3166)
 * @property string|null $title
 * @property string|null $description
 * @property string|null $thumbnail_base_url
 * @property string|null $thumbnail_path
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class InstapPromotionLocalization extends MyLocalization
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'instap_promotion_localization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return ArrayHelper::merge([
            [['promotion_id'], 'integer'],
            [['promotion_id', 'title'], 'required'],
            [['title'], 'string', 'max' => 256],
            [['thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
        ], parent::rules());
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge([
            'promotion_id' => 'Promotion ID',
            'title' => 'Title',
            'description' => 'Description',
            'thumbnail_base_url' => 'Thumbnail Base Url',
            'thumbnail_path' => 'Thumbnail Path',
        ], parent::attributeLabels());
    }

    public static function makeModel($language_code, $promotion) {
        $m = new SELF();
        $m->language_code = $language_code;
        $m->promotion_id = $promotion->id;
        $m->title = $promotion->title;
        $m->description = $promotion->description;
        $m->thumbnail_base_url = $promotion->thumbnail_base_url;
        $m->thumbnail_path = $promotion->thumbnail_path;
        return $m;
    }
}
