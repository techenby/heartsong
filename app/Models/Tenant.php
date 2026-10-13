<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * Each user owns exactly one tenant, which holds their private database.
 *
 * @property string $id
 * @property int $user_id
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;

    /**
     * Columns stored directly on the tenants table instead of the `data` JSON column.
     *
     * @return array<int, string>
     */
    public static function getCustomColumns(): array
    {
        return ['id', 'user_id'];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
