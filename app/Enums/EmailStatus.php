<?php

namespace App\Enums;

enum EmailStatus: string
{
    case PENDING = 'pending';
    case SENDING = 'sending';
    case SENT    = 'sent';
    case FAILED  = 'failed';
}