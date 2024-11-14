<?php

namespace app\common\service\validators;

use app\common\model\merchant\OrderOut;
use app\common\model\merchant\Project;
use app\common\service\OrderInService;
use app\common\service\OrderOutService;

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
        if ($data['type'] == OrderOutService::TYPE_OUT && !isset($data['is_member'])) {
            $extend = json_decode($product->extend, true); //[{"title":"类型","value":"type"},{"title":"账号","value":"pix"},{"title":"xx","value":"ww"}]

            if (!$extend) {

                return true;
            }

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