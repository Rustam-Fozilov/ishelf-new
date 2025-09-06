<?php

namespace App\Services\PrintLog;

use App\Models\PrintLog\PrintLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use App\Models\Shelf\ShelfChange;
use App\Services\Shelf\ShelfChangeService;
use Illuminate\Pagination\LengthAwarePaginator;

class PrintLogService
{
    public function listByShelf(int $shelf_id, array $params): LengthAwarePaginator
    {
        return PrintLog::with(['shelf', 'user'])
            ->where('shelf_id', $shelf_id)
            ->when(isset($params['status']), function ($q) use ($params) {
                $q->where('status', $params['status']);
            })
            ->when(isset($params['date_from']), function ($q) use ($params) {
                $q->whereDate('created_at', '>=', $params['date_from']);
            })
            ->when(isset($params['date_to']), function ($q) use ($params) {
                $q->whereDate('created_at', '<=', $params['date_to']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($params['per_page'] ?? 10);
    }

    public static function create(int $shelf_id, int $status, int $user_id = null): void
    {
        $last_change = ShelfChangeService::getLastChange($shelf_id);

        if (!is_null($last_change)) {
            PrintLog::query()->create([
                'shelf_id'  => $shelf_id,
                'user_id'   => $user_id ?? auth()->id(),
                'change_id' => $last_change->id,
                'status'    => $status,
            ]);
        }
    }

    public function top(array $request): LengthAwarePaginator
    {
        $splitDate = explode('-', $request['date_from']);
        $iterateMonth = (int) $splitDate[1] + 1;
        $splitDate[1] = $iterateMonth < 10 ? '0' . $iterateMonth : $iterateMonth;
        $dateTo = implode('-', $splitDate);

        $user_prints = PrintLog::with(['shelf.branches', 'shelf.category', 'user'])
            ->where('print_logs.status', 1)
            ->whereRelation('shelf', 'status', '=', 1)
            ->whereRelation('user.role', 'id', '=', 2)
            ->when(isset($request['category_sku']), function ($query) use ($request) {
                $query->whereRelation('shelf', 'category_sku', '=', $request['category_sku']);
            })
            ->when(isset($request['branch_id']), function ($query) use ($request) {
                $query->whereRelation('shelf.branches', 'id', '=', $request['branch_id']);
            })
            ->when(isset($request['region_id']), function ($query) use ($request) {
                $query->whereRelation('shelf.branches.region', 'id', '=', $request['region_id']);
            })
            ->when(isset($request['date_from']), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request['date_from']);
            })
            ->when(isset($request['date_from']), function ($q) use ($dateTo) {
                $q->whereDate('created_at', '<=', $dateTo);
            })
            ->get()
            ->groupBy('user_id');

        $top = [];
        foreach ($user_prints as $user_id => $user_print) {
            $result = $this->filterDuplicatePrints($user_print);
            $finalResult = $this->filterForLastPrint($result);

            foreach ($finalResult as $item) {
                $last_saved = ShelfChange::query()
                    ->where('shelf_id', $item['shelf_id'])
                    ->orderBy('id', 'desc')
                    ->first();
                if (is_null($last_saved)) continue;
                $has_seen = PrintLog::query()
                    ->where('shelf_id', $item['shelf_id'])
                    ->where('user_id', $item['user_id'])
                    ->where('status', 4)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($last_saved->id == $item['change_id']) {
                    $minute = $this->calculatePrintTime($last_saved, $item);
                    $time = $this->convertMinuteToTime($minute);

                    $item->status_text = trans('base.done');
                    $item->status_text_id = 1;
                    $item->minute = $minute;
                    $item->time = $time;
                } else if ($has_seen->change_id == $last_saved->id) {
                    $item->status_text = trans('base.in_process');
                    $item->status_text_id = 2;
                } else {
                    $item->status_text = trans('base.not_started');
                    $item->status_text_id = 3;
                }
            }

            $top[$user_id]['shelves'] = $finalResult;
            $calc = $finalResult->filter(function ($item) {
                return isset($item['minute']);
            });
            $average_time = $calc->avg('minute');
            $top[$user_id]['average_minute'] = $average_time;
            $top[$user_id]['average_time'] = $this->convertMinuteToTime($average_time);
            $top[$user_id]['branch'] = $user_print[0]['shelf']['branches']['name'];
            $top[$user_id]['user'] = $user_print[0]['user'];
        }

        foreach ($top as &$user) {
            $statusIds = collect($user['shelves'])->pluck('status_text_id')->unique();

            if ($statusIds->count() === 1) {
                $user['average_status_id'] = $statusIds->first();
                $user['average_status'] = $statusIds->first() == 1 ? trans('base.done') : trans('base.not_started');
            } else {
                $user['average_status_id'] = 2;
                $user['average_status'] = trans('base.in_process');
            }
        }

        usort($top, function ($a, $b) {
            if (!isset($a['average_minute']) && !isset($b['average_minute'])) {
                return 0; // Both are unset
            }
            if (!isset($a['average_minute'])) {
                return 1; // $a is unset, so $b should come first
            }
            if (!isset($b['average_minute'])) {
                return -1; // $b is unset, so $a should come first
            }
            return $a['average_minute'] <=> $b['average_minute']; // Ascending sort
        });

        return $this->paginate($top, $request['per_page'] ?? 15, $request['page']);
    }

    private function filterDuplicatePrints($user_print)
    {
        $collection = collect($user_print);
        return $collection->unique(function ($item) {
            return $item['shelf_id'] . $item['change_id'] . $item['user_id'];
        })->values();
    }

    private function filterForLastPrint($result): mixed
    {
        return $result->groupBy('shelf_id')
            ->map(function ($group) {
                return $group->sortByDesc('change_id')->first();
            })
            ->values();
    }

    private function calculatePrintTime($saved, mixed $printed): float
    {
        $start = Carbon::parse($saved->created_at);
        $end = Carbon::parse($printed->created_at);
        $minutes = $end->diffInMinutes($start);
        return round(abs($minutes), 2);
    }

    private function convertMinuteToTime($minutes): string
    {
        $hours = floor($minutes / 60);  // Get the number of hours
        $remainingMinutes = floor($minutes % 60);  // Get the remaining minutes
        $remainingSeconds = round(($minutes - floor($minutes)) * 60);  // Get the remaining seconds
        $timeString = '';

        if ($hours > 0) {
            $timeString .= $hours . ' ' .  trans('times.hour');
        }

        if ($remainingMinutes > 0) {
            if ($hours > 0) {
                $timeString .= ' ';
            }
            $timeString .= $remainingMinutes . ' ' . trans('times.minute');
        }

        if ($remainingSeconds > 0 && $hours == 0) {
            if ($remainingMinutes > 0) {
                $timeString .= ' ';
            }
            $timeString .= $remainingSeconds . ' ' . trans('times.second');
        }

        return $timeString;
    }

    private function paginate($items, $perPage = 10, $page = null, $options = []): LengthAwarePaginator
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage)->values(), $items->count(), $perPage, $page, $options);
    }
}
