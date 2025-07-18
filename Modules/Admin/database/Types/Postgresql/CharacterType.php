<?php

namespace Modules\Admin\database\Types\Postgresql;

use Modules\Admin\database\Types\Common\CharType;

class CharacterType extends CharType
{
    public const NAME = 'character';
    public const DBTYPE = 'bpchar';
}
