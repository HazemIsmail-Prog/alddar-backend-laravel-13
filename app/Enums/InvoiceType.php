<?php

namespace App\Enums;

enum InvoiceType: string
{
    case SALES = 'sales';
    case PURCHASE = 'purchase';
    case CREDIT_NOTE = 'credit_note';
    case DEBIT_NOTE = 'debit_note';

    /**
     * Get the human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::SALES => 'Sales Invoice',
            self::PURCHASE => 'Purchase Invoice',
            self::CREDIT_NOTE => 'Credit Note',
            self::DEBIT_NOTE => 'Debit Note',
        };
    }

    /**
     * Get icon or badge color
     */
    public function color(): string
    {
        return match($this) {
            self::SALES => 'success',
            self::PURCHASE => 'info',
            self::CREDIT_NOTE => 'warning',
            self::DEBIT_NOTE => 'danger',
        };
    }

    /**
     * Check if it's a sales type
     */
    public function isSales(): bool
    {
        return in_array($this, [self::SALES, self::CREDIT_NOTE]);
    }

    /**
     * Check if it's a purchase type
     */
    public function isPurchase(): bool
    {
        return in_array($this, [self::PURCHASE, self::DEBIT_NOTE]);
    }

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all labels as array
     */
    public static function labels(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }

    /**
     * Get options for select dropdowns
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }
}