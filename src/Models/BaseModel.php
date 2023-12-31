<?php

namespace Slowlyo\OwlAdmin\Models;

use Illuminate\Database\Eloquent\Model;
use Slowlyo\OwlAdmin\Traits\StaticTrait;
use Slowlyo\OwlAdmin\Traits\DateTimeFormatterTrait;

class BaseModel extends Model
{
    use StaticTrait, DateTimeFormatterTrait;
}
