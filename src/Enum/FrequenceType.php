<?php

namespace App\Enum;

enum FrequenceType: string
{
    case QUOTIDIEN = 'quotidien';
    case HEBDOMADAIRE = 'hebdomadaire';
    case MENSUEL = 'mensuel';
}