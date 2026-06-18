<?php

namespace App\Services;

use App\Models\Order;

class DispatchingService
{
    public static function getFirstOrderIdForTechnicianIndepartment(int | null $technicianId, int $departmentId) : int
    {
        if($technicianId) {
            return Order::query()
                ->where('department_id', $departmentId)
                ->where('technician_id', $technicianId)
                ->whereIn('status_id', [3, 4, 5])
                ->orderBy('sort_number', 'asc')
                ->first()
                ?->id ?? 0;
        }
        return 0;
    }
    

}
