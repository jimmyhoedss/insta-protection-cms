<?php
 
namespace common\rbac\rule;

class AccessRule extends \yii\filters\AccessRule {
 
    /**
     * @inheritdoc
     */
    protected function matchRole($user)
    {
        if (empty($this->roles)) {
            return true;
        }
        foreach ($this->roles as $role) {
            if ($role === '?') {
                if ($user->getIsGuest()) {
                    return true;
                }
            } elseif ($role === '@') {
                if (!$user->getIsGuest()) {
                    return true;
                }
            // Check if the user is logged in, and the roles match
            } elseif (!$user->getIsGuest() && $role === $user->identity->role) {
                return true;
            }
        }
     }
}

/*
ref: https://stackoverflow.com/questions/35417353/yii2-rbac-check-permissions-without-each-actions-in-controller

$admin = $auth->createRole('Admin');
$moderator = $auth->createRole('Moderator');

$createPost=$auth->createPermission('createPost');
$updatePost=$auth->createPermission('updatePost');
$deletePost=$auth->createPermission('deletePost');
$createCategory=$auth->createPermission('createCategory');
$updateCategory=$auth->createPermission('updateCategory');
$deleteCategory=$auth->createPermission('deleteCategory');


$auth->add($admin);
$auth->add($moderator);
$auth->add($createPost);
$auth->add($updatePost);
$auth->add($deletePost);
$auth->add($createCategory);
$auth->add($updateCategory);
$auth->add($deleteCategory);

$auth->addChild($admin,$moderator);
$auth->addChild($admin,$createCategory);
$auth->addChild($admin,$updateCategory);
$auth->addChild($admin,$deleteCategory);
$auth->addChild($moderator, $createPost);
$auth->addChild($moderator, $updatePost);
$auth->addChild($moderator, $deletePost);


$auth->assign($admin,1);
$auth->assign($moderator,2);

*/