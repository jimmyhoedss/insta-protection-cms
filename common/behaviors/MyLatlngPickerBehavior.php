<?php
namespace common\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use \yii\db\Expression;

/**
 * Class MyLatlngPickerBehavior
 * @package common\behaviors
 * @author Loy
 */
class MyLatlngPickerBehavior extends Behavior
{
    public $attribute = "latlng";
    public $latitudeAttribute = "latitude";
    public $longitudeAttribute = "longitude";

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }


    public function beforeSave($event)
    {
        $latitude = $this->owner->getAttribute($this->latitudeAttribute);
        $longitude = $this->owner->getAttribute($this->longitudeAttribute);
        //$this->owner->setAttribute($this->attribute, new Expression("POINT({$latitude}, {$longitude})"));
        $this->owner->setAttribute($this->attribute, new Expression("POINT({$longitude}, {$latitude})"));
        //new Expression("GeomFromText('POINT({$latitude} {$longitude})')");
        
        return true;
    }

}
