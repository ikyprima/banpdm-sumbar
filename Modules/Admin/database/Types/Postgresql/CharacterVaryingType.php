<?php

namespace Modules\Admin\database\Types\Postgresql;

use Modules\Admin\database\Types\Common\VarCharType;

class CharacterVaryingType extends VarCharType
{
    public const NAME = 'character varying';
    public const DBTYPE = 'varchar';
}
