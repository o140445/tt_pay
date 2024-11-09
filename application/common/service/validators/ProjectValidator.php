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

        // 代付扩展字段检查
        if ($data['type'] == OrderInService::STATUS_OPEN) {
            $extend = json_decode($product->extend, true); //[{"title":"类型","value":"type"},{"title":"账号","value":"pix"},{"title":"xx","value":"ww"}]
            $extra = $data['extra']; //{"type":"1","pix":"123456","xx":"ww"}

            // 代付扩展字段检查
            foreach ($extend as $item) {
                if (!isset($extra[$item['value']])) {
                    $this->errorMessage = $item['title'] . "是必需的";
                    return false;
                }
            }
        }


        return true;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }
}