<?php
namespace common\components\filesystem;

use Yii;
use yii\base\BaseObject;
use Aws\S3\S3Client;
use Aws\Exception\InvalidRegionException;
use Aws\AppConfig\Exception\AppConfigException;

class MyS3Client extends BaseObject
{
    public $client;

    public $key;
    public $secret;
    public $region;
    public $bucket;

    public $cmd;

    public function init()
    {
      try{
          $this->client = new S3Client([
            'credentials' => array(
                  'key' => $this->key,
                  'secret'  => $this->secret,
              ),
            'region' => $this->region,
            'version' => 'latest',
          ]);
      } catch (InvalidRegionException $e) {
        Yii::error($e->getMessage(), 'MyS3Client');
      }catch (\Exception $e) {
        Yii::error($e->getMessage(), 'MyS3Client');
      }
      return $this;
    }


    public function getPreSignedS3Url($path) {

      if (empty($path)) {
          return null;
      }
      $this->cmd = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $path,
        ]);
      $request = $this->client->createPresignedRequest($this->cmd, '+10 minutes');
      $signedUrl = (string) $request->getUri();

      return $signedUrl;
    }
}

