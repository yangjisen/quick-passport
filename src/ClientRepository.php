<?php

namespace YangJiSen\QuickPassport;

use Illuminate\Support\Facades\Cache;
use Laravel\Passport\ClientRepository as PassportClientRepository;
use Laravel\Passport\Passport;

class ClientRepository extends PassportClientRepository
{
    protected string $cacheKey = 'Passport:ClientRepository';

    /**
     * Get a client by the given ID.
     *
     * @param  int|string  $id
     * @return \Laravel\Passport\Client|null
     */
    public function find($id): ?\Laravel\Passport\Client
    {
        return Cache::remember(
            "{$this->cacheKey}:{$id}",
            config('passport.cache_time', 86400),
            function () use ($id) {
                return parent::find($id);
            }
        );
    }


    /**
     * Get an active client by the given ID.
     *
     * @return \Laravel\Passport\Client|null
     */
    public function getClient(): ?\Illuminate\Database\Eloquent\Collection
    {
        $client = Passport::client();
        return $client->where('revoked', 0)->get();
    }
}
