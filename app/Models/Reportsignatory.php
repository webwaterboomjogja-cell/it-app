<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reportsignatory extends Model
{
    public const ROLE_PREPARED =
        'prepared_by';

    public const ROLE_REVIEWED =
        'reviewed_by';

    public const ROLE_APPROVED =
        'approved_by';

    protected $fillable = [
        'user_id',
        'role',
        'name',
        'position',
        'signature_path',
        'is_active',
        'sort',
    ];

    protected $table = 'report_signatories';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            User::class
        );
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_PREPARED =>
                'Pembuat Laporan',

            self::ROLE_REVIEWED =>
                'Pemeriksa',

            self::ROLE_APPROVED =>
                'Penyetuju',

            default => '-',
        };
    }
}