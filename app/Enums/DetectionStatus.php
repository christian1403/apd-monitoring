<?php

namespace App\Enums;

enum DetectionStatus: string
{
    case SAFE = 'safe';
    case WARNING = 'warning';
    case UNSAFE = 'unsafe';
}
