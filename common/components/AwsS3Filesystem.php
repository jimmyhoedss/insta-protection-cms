<?php 
namespace common\components;  // modify NS to match yours! 
use Aws\S3\S3Client; 
use creocoder\flysystem\Filesystem; 
use League\Flysystem\AwsS3v3\AwsS3Adapter; 
use yii\base\InvalidConfigException; 

class AwsS3Filesystem extends Filesystem { 
    /** * @var string */ 
    public $key; 
    /** * @var string */ 
    public $secret; 
    /** * @var string */ 
    public $region; 
    /** * @var string */ 
    public $bucket; 
    /** * @var string|null */ 
    public $prefix; 
    /** * @var string */ 
    public $version = "latest"; 
    /** * @var array */ 
    public $options = []; 
    /** * @inheritdoc */ 

    public function init() { 
        if ($this->key === null) { 
            throw new InvalidConfigException('The "key" property must be set.'); 
        } 
        if ($this->secret === null) { 
            throw new InvalidConfigException('The "secret" property must be set.'); 
        } 
        if ($this->bucket === null) {
            throw new InvalidConfigException('The "bucket" property must be set.'); 
        } 
        if ($this->region === null) { 
            throw new InvalidConfigException('The "region" property must be set.'); 
        } 
        parent::init(); 
    } 
    /** * @return AwsS3Adapter */ 
    protected function prepareAdapter() { 
        $config = [ 'credentials' => 
            [ 'key' => $this->key,  'secret' => $this->secret ], 
            'region' => $this->region, 
            'version' => $this->version,
            'http' => [ 'verify' => false ],
        ]; 
        $client = new S3Client($config);
        return new AwsS3Adapter( $client, $this->bucket, $this->prefix ); 
    } 
}


?>