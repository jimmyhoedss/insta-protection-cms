<?php
namespace common\components;

use Yii;
use \yii\db\Expression;
use yii\base\Model;
use yii\helpers\ArrayHelper;


class UploadDocument {

    static public function uploadDocuments($upload_path, $file_param, $file) {
        
        $uploadAction = new \trntv\filekit\actions\UploadAction("uploads",$file);
        $uploadAction->uploadPath = $upload_path;
        $uploadAction->fileparam = $file_param;
        $uploadAction->multiple = true;

        $data = [];

        $res = $uploadAction->run();
        $files = $res['files'];
        $files_count = count($files);
        for ($i=0; $i < $files_count; $i++) { 
            $path = $files[$i]['path'];
            $path =  str_replace('\\', '/', $path);                
            $temp = [
                'base_url' => $files[$i]['base_url'],
                'path' => $path
            ];
            array_push($data, $temp);
        }

        return $data;
    }



}