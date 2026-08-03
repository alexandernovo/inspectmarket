<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerDataTable
{
    public static function make(
        Request $request,
        Builder $query,
        array $searchable,
        array $sortable,
        Closure $transform
    ): JsonResponse {
        $total = (clone $query)->count();
        $search = trim((string) $request->input('search.value', ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($searchable, $search) {
                foreach ($searchable as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $builder->{$method}($column, 'like', "%{$search}%");
                }
            });
        }

        $filtered = (clone $query)->count();
        $orderIndex = (int) $request->input('order.0.column', 0);
        $direction = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumn = $sortable[$orderIndex] ?? end($sortable);
        $length = min(max((int) $request->input('length', 10), 1), 100);
        $start = max((int) $request->input('start', 0), 0);

        $records = $query
            ->orderBy($orderColumn, $direction)
            ->skip($start)
            ->take($length)
            ->get()
            ->map($transform)
            ->values();

        return response()->json([
            'draw' => (int) $request->input('draw', 0),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $records,
        ]);
    }
}
