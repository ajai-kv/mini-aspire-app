<?php

namespace App\Enums;

enum RepaymentScheduleStatus:string {
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case WAIVED = 'WAIVED';
}

