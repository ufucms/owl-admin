<?php

namespace Slowlyo\OwlAdmin\Models;

use Slowlyo\OwlAdmin\Admin;
use Illuminate\Database\Eloquent\Model;
use Slowlyo\OwlAdmin\Traits\StaticTrait;
use Slowlyo\OwlAdmin\Traits\DateTimeFormatterTrait;

class BaseModel extends Model
{
    use StaticTrait, DateTimeFormatterTrait;
    
    public function __construct(array $attributes = [])
    {
        $this->setConnection(Admin::config('admin.database.connection'));

        parent::__construct($attributes);
    }
}
