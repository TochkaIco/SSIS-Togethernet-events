<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Jenssegers\Agent\AgentServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AgentServiceProvider::class,
];
