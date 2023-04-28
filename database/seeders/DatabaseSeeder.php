<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $stops = \App\Models\Stop::factory(100)->create();

        foreach (range(1, 15) as $loop) {
            $routeStops = $stops->random(rand(5, 12))->shuffle();
            $firstStop = $routeStops->first();
            $lastStop = $routeStops->last();

            $route = \App\Models\Route::factory()->create([
                'name' => $firstStop->name . ' to ' . $lastStop->name,
            ]);
            $sortOrder = 0;
            $route->stops()->attach($routeStops->map(function ($stop) use (&$sortOrder) {
                return [
                    'stop_id' => $stop->id,
                    'sort_order' => $sortOrder++,
                ];
            })->all());

            $reverse_route = \App\Models\Route::factory()->create([
                'name' => $lastStop->name . ' to ' . $firstStop->name,
            ]);
            $sortOrder = 0;
            $reverse_route->stops()->attach($routeStops->reverse()->map(function ($stop) use (&$sortOrder) {
                return [
                    'stop_id' => $stop->id,
                    'sort_order' => $sortOrder++,
                ];
            })->all());
        }
    }
}
