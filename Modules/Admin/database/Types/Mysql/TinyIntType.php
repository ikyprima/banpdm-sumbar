<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class TinyIntType extends Type
{
    public const NAME = 'tinyint';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        $commonIntegerTypeDeclaration = call_protected_method($platform, '_getCommonIntegerTypeDeclarationSQL', $field);

        return 'tinyint'.$commonIntegerTypeDeclaration;
    }
}
