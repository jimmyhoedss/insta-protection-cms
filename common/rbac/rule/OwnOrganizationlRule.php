<?php
/**
 * Eugine Terentev <eugine@terentev.net>
 */

namespace common\rbac\rule;

use yii\rbac\Item;
use yii\rbac\Rule;

class OwnOrganizationlRule  extends Rule
{
    /** @var string */
    public $name = 'ownOrganizationlRule';

    /**
     * @param int $user
     * @param Item $item
     * @param array $params
     * - model: model to check owner
     * - attribute: attribute that will be compared to user ID
     * @return bool
     */
    public function execute($user, $item, $params)
    {
        // $attribute = isset($params['attribute']) ? $params['attribute'] : 'created_by';
        // return $user && isset($params['model']) && $user === $params['model']->getAttribute($attribute);
        print_r($params);
        print_r($user);
        print_r($item);
        exit();
        return true;
    }
}
