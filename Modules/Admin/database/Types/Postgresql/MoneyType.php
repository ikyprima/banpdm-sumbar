<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class MoneyType extends Type
{
    public const NAME = 'money';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'money';
    }
}
