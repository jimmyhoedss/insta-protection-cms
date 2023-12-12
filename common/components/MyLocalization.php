<?php
namespace common\components;

use Yii;
use common\components\MyCustomActiveRecord;

class MyLocalization extends MyCustomActiveRecord {
    /*
        Country Code : https://en.wikipedia.org/wiki/ISO_3166-1
        Language Code : https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
    */
    const ENGLISH_SINGAPORE = "en";
    const ENGLISH_MALAYSIA = "en-MY";
    const ENGLISH_THIALAND = "en-TH";
    const ENGLISH_VIETNAM = "en-VN";
    const MALAY_MALAYSIA = "ms-MY";
    const THAI_THAILAND = "th-TH";
    const VIETNAMESE_VIETNAM = "vi-VN";

    public function rules()
    {
        return [
            [['created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['language_code'], 'required'],
            ['language_code', 'in', 'range' => [
                SELF::ENGLISH_SINGAPORE,
                SELF::ENGLISH_MALAYSIA,
                SELF::ENGLISH_THIALAND,
                SELF::ENGLISH_VIETNAM,
                SELF::MALAY_MALAYSIA,
                SELF::THAI_THAILAND,
                SELF::VIETNAMESE_VIETNAM
            ]],
        ];
    }

    public static function languageCodes()
    {
        return [
            SELF::ENGLISH_SINGAPORE => Yii::t('common', 'English Singapore'),
            SELF::ENGLISH_MALAYSIA => Yii::t('common', 'English Malaysia'),
            SELF::ENGLISH_THIALAND => Yii::t('common', 'English Thialand'),
            SELF::ENGLISH_VIETNAM => Yii::t('common', 'English Vietnam'),
            SELF::MALAY_MALAYSIA => Yii::t('common', 'Malay Malaysia'),
            SELF::THAI_THAILAND => Yii::t('common', 'Thai Thailand'),
            SELF::VIETNAMESE_VIETNAM => Yii::t('common', 'Vietnamese Vietnam'),
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'language_code' => 'Language Code',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

}