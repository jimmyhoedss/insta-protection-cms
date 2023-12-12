<?php
namespace common\components\filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use trntv\filekit\filesystem\FilesystemBuilderInterface;
use yii\base\BaseObject;
use Aws\S3\S3Client;

class AwsS3v3FlysystemBuilder extends BaseObject implements FilesystemBuilderInterface
{
    public $key;
    public $secret;
    public $region;
    public $bucket;

    public function build()
    {
        $client = new S3Client([
            'credentials' => [
                'key'    => $this->key,
                'secret' => $this->secret
            ],
            'region' => $this->region,
            'version' => 'latest',
        ]);


        $adapter = new AwsS3Adapter($client, $this->bucket);
        $filesystem = new Filesystem($adapter);

        return $filesystem;
    }
}

