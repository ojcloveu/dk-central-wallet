<?php

namespace App\Enum\Triggers;

use Illuminate\Support\Facades\DB;

enum TransactionTgEnum: string
{
    case BEFORE_DELETE = 'transaction_before_delete_tg';
    case BEFORE_UPDATE = 'transaction_before_update_tg';

    public static function clearTrigger()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::BEFORE_DELETE->value);
        DB::unprepared('DROP TRIGGER IF EXISTS '.self::BEFORE_UPDATE->value);
    }

    public static function addTrigger()
    {
        self::clearTrigger();
        
        DB::unprepared("
            CREATE TRIGGER ".self::BEFORE_DELETE->value."
            BEFORE DELETE ON transactions
            FOR EACH ROW
            BEGIN
            IF OLD.confirmed = 1 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete confirmed transaction';
            END IF;
            END;
        ");

        DB::unprepared("
            CREATE TRIGGER ".self::BEFORE_UPDATE->value."
            BEFORE UPDATE ON transactions
            FOR EACH ROW
            BEGIN
            IF OLD.confirmed = 1 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot update confirmed transaction';
            END IF;
            END;
        ");
    }
}
