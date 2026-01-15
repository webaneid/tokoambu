<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Domain\Inventory\Events\PurchaseReceived::class => [
            \App\Domain\Inventory\Listeners\CreateReceiveMovementsAndIncreaseBalances::class,
        ],
        \App\Domain\Inventory\Events\StockTransferred::class => [
            \App\Domain\Inventory\Listeners\MoveBalancesAndCreateTransferMovement::class,
        ],
        \App\Domain\Inventory\Events\StockAdjusted::class => [
            \App\Domain\Inventory\Listeners\ApplyAdjustmentAndCreateMovement::class,
        ],
        \App\Domain\Inventory\Events\OrderPackedOrShipped::class => [
            \App\Domain\Inventory\Listeners\DecreaseBalanceAndCreateShipMovement::class,
        ],
        \App\Domain\Inventory\Events\StockOpnameConfirmed::class => [
            \App\Domain\Inventory\Listeners\CreateOpnameAdjustments::class,
        ],
    ];
}
