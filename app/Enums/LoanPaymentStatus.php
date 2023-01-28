<?php

namespace App\Enums;

enum LoanPaymentStatus:string {
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case PAID = 'PAID';
}
