<?php

namespace App\Models\Wallet\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait MetaAttribute
{
    public function metaMinorUnit(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->meta['minor_unit']
        );
    }

    public function metaDecimalPlaces(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->meta['decimal_places']
        );
    }

    public function metaShortCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->meta['short_code']
        );
    }

    public function metaCode(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->meta['code']
        );
    }
}