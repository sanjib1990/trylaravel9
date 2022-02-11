<?php

namespace App\Thinkific\Repositories;

use App\Thinkific\Constants;
use App\Thinkific\Models\Order;
use Illuminate\Support\Collection;

class OrderRespository
{
    /**
     * @param mixed $id
     *
     * @return \App\Thinkific\Models\Order|null
     */
    public function findById(mixed $id): Order | null
    {
        return Order::query()->find($id);
    }

    /**
     * @param \Illuminate\Support\Collection $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function fetch(Collection $request): Collection
    {
        return Order::query()->orderByDesc(Constants::CREATED_AT)->get();
    }

    /**
     * @param \App\Thinkific\Models\Order $order
     *
     * @return void
     */
    public function refund(Order $order): void
    {
        $order->setAttribute(Constants::STATUS, Constants::REFUNDED);
        $order->save();
    }
}
