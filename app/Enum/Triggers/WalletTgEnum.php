<?php

namespace App\Enum\Triggers;

use Illuminate\Support\Facades\DB;

enum WalletTgEnum: string
{
    case AFTER_CREATE = 'wallets_after_create_tg';

    public static function clearTrigger()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::AFTER_CREATE->value);
    }

    public static function addTrigger()
    {
        self::clearTrigger();
        
        DB::unprepared('
            CREATE TRIGGER '.self::AFTER_CREATE->value.'
            BEFORE INSERT ON wallets
            FOR EACH ROW
            BEGIN
                IF NEW.meta IS NULL THEN
                    -- Fetch data from currency table and set NEW.meta
                    SELECT JSON_OBJECT(
                        "code", code,
                        "short_code", short_code,
                        "decimal_places", decimal_places,
                        "minor_unit", minor_unit
                    )
                    INTO @meta
                    FROM currencies
                    WHERE id = NEW.currency_id;

                    SET NEW.meta = @meta;
                END IF;
            END
        ');
    }
}
