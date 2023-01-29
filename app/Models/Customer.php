<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'customer';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'date_of_birth',
        'address',
        'identification_document',
        'document_reference_number',
        'deleted_at'
    ];

    // A customer is mapped to a user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function loan(): HasMany
    {
        return $this->hasMany(Loan::class, 'loan_id', 'id');
    }
}
