<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\components\MyLocalization;

/**
 * This is the model class for table "instap_plan_localization".
 *
 * @property int $id
 * @property int|null $plan_id
 * @property string $language_code language-country (ISO 639-ISO 3166)
 * @property string|null $name
 * @property string|null $description
 * @property string|null $thumbnail_base_url
 * @property string|null $thumbnail_path
 * @property int|null $created_at
 * @property int|null $created_by
 * @property int|null $updated_at
 * @property int|null $updated_by
 */
class InstapPlanLocalization extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'instap_plan_localization';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['plan_id'], 'integer'],
            [['plan_id', 'name'], 'required'],
            [['name'], 'string', 'max' => 256],
            [['thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'plan_id' => 'Plan ID',
            'name' => 'Name',
            'description' => 'Description',
            'thumbnail_base_url' => 'Thumbnail Base Url',
            'thumbnail_path' => 'Thumbnail Path',
        ];
    }

    public static function makeModel($language_code, $plan) {
        // print_r($plan->attributes);
        // exit();
        $m = new SELF();
        $m->language_code = $language_code;
        $m->plan_id = $plan->id;
        $m->name = $plan->name;
        $m->description = $plan->description;
        $m->thumbnail_base_url = $plan->thumbnail_base_url;
        $m->thumbnail_path = $plan->thumbnail_path;
        return $m;
    }
}
