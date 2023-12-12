<?php
namespace common\matchcallback;

use yii\rbac\Item;
use yii\rbac\Rule;
use common\models\DealerCompany;
use common\models\DealerUser;
use common\models\DealerOrder;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\HttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;


use api\components\CustomHttpException;

class DealerMatchCallBack 
{
    const MCB_API = "mcb_api";
    const MCB_CMS = "mcb_cms";
    
    public static function callbackViewInventory($user, $role, $company_ids, $mcb) {

        $company = DealerUser::getDealerFromUserId($user);
        $company_id = $company->id;
        $match = DealerCompany::find()->andWhere(['id'=> $company_id])->andWhere(['sp_inventory_order_mode' => DealerCompany::INVENTORY_MODE_STOCKPILE])->one() && Yii::$app->user->can($role);

        $flag = DealerCompany::isSameOrganisation($company_id, $company_ids);

        if(!$match && !$flag && $mcb = self::MCB_API) {
            $str = Utility::jsonifyError("dealer_company_ids", "Not authorized company ids.");
                    throw new CustomHttpException($str, CustomHttpException::UNAUTHORIZED);
        }
        if(!$match && !$flag && $mcb = self::MCB_CMS) {
            Yii::$app->session->setFlash('error', "Not authorized company ids!");
            return $this->redirect(['index']);
        }

        if($match && $flag) {
            return true;
        }

    }
}

?>