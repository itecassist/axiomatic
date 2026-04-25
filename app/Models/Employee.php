<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $appends = ['employee_display'];

    protected $fillable = [
        'branch_id',
        'first_name',
        'last_name',
        'employee_number'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class)->withDefault();
    }

    public function commission_notes(): HasMany
    {
        return $this->hasMany(CommissionNote::class);
    }

    public function getEmployeeDisplayAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
