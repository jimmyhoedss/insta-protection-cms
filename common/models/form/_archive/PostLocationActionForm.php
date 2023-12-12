<?php
namespace common\models\form;

use common\models\UserPostLocation;
use common\models\UserPostLocationVote;
use common\models\UserPostLocationReport;
use common\components\MyCustomBadRequestException;
use yii\base\Model;
use Yii;
use yii\web\JsExpression;

class PostLocationActionForm extends Model
{
    public $id_hash;

    public function rules()
    {
        return [
            [['id_hash'], 'required'],
            ['id_hash', 'string'],        
        ];
    }

    public function votePostLocation(){
        $user_id = Yii::$app->user->id;
        $model = UserPostLocation::find($this->id_hash)->Where(['id_hash'=>$this->id_hash])->one();
        $type = "vote";
        if ($model != null) {
            $voteModel = UserPostLocationVote::find()->andWhere(['user_id'=>$user_id, 'user_post_location_id'=>$model->id])->one();
            if ($voteModel == null) {
                $model->updateCounters(['votes' => 1]); //this is to up the vote by 1

                $m = new UserPostLocationVote();
                $m->user_id = $user_id;
                $m->user_post_location_id = $model->id;

                if (!$m->save()) {
                    $this->addError('vote', Yii::t('app', 'Error voting.'));
                     return false;
                }   
            } else {
                $model->updateCounters(['votes' => -1]); //this is to down the vote by 1
                $type = "unvote";
                if (!$voteModel->delete()) {
                    $this->addError('vote', Yii::t('app', 'Error removing vote.'));
                     return false;
                }   
            }

            $data = [];
            $data['votes'] = $model->votes;
            $data['type'] = $type;
            $data['user_post_location_id'] = $model->id;
            $data['user_id'] = $user_id;  
            return $data;                 

        } else {
            $this->addError('vote', Yii::t('app', 'Invalid id_hash.'));
            return false;
        } 
    }

    public function reportPostLocation(){
        $user_id = Yii::$app->user->id;
        $model = UserPostLocation::find($this->id_hash)->andWhere(['id_hash'=>$this->id_hash])->one();

        if ($model != null) {
            $reportModel = UserPostLocationReport::find()->andWhere(['user_id'=>$user_id, 'user_post_location_id'=>$model->id])->one();
            if ($reportModel != null) {
                $msg = "Already reported this post";
                throw new MyCustomBadRequestException(MyCustomBadRequestException::DUPLICATE_REPORT_POST, $msg);
            } else {
                $m = new UserPostLocationReport();
                $m->user_id = $user_id;
                $m->user_post_location_id = $model->id;            

                if ($m->save()) {                    
                    $data = [];
                    $data['report'] = "ok";
                    $data['created_at'] = $model->created_at;
                    return $data;
                } else {
                    $this->addError('report', Yii::t('app', 'Error reporting post.'));
                     return false;
                }   
            }
        } else {
            $this->addError('report', Yii::t('app', 'Invalid id_hash.'));
            return false;
        } 
    }

    public function deletePostLocation(){
        $user_id = Yii::$app->user->id;
        $model = UserPostLocation::find($this->id_hash)->andWhere(['id_hash'=>$this->id_hash])->one();

        if ($model != null) {
            if($model->belongsToUser($user_id)) {
                $model->updateAttributes(['status' => "disabled"]);
                return true;
            } else {
                $this->addError('delete', Yii::t('app', 'Error deleting post.'));
                return false;
            }          
        } else {
            $this->addError('delete', Yii::t('app', 'Invalid id_hash.'));
            return false;
        } 
    }

    public function attributeLabels()
    {
        return [
            'id_hash'=>Yii::t('frontend', 'id hash')
        ];
    }
}