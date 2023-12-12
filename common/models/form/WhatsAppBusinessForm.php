<?php
namespace common\models\form;

use common\models\User;
use common\models\SysRegion;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;
use yii\web\JsExpression;
use common\components\MyCustomBadRequestException;

/**
 * Password reset form
 */

class WhatsAppBusinessForm extends Model
{
    const MOBILE_NUMBER_SG = "+6598551971";
    const MOBILE_NUMBER_MY = "+60126934226";
    const MOBILE_NUMBER_VN = "+60126934226";
    const MOBILE_NUMBER_TH = "+60126934226";


    public static function getWhatsappBusinessUrl($region_id) {

        $url = "";
        switch ($region_id) {
            case SysRegion::SINGAPORE:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_SG."?text=Hi+Instaprotection%2C+I%27ve+some+enquires";
                break;
            case SysRegion::VIETNAM:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_VN."?text=Hi+Instaprotection%2C+I%27ve+some+enquires+vietnam";
                break;
            case SysRegion::MALAYSIA:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_MY."?text=Hi+Instaprotection%2C+I%27ve+some+enquires+malaysia";
                break;
            case SysRegion::THAILAND:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_TH."?text=Hi+Instaprotection%2C+I%27ve+some+enquires+thailand";
                break;
            default:
                $url = "https://wa.me/".SELF::MOBILE_NUMBER_MY."?text=Hi+Instaprotection%2C+I%27ve+some+enquires+default";
                break;
        }

        return $url;
    }

  
}
