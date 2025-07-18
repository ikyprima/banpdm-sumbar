<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class TsVectorType extends Type
{
    public const NAME = 'tsvector';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'tsvector';
    }
}
