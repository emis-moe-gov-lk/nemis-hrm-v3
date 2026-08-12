<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Current Appointment Duration Report</title>

    <style>
        @page {
            margin: 15mm 10mm 15mm 10mm;
        }

        body {
            font-family: sans-serif;
            font-size: 9px;
            color: #1e293b;
        }

        h1 {
            margin: 0 0 5px;
            text-align: center;
            font-size: 18px;
        }

        .subtitle {
            margin-bottom: 15px;
            text-align: center;
            color: #475569;
        }

        .summary {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .summary td {
            width: 25%;
            padding: 8px;
            border: 1px solid #cbd5e1;
        }

        .label {
            display: block;
            margin-bottom: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
        }

        .value {
            font-size: 10px;
            font-weight: bold;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .report-table th {
            padding: 7px 5px;
            border: 1px solid #94a3b8;
            background: #e2e8f0;
            font-size: 8px;
            text-align: left;
            text-transform: uppercase;
        }

        .report-table td {
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .center {
            text-align: center;
        }

        .secondary {
            margin-top: 2px;
            font-size: 8px;
            color: #64748b;
        }

        .nowrap {
            white-space: nowrap;
        }

        .empty {
            padding: 25px !important;
            text-align: center;
            color: #64748b;
        }

        .footer {
            margin-top: 12px;
            font-size: 8px;
            text-align: right;
            color: #64748b;
        }
    </style>
</head>

<body>
    <h1>Current Appointment Duration Report</h1>

    <div class="subtitle">
        Employees who have remained in their current appointment for more
        than {{ $years }}
        {{ \Illuminate\Support\Str::plural('completed year', $years) }}
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="label">Service</span>

                <span class="value">
                    {{ $service?->service_name ?? 'All services' }}
                </span>
            </td>

            <td>
                <span class="label">Duration</span>

                <span class="value">
                    More than {{ $years }}
                    {{ \Illuminate\Support\Str::plural('year', $years) }}
                </span>
            </td>

            <td>
                <span class="label">Appointment before</span>

                <span class="value">
                    {{ $cutoffDate->format('Y-m-d') }}
                </span>
            </td>

            <td>
                <span class="label">Matching records</span>

                <span class="value">
                    {{ number_format($appointments->count()) }}
                </span>
            </td>
        </tr>
    </table>

    <table class="report-table">
        <thead>
            <tr>
                <th class="center">#</th>
                <th>Employee</th>
                <th>Workplace</th>
                <th>Position</th>
                <th>Service and rank</th>
                <th>Appointment date</th>
                <th>Duration</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($appointments as $appointment)
                @php
                    $duration = $appointment->appoint_date->diff(now());

                    $employeeName =
                        $appointment->employee?->name_with_initials
                        ?? $appointment->employee?->full_name
                        ?? '-';

                    $workplaceName =
                        $appointment->workplace?->office_name
                        ?? $appointment->workplace_id
                        ?? '-';
                @endphp

                <tr>
                    <td class="center">
                        {{ $loop->iteration }}
                    </td>

                    <td>
                        <strong>
                            {{ $appointment->employee?->title?->title_name }}
                            {{ $employeeName }}
                        </strong>

                        <div class="secondary">
                            {{ $appointment->employee_id }}
                        </div>
                    </td>

                    <td>
                        {{ $workplaceName }}

                        <div class="secondary">
                            {{ $appointment->workplace_id ?? '-' }}
                        </div>
                    </td>

                    <td>
                        {{ $appointment->position?->position_name ?? '-' }}
                    </td>

                    <td>
                        <strong>
                            {{ $appointment->rank?->service?->service_name ?? '-' }}
                        </strong>

                        <div class="secondary">
                            {{ $appointment->rank?->rank_name ?? '-' }}
                        </div>
                    </td>

                    <td class="nowrap">
                        {{ $appointment->appoint_date->format('Y-m-d') }}
                    </td>

                    <td class="nowrap">
                        {{ $duration->y }}Y
                        {{ $duration->m }}M
                        {{ $duration->d }}D
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">
                        No matching current appointments found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated: {{ $generatedAt->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>
