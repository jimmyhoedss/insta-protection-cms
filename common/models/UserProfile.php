<?php

namespace common\models;

use Yii;
use common\models\User;
use common\models\SysRegion;
use yii\helpers\Url;
use common\components\Utility;


class UserProfile extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'user_profile';
    }
    public function rules()
    {
        return [
            [['write_up'], 'string'],
            [['gender', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'integer'],
            [['title', 'first_name', 'last_name', 'birthday'], 'string', 'max' => 45],
            [['avatar_path', 'avatar_base_url'], 'string', 'max' => 1024],
            [['address1', 'address2', 'address3'], 'string', 'max' => 255],
            [['first_name', 'last_name'], 'filter', 'filter' => '\yii\helpers\HtmlPurifier::process'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('common', 'User ID'),
            'title' => Yii::t('common', 'Title'),
            'first_name' => Yii::t('common', 'First Name'),
            'last_name' => Yii::t('common', 'Last Name'),
            'write_up' => Yii::t('common', 'Write Up'),
            'avatar_path' => Yii::t('common', 'Avatar Path'),
            'avatar_base_url' => Yii::t('common', 'Avatar Base Url'),
            'gender' => Yii::t('common', 'Gender'),
            'address1' => Yii::t('common', 'Address1'),
            'address2' => Yii::t('common', 'Address2'),
            'address3' => Yii::t('common', 'Address3'),
            'birthday' => Yii::t('common', 'Birthday'),
            'created_at' => Yii::t('common', 'Created At'),
            'created_by' => Yii::t('common', 'Created By'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'updated_by' => Yii::t('common', 'Updated By'),
        ];
    }
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    public function getFullName()
    {
        if ($this->first_name || $this->last_name) {
            return implode(' ', [$this->first_name, $this->last_name]);
        }
        return null;
    }
    public function getAvatar($default = null) 
    {
        if ($default == null) {
            $default = "media/system/default-profile.jpg"; //toDo switch live when in live setting
        }
        //s3 lamda auto create square thumbnail
        // return $this->avatar_path ? Yii::getAlias($this->avatar_base_url . '/thumbnail_square/' . $this->avatar_path): $default;
        return $this->avatar_path ? Utility::getPreSignedS3Url('thumbnail_square/' . $this->avatar_path): Utility::getPreSignedS3Url($default);
    }

    public function getAvatarByRegion($userModel) 
    {
        $roleArr = $userModel->getRoleArrayById($userModel->id);
        $isDealer = array_intersect([User::ROLE_DEALER_MANAGER, User::ROLE_DEALER_ASSOCIATE], $roleArr);
        // print_r($isDealer);exit();
        $default = $this->mapAvatarByRegion($userModel->region_id, $isDealer);

        return Utility::getPreSignedS3Url($default);
        //s3 lamda auto create square thumbnail
        // return $this->avatar_path ? Yii::getAlias($this->avatar_base_url . '/thumbnail_square/' . $this->avatar_path): $default;
        // return $this->avatar_path ? Utility::getPreSignedS3Url('thumbnail_square/' . $this->avatar_path): Utility::getPreSignedS3Url($default);
    }

    public function mapAvatarByRegion($region_id, $isDealer = false) {
        $dealerProfileArr = [
                SysRegion::THAILAND => "media/system/dealer-default-profile/th-profile.png",
                SysRegion::VIETNAM => "media/system/dealer-default-profile/vi-profile.png",
                SysRegion::MALAYSIA => "media/system/dealer-default-profile/my-profile.png",
                SysRegion::INDONESIA => "media/system/dealer-default-profile/id-profile.png",
                SysRegion::SINGAPORE => "media/system/dealer-default-profile/sg-profile.png",
            ];
        $userProfileArr = [
                SysRegion::THAILAND => "media/system/user-default-profile/th-profile.png",
                SysRegion::VIETNAM => "media/system/user-default-profile/vi-profile.png",
                SysRegion::MALAYSIA => "media/system/user-default-profile/my-profile.png",
                SysRegion::INDONESIA => "media/system/user-default-profile/id-profile.png",
                SysRegion::SINGAPORE => "media/system/user-default-profile/sg-profile.png",
            ];

        $default = "media/system/default-profile.jpg";

        if($isDealer) {
            if(isset($dealerProfileArr[$region_id])) {
                $default =  $dealerProfileArr[$region_id];
            }
        } else {
            if(isset($userProfileArr[$region_id])) {
                $default = $userProfileArr[$region_id];
            }
        }

        return $default;
    } 

    //html layouts
    public function getAvatarPic($default = null) {
        $pic = $this->getAvatar($default);
        return "<div class='avatar ' style='background-image:url($pic)'>" . "" . "</div>";
    }

    public function getAvatarLayout($link = null) {
        $model = $this;
        $pic = $model->getAvatar();
        $html = "<div class='avatar-holder'>";
        $div = "<div class='avatarbig ' style='background-image:url($pic)'>" . "" . "</div>";
        $html .= $link ? "<a href='".$link."'>" . $div . "</a>" : $div;
        $html .= "<div class='profile-detail'>";
        $html .= "<div class='name'>" . $model->fullName. "</div>" ;
        $html .='<div class="small"><span class="glyphicon glyphicon-envelope"></span>&nbsp '.$model->user->email."</div>";
        $html .='<div class="small"><span class="glyphicon glyphicon-earphone"></span>&nbsp '.$model->user->mobile_number_full."</div>";
        $html .= "</div><div>";
        return $html;
    }
    public function getAvatarSmallLayout($link = null) {
        $model = $this;
        $pic = $model->getAvatar();
        $div = "<div class='avatar-small-holder'>";
        $div .= "<div class='avatar ' style='background-image:url($pic)'></div>";        
        $div .= "<div class='profile-detail'>";
        $div .= "<div class='name'>" . $model->fullName. "</div>" ;
        $div .="</div></div>";
        $html = $link ? "<a href='".$link."'>" . $div . "</a>" : $div;
        return $html;
    }

    public function getCompletionStatus(){
        // return $this->first_name && $this->last_name && $this->birthday && $this->user->email && ($this->gender == 0 || $this->gender == 1);
        return $this->first_name && $this->last_name && $this->birthday && $this->user->email_status == User::EMAIL_STATUS_VERIFIED && ($this->gender == 0 || $this->gender == 1);
    }

    public function getUserDetailLayout() {
        $model = $this;
        $html = "<table class='table'><thead><tr>";
        $html .= "<th width='20'>Region</th>";
        $html .= "<th width='*'>User</th>";
        //$html .= "<th>Address</th>";
        $html .= "<th width='120'>Account status</th>";
        $html .= "<th width='100'>Mobile Status</th>";
        $html .= "<th width='100'>Email Status</th>";
        $html .= "<th width='100'>Created At</th>";
        $html .= "<th width='100'>Login At</th>";
        $html .= "</tr></thead>";
        $html .= "<tbody><tr>";
        $html .= "<td>" . $model->user->region_id . "</td>";
        $link = Url::to(['user/view', 'id' => $model->user_id]);
        $html .= "<td>" . $model->getAvatarLayout($link) . "</td>";
        
        //$html .= "<td>" . $model->address1 . "<br>" . $model->address2 . "<br>" . $model->address3 . "</td>";
        $html .= "<td>" . $model->user->account_status . "</td>";
        $html .= "<td>" . $model->user->mobile_status . "</td>";
        $html .= "<td>" . $model->user->email_status . "</td>";
        $html .= "<td>" . Yii::$app->formatter->asDatetime($model->user->created_at) . "</td>";
        $html .= "<td>" . Yii::$app->formatter->asDatetime($model->user->login_at) . "</td>";
        $html .= "</tr></tbody></table>";

        return $html;
    }



}

