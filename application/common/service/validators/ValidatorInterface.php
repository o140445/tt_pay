<?php

namespace app\common\service\validators;

interface ValidatorInterface
{
    public function validate(array $data): bool;
    public function getErrorMessage(): string;
}