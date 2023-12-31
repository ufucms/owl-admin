<?php

namespace Slowlyo\OwlAdmin\Traits;

trait DateTimeFormatterTrait
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->getDateFormat());
    }
}
