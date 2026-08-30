<?php

declare(strict_types=1);

namespace App\Support;

class MonotonicClock
{
    public function nowNanoseconds(): int
    {
        return hrtime(true);
    }
}
