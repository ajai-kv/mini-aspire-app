<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepaymentSchedule extends Model
{
    use HasFactory;

    use HasUuids;

    protected $table = 'repayment_schedule';

    protected $primaryKey = 'id';

    protected $fillable = [
        'loan_id', 
        'amount', 
        'due_date',
        'status'
    ];

    // A repayment schedule belongs to a loan
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class, 'loan_id', 'id');
    }
}
