<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class StopsController extends Controller
{
    public function sendStops(Request $request)
    {

        $stopsArray = array();
        $input = $request->all();
        if (array_key_exists('stop', $input)) {
            $stops = DB::table('stops')->get();
            $stopName = $input['stop'];
            $stopIdFromDatabase = DB::table('stops')
                ->where('name', '=', $stopName)
                ->get();

            if (empty($stopIdFromDatabase[0])) {
                return response()->json([
                    'errorMessage' => "Provided stop does not exist",
                    'errorCode' => 404
                ]);
            }

            $stopId = $stopIdFromDatabase[0]->id;
            $routesWithStopID = DB::table('route_stop')
                ->where('stop_id', '=', $stopId)
                ->get();

            if (empty($routesWithStopID[0])) {
                return response()->json([
                    'errorMessage' => "Stop exists but It Isn't associated with any routes",
                    'errorCode' => 500
                ]);
            }

            $routeIdArray = array();
            for ($i = 0; $i < sizeof($routesWithStopID); $i++) {
                array_push($routeIdArray, $routesWithStopID[$i]->route_id);
            }

            $routesWithRouteId = DB::table('route_stop')
                ->whereIn('route_id', $routeIdArray)
                ->orderBy('route_id', 'ASC')
                ->orderBy('id', 'ASC')
                ->orderBy('sort_order', 'ASC')
                ->get();

            $routesWithStops = array();

            for ($i = 0, $backwardsArrayIndex = 0, $forwardsArrayIndex = 1; $i < sizeof($routeIdArray); $i++, $backwardsArrayIndex++, $forwardsArrayIndex++) {

                for ($j = 0; $j < sizeof($routesWithRouteId); $j++) {
                    $backwardsStopNumber = 0;
                    $forwardsStopNumber = 0;
                    $backwardsStopArrayIndex = 0;
                    $forwardStopArrayIndex = 0;
                    $finalDestinations = [
                        0 => '',
                        1 => ''
                    ];

                    if ($routesWithRouteId[$j]->stop_id == $stopId) {
                        for ($k = $j; $k >= 0; $k--) {
                            if ($routesWithRouteId[$k]->route_id != $routeIdArray[$i]) {
                                break;
                            }

                            if (!$finalDestinations[0]) {
                                for ($m = $j; $m >= 0; $m--) {
                                    if ($routesWithRouteId[$m]->sort_order == 0) {
                                        foreach ($stops as $stop) {
                                            if ($stop->id == $routesWithRouteId[$m]->stop_id) {
                                                $finalDestinations[0] = $stop->name;
                                                break;
                                            }
                                        }
                                        break;
                                    }
                                }
                            }

                            $StartingAndFinalDestination =  "{$stopName}-{$finalDestinations[0]}";
                            if (!array_key_exists($backwardsArrayIndex, $routesWithStops)) {
                                $stopArray = [
                                    $StartingAndFinalDestination => []
                                ];
                                array_push($routesWithStops, $stopArray);
                                foreach ($stops as $stop) {
                                    if ($stop->id == $routesWithRouteId[$k]->stop_id) {
                                        $stop = [
                                            "Stop {$backwardsStopNumber}" => $stop->name
                                        ];
                                        array_push($routesWithStops[$backwardsArrayIndex][$StartingAndFinalDestination], $stop);
                                        $backwardsStopNumber++;
                                        $backwardsStopArrayIndex++;
                                        break;
                                    }
                                }
                                continue;
                            }

                            foreach ($stops as $stop) {
                                if ($stop->id == $routesWithRouteId[$k]->stop_id) {
                                    $stop = [
                                        "Stop {$backwardsStopNumber}" => $stop->name,
                                    ];
                                    $routesWithStops[$backwardsArrayIndex][$StartingAndFinalDestination][$backwardsStopArrayIndex] = $stop;
                                    $backwardsStopArrayIndex++;
                                    $backwardsStopNumber++;
                                    break;
                                }
                            }

                            if ($routesWithRouteId[$k]->sort_order == 0) {
                                break;
                            }
                        };

                        for ($l = $j; $l < sizeof($routesWithRouteId); $l++) {
                            $routesWithRouteIdClone = clone $routesWithRouteId;
                            if ($routesWithRouteId[$l]->route_id != $routeIdArray[$i]) {
                                break;
                            }
                            if (!$finalDestinations[1]) {
                                for ($f = $j; $f < sizeof($routesWithRouteId); $f++) {
                                    $nextStop = $f + 1;
                                    if ($routesWithRouteId[$f]->route_id != $routeIdArray[$i]) {
                                        $previousStop = $f - 1;
                                        foreach ($stops as $stop) {
                                            if ($stop->id == $routesWithRouteId[$previousStop]->stop_id) {

                                                $finalDestinations[1] = $stop->name;
                                                break;
                                            }
                                        }
                                        break;
                                    } else if (!array_key_exists($nextStop, $routesWithRouteIdClone->toArray())) {
                                        foreach ($stops as $stop) {
                                            if ($stop->id == $routesWithRouteId[$f]->stop_id) {
                                                $finalDestinations[1] = $stop->name;
                                                break;
                                            }
                                        }
                                        break;
                                    }
                                }
                            }

                            $StartingAndFinalDestination =  "{$stopName}-{$finalDestinations[1]}(f)";

                            if (!array_key_exists($forwardsArrayIndex, $routesWithStops)) {
                                $stopArray = [
                                    $StartingAndFinalDestination  => []
                                ];
                                array_push($routesWithStops, $stopArray);
                                foreach ($stops as $stop) {
                                    if ($stop->id == $routesWithRouteId[$l]->stop_id) {
                                        $stopvalue = [
                                            "Stop {$forwardsStopNumber}" => $stop->name
                                        ];
                                        array_push($routesWithStops[$forwardsArrayIndex][$StartingAndFinalDestination], $stopvalue);
                                        $forwardsStopNumber++;
                                        $forwardStopArrayIndex++;
                                        break;
                                    }
                                }
                                continue;
                            }
                            foreach ($stops as $stop) {
                                if ($stop->id == $routesWithRouteId[$l]->stop_id) {
                                    $stop = [
                                        "Stop {$forwardsStopNumber}" => $stop->name
                                    ];
                                    $routesWithStops[$forwardsArrayIndex][$StartingAndFinalDestination][$forwardStopArrayIndex] = $stop;
                                    $forwardsStopNumber++;
                                    $forwardStopArrayIndex++;
                                    break;
                                }
                                continue;
                            }

                            continue;
                        }
                    }
                }
            }

            return $routesWithStops;
        }
        $stops = DB::table('stops')->get();
        $routeStops = DB::table('route_stop')
            ->orderBy('route_id', 'asc')
            ->orderBy('sort_order', 'asc')
            ->get();
        $routes = DB::table('routes')->get();
        foreach ($routes as $route) {

            $routeId = $route->id;
            $routeName = $route->name;
            if (!array_key_exists($routeName, $stopsArray)) {

                $routeWithStops = [
                    'Route' => $routeName,
                    'Stops' => []
                ];
                array_push($stopsArray, $routeWithStops);
            }

            foreach ($routeStops as $routeStop) {
                $arrayIndex =  $routeId - 1;
                $stopId = $routeStop->stop_id;

                if ($routeStop->route_id != $routeId) {
                    continue;
                }
                foreach ($stops as $stop) {
                    if ($stopId == $stop->id) {
                        $stopValue = [
                            "Stop {$routeStop->sort_order}" => $stop->name
                        ];
                        array_push($stopsArray[$arrayIndex]['Stops'], $stopValue);
                        break;
                    }
                };
            };
        };


        return response()->json($stopsArray);
    }
}
