<?php

namespace App\Services;

use External\Bar\Movies\MovieService as BarService;
use External\Baz\Exceptions\ServiceUnavailableException as BazException;
use External\Foo\Exceptions\ServiceUnavailableException as FooException;
use External\Bar\Exceptions\ServiceUnavailableException as BarException;
use External\Baz\Movies\MovieService as BazService;
use External\Foo\Movies\MovieService as FooService;
use Cache;
use Illuminate\Support\Arr;

class MovieService
{
    private $services = [];
    private $titles = [];

    public function __construct(BarService $barService, BazService $bazService, FooService $fooService)
    {
        $this->services[] = $barService;
        $this->services[] = $bazService;
        $this->services[] = $fooService;
    }

    public function getTitles(): array
    {
        foreach ($this->services as $service) {
            try {
                $this->titles[get_class($service)] = Cache::get(get_class($service), function() use ($service) {
                    return $this->processResponse($service->getTitles(), $service);
                });
            } catch (BazException|BarException|FooException $e) {
                // co zrobić, skoro zadanie wspomina o ponowieniu, ale też o wyświetlaniu błędu jeśli którykolwiek z serwisów dał błąd...
                // wyciszam i wyświetlam to co mam...
            }
        }

        return Arr::flatten(array_values($this->titles));

    }

    public function processResponse(array $titles, $service): array
    {
        switch (get_class($service)) {
            case BarService::class:
                return array_column($titles['titles'], 'title');
            case BazService::class:
                return $titles['titles'];
            case FooService::class: {
                return $titles;
            }
        }
    }

    public function getCached(string $service)
    {
        return Cache::get($service);
    }
}
