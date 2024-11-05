<?php

namespace app\common\service\validators;

use app\admin\model\Project;
use app\common\service\OrderInService;

class ProjectValidator implements ValidatorInterface
{
    protected $errorMessage;

    public function validate(array $data): bool {
        if (!isset($data['product_id'])) {
            $this->errorMessage = "产品ID是必需的";
            return false;
        }

        $product = Project::where('status', OrderInService::STATUS_OPEN)->find($data['product_id']);
        if (!$product) {
            $this->errorMessage = "产品不存在";
            return false;
        }


        return true;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }
}