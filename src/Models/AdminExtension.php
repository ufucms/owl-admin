<?php

namespace Slowlyo\OwlAdmin\Models;

class AdminExtension extends BaseModel
{
    //protected $table = 'admin_extensions';

    protected $fillable = ['name', 'is_enabled', 'options'];

    protected $casts = [
        'options' => 'json',
    ];
}
