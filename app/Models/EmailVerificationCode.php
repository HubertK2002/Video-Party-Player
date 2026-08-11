<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

#[Fillable(['user_id', 'code_hash', 'expires_at'])]
class EmailVerificationCode extends Model
{
    public const UPDATED_AT = null;

    /**
     * Ile minut kod jest ważny.
     */
    public const TTL_MINUTES = 10;

    /**
     * Ile razy można pomylić się przy jednym kodzie.
     */
    public const MAX_ATTEMPTS = 5;

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Unieważnia poprzednie kody użytkownika i tworzy nowy.
     * Zwraca kod w postaci jawnej — w bazie trzymany jest tylko hash.
     */
    public static function issueFor(User $user): string
    {
        static::where('user_id', $user->id)->delete();

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        static::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(static::TTL_MINUTES),
        ]);

        return $code;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function matches(string $code): bool
    {
        return Hash::check($code, $this->code_hash);
    }
}
