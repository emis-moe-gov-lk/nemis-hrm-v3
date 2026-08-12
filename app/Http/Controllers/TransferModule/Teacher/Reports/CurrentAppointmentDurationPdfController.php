<?php

namespace App\Http\Controllers\TransferModule\Teacher\Reports;

use App\Http\Controllers\Controller;
use App\Models\EmployerCurrentAppointment;
use App\Models\Service;
use Illuminate\Http\Request;
use misterspelik\LaravelPdf\Facades\Pdf;

class CurrentAppointmentDurationPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'years' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],
            'service_id' => [
                'nullable',
                'string',
                'exists:services,service_id',
            ],
        ]);

        $years = (int) $validated['years'];
        $serviceId = $validated['service_id'] ?? null;
        $cutoffDate = now()->startOfDay()->subYears($years);

        $appointments = EmployerCurrentAppointment::query()
            ->with([
                'employee.title',
                'rank.service',
                'position',
                'officeLevel',
                'workplace',
            ])
            ->whereNotNull('appoint_date')
            ->whereDate('appoint_date', '<', $cutoffDate)
            ->when(
                filled($serviceId),
                fn ($query) => $query->whereHas(
                    'rank',
                    fn ($rankQuery) => $rankQuery->where(
                        'service_id',
                        $serviceId
                    )
                )
            )
            ->orderBy('appoint_date')
            ->orderBy('employee_id')
            ->get();

        $service = filled($serviceId)
            ? Service::query()
                ->where('service_id', $serviceId)
                ->first()
            : null;

        $pdf = Pdf::loadView(
            'pdf.transfer.current-appointment-duration-report',
            [
                'appointments' => $appointments,
                'years' => $years,
                'cutoffDate' => $cutoffDate,
                'service' => $service,
                'generatedAt' => now(),
            ]
        );

        $fileName = 'current-appointment-duration-report-'
            . now()->format('Y-m-d-His')
            . '.pdf';

        return $pdf->download($fileName);
    }
}
