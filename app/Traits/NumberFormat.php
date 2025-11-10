<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait NumberFormat
{
    public function rnfNumberFormat(
        string $key,
        int|float|string|null $amount,
        $decimal = 2,
        $left = '',
        $right = ''
    ) {
        // Truncate the number without rounding
        $amount = $this->rnfRemoveNumber($amount, $decimal);

        $formattedAmount = $amount < 0
            ? "-{$left}" . number_format(abs($amount), $decimal) . $right
            : $left . number_format($amount, $decimal) . $right;

        return [
            $key => $amount,
            $key . '_format' => $formattedAmount
        ];
    }

    public function rnfNumberFormatRemoveMinus(
        string $key,
        int|float|string|null $amount,
        $decimal = 2,
        $left = '',
        $right = ''
    ) {
        $data = $this->rnfNumberFormat($key, $amount, $decimal, $left, $right);
        $data[$key.'_format'] = Str::replace('-', '', $data[$key.'_format']);
        
        return $data;
    }

    public function rnfRemoveNumber($amount, $decimal = 2)
    {
        // Convert the amount to a string and split it into integer and decimal parts
        $amountStr = (string) $amount;
        $parts = explode('.', $amountStr);

        // Ensure there is a decimal part to work with
        if (count($parts) > 1) {
            // Truncate the decimal part to the specified number of decimal places
            $parts[1] = substr($parts[1], 0, $decimal);

            // Join the integer and truncated decimal parts
            $amountStr = implode('.', $parts);
        }

        // Convert the string back to a float for numerical operations
        return (float) $amountStr;
    }
}
