<?php

namespace App\Libraries;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Backpack\Settings\app\Models\Setting;

class UniqueId
{
    // protected ?string $prefix = null;
    // protected array $setting;

    // public function __construct(public string $table, bool $initSetting = false)
    // {
    //     $initSetting && $this->setSetting();
    // }

    // public static function make(string $table)
    // {
    //     return new static($table, true);
    // }

    // protected function defaultDatePrefix($format = 'ym')
    // {
    //     return Carbon::now()->format($format);
    // }

    // protected function beforePrefix()
    // {
    //     return $this->prefix ?: $this->getSetting().$this->defaultDatePrefix();
    // }

    // /**
    //  * Set Prefix
    //  *
    //  * @param callable<string>|string $value, callble return type must be string
    //  */
    // public function usePrefix(callable|string $value)
    // {
    //     $this->prefix = is_callable($value) ? $value($this) : $value;

    //     return $this;
    // }

    // protected function setSetting(): void
    // {
    //     $key = 'unique_id_prefix';
        
    //     if (! Setting::where('key', $key)->first()) {
    //         $setting = new Setting;

    //         $setting->key = $key;
    //         $setting->name = 'Prefix before unique id';
    //         $setting->description = 'Prefix before unique id';
    //         $setting->value = json_encode([
    //             ['table' => 'transactions', 'prefix' => 'TR'],
    //             ['table' => 'orders', 'prefix' => 'OR'],
    //             ['table' => 'withdraw_orders', 'prefix' => 'WR'],
    //             ['table' => 'products', 'prefix' => 'PR'],
    //             ['table' => 'user', 'prefix' => 'US']
    //         ]);
    //         $setting->field = json_encode([
    //             'name' => 'value',
    //             'label' => 'Value',
    //             'type' => 'repeatable',
    //             'subfields' => [
    //                 [
    //                     'name'    => 'table',
    //                     'type'    => 'text',
    //                     'label'   => 'Table Name',
    //                     'wrapper' => ['class' => 'form-group col-md-6'],
    //                 ],
    //                 [
    //                     'name'    => 'prefix',
    //                     'type'    => 'text',
    //                     'label'   => 'Prefix',
    //                     'wrapper' => ['class' => 'form-group col-md-6'],
    //                 ],
    //             ],
    //             'hint' => 'prefix length must be <= 2'
    //         ]);
    //         $setting->active = true;
    //         $setting->save();
    //     }

    //     collect(json_decode(Setting::get($key), true))->each(
    //         fn ($v, $k) => $this->setting[$v['table']] = $v['prefix']
    //     );
    // }

    // public function getSetting()
    // {
    //     return $this->setting[$this->table] ?? null;
    // }

    // public function ulid(): string
    // {
    //     return $this->beforePrefix().Str::ulid();
    // }

    // public function uuid(): string
    // {
    //     return $this->beforePrefix().Str::uuid();
    // }

    public static function bankAccountId($id, $length = 9, $pad = 0)
    {
        return Str::of($id)->padLeft($length, $pad);
    }

    public static function bankAccountSplit($id, $split = 3, $seperate = ' ')
    {
        return Str::of(self::bankAccountId($id))->split($split)->implode($seperate);
    }
}