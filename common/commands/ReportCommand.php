<?php

namespace common\commands;

use Yii;
use yii\base\Object;
use common\models\TimelineEvent;
use trntv\bus\interfaces\BackgroundCommand;
use trntv\bus\interfaces\SelfHandlingCommand;
use trntv\bus\middlewares\BackgroundCommandTrait;

//loynote background not working in windows, haven't test on linux

class ReportCommand extends Object implements BackgroundCommand, SelfHandlingCommand
{
    use BackgroundCommandTrait;
    
    public $someImportantData;

    public function handle($command) {
        // do what you need
        // echo 'handle command';
        $m = new TimelineEvent();
        $m->application = 'application';
        $m->category = 'test';
        $m->event = 'test';
        $m->data = json_encode(['k'=>'v'], JSON_UNESCAPED_UNICODE);
        $r = $m->save(false);
        return $r;
    }
}