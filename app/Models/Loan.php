<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    use HasUuids;

    protected $table = 'loan';

    protected $primaryKey = 'id';

    protected $fillable = [
    'customer_id', 
    'tenure', 
    'tenure_type',
    'currency',
    'amount',
    'approved_by',
    'rejected_by',
    'reject_reason',
    'status'
    ];

    // A loan belongs to a customer
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    // A loan has many repayment schedules
    public function repaymentSchedule(): HasMany
    {
        return $this->hasMany(RepaymentSchedule::class, 'repayment_schedule_id', 'id');
    }
    
}
