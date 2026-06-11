<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
    case Student = 'student';
    case Parent = 'parent';

    /** Human-readable label (UA). */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Адміністратор',
            self::Teacher => 'Вчитель',
            self::Student => 'Учень',
            self::Parent => 'Батьки',
        };
    }

    /** Tailwind badge colour classes. */
    public function color(): string
    {
        return match ($this) {
            self::Admin => 'bg-rose-100 text-rose-700',
            self::Teacher => 'bg-indigo-100 text-indigo-700',
            self::Student => 'bg-emerald-100 text-emerald-700',
            self::Parent => 'bg-amber-100 text-amber-700',
        };
    }

    /** @return array<string,string> value => label */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }

    /** @return string[] all values */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
