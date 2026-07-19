<?php

namespace App\Events;

use App\Models\UserIntegration;
use Illuminate\Foundation\Events\Dispatchable;

class UserIntegrationConnected
{
    use Dispatchable;

    public function __construct(public readonly UserIntegration $userIntegration) {}
}
