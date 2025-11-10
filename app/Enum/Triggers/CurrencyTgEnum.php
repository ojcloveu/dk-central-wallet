<?php

namespace App\Enum\Triggers;

use Illuminate\Support\Facades\DB;

enum CurrencyTgEnum: string
{
    case AFTER_CREATE = 'currencies_after_create_tg';
    case AFTER_UPDATE = 'currencies_after_update_tg';

    public static function clearTrigger()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::AFTER_CREATE->value);
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::AFTER_UPDATE->value);
    }

    public static function addTrigger()
    {
        self::clearTrigger();
        
        DB::unprepared('
            CREATE TRIGGER '.self::AFTER_CREATE->value.'
            AFTER INSERT ON currencies
            FOR EACH ROW
            BEGIN
                UPDATE wallet
                SET meta = JSON_SET(
                    COALESCE(meta, JSON_OBJECT()),
                    "$.code", NEW.code,
                    "$.short_code", NEW.short_code,
                    "$.decimal_places", NEW.decimal_places,
                    "$.minor_unit", NEW.minor_unit
                )
                WHERE currency_id = NEW.id;
            END
        ');

        DB::unprepared('
            CREATE TRIGGER '.self::AFTER_UPDATE->value.'
            AFTER UPDATE ON currencies
            FOR EACH ROW
            BEGIN
                UPDATE wallet
                SET meta = JSON_SET(
                    COALESCE(meta, JSON_OBJECT()),
                    "$.code", NEW.code,
                    "$.short_code", NEW.short_code,
                    "$.decimal_places", NEW.decimal_places,
                    "$.minor_unit", NEW.minor_unit
                )
                WHERE currency_id = NEW.id;
            END
        ');
    }
}
