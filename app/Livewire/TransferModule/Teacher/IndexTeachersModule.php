<?php

namespace App\Livewire\TransferModule\Teacher;

use Livewire\Component;

use App\Models\TeacherTransferPolicy;

use App\Models\TeacherTransferApplication;
use App\Support\Transfer\TransferAccess;
use Illuminate\Support\Facades\Auth;

class IndexTeachersModule extends Component
{
    public function render()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $currentOfficeLevelId = $user?->workplace?->office_level_id;
        $canViewPortal = TransferAccess::canViewPortal($user);
        $canViewTeacherSelfService = TransferAccess::canViewTeacherSelfService($user);
        $shouldUseTeacherSelfServiceDashboard = TransferAccess::shouldUseTeacherSelfServiceDashboard($user);
        $canViewPolicies = TransferAccess::canViewPolicies($user);
        $canManagePolicies = TransferAccess::canManagePolicies($user);
        $canViewBoards = TransferAccess::canViewBoards($user);
        $canBrowseActivePolicies = $canViewPolicies || $canViewBoards;
        $canViewRequests = TransferAccess::canViewRequestPipeline($user);
        $canViewScopedRequests = TransferAccess::canViewInstitutionRequests($user)
            || TransferAccess::canViewZonalRequests($user);
        $canManageAnnouncements = TransferAccess::canManageAnnouncements($user);

        if ($shouldUseTeacherSelfServiceDashboard) {
            $activePolicies = TeacherTransferPolicy::with(['authority'])
                ->active()
                ->orderByDesc('policy_year')
                ->orderByDesc('created_at')
                ->get();

            $allPolicies = TeacherTransferPolicy::with(['authority'])
                ->orderByDesc('policy_year')
                ->orderByDesc('created_at')
                ->get();

            $announcements = \App\Models\TransferAnnouncement::active()
                ->orderByDesc('publish_date')
                ->orderByDesc('created_at')
                ->take(5)
                ->get();

            return view('livewire.transfer-module.teacher.index-teachers-module-teacher', [
                'activePolicies' => $activePolicies,
                'allPolicies' => $allPolicies,
                'announcements' => $announcements,
                'canViewTeacherSelfService' => $canViewTeacherSelfService,
            ]);
        }

        $stats = [
            'total_policies' => TeacherTransferPolicy::count(),
            'active_policies' => TeacherTransferPolicy::active()->count(),
            'locked_policies' => TeacherTransferPolicy::where('is_locked', true)->count(),
            'total_applications' => TeacherTransferApplication::count(),
            'pending_applications' => TeacherTransferApplication::whereIn('status', ['submitted', 'processing'])->count(),
        ];

        $policies = $canBrowseActivePolicies
            ? TeacherTransferPolicy::with(['authority'])
                ->orderBy('policy_year', 'desc')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
            : collect();

        $boards = [
            [
                'label' => 'National Board',
                'desc' => 'Oversee inter-provincial transfers and national level policy implementation.',
                'icon' => 'globe-alt',
                'route' => route('transfer-board.national-teacher-transfer'),
                'gradient' => 'from-indigo-500 to-blue-600',
                'shadow' => 'shadow-indigo-200',
                'text' => 'text-indigo-600',
                'office_levels' => ['OLID001'],
            ],
            [
                'label' => 'Provincial Ministry Board',
                'desc' => 'Handle PMOE-stage transfer boards, including National School escalations.',
                'icon' => 'building-library',
                'route' => route('transfer-board.provincial-ministry-teacher-transfer'),
                'gradient' => 'from-fuchsia-500 to-violet-600',
                'shadow' => 'shadow-fuchsia-200',
                'text' => 'text-fuchsia-600',
                'office_levels' => ['OLID002'],
            ],
            [
                'label' => 'Provincial Board',
                'desc' => 'Manage intra-provincial teacher transfers across all zonal departments.',
                'icon' => 'building-office-2',
                'route' => route('transfer-board.province-teacher-transfer'),
                'gradient' => 'from-blue-500 to-cyan-600',
                'shadow' => 'shadow-blue-200',
                'text' => 'text-blue-600',
                'office_levels' => ['OLID003'],
            ],
            [
                'label' => 'Zonal Board',
                'desc' => 'Coordinate school-to-school transfers within a specific educational zone.',
                'icon' => 'map-pin',
                'route' => route('transfer-board.zone-teacher-transfer'),
                'gradient' => 'from-emerald-500 to-teal-600',
                'shadow' => 'shadow-emerald-200',
                'text' => 'text-emerald-600',
                'office_levels' => ['OLID004'],
            ],
        ];

        $tools = [];

        if ($canViewPortal) {
            $tools[] = [
                'label' => 'Appointment Duration Report',
                'desc' => 'Find employees who have remained in their current appointment longer than the specified number of years.',
                'icon' => 'chart-bar',
                'route' => route(
                    'transfer.reports.current-appointment-duration'
                ),
                'gradient' => 'from-cyan-500 to-blue-600',
                'shadow' => 'shadow-cyan-200',
                'text' => 'text-cyan-600',
            ];
        }

        if ($canViewPolicies) {
            $tools[] = [
                'label' => 'Policy Definitions',
                'desc' => 'Configure transfer rules, point rankings, and eligibility criteria.',
                'icon' => 'document-text',
                'route' => route('transfer.transfer-policies'),
                'gradient' => 'from-violet-500 to-fuchsia-600',
                'shadow' => 'shadow-violet-200',
                'text' => 'text-violet-600',
            ];
        }

        if ($canViewRequests || $canViewScopedRequests) {
            $tools[] = [
                'label' => 'Request Pipeline',
                'desc' => 'Handle internal department requests and administrative transfers.',
                'icon' => 'arrows-right-left',
                'route' => TransferAccess::recommendationRedirectRoute($user),
                'gradient' => 'from-orange-500 to-red-600',
                'shadow' => 'shadow-orange-200',
                'text' => 'text-orange-600',
            ];
        }

        if ($canManageAnnouncements) {
            $tools[] = [
                'label' => 'System Announcements',
                'desc' => 'Publish updates, deadlines, and notifications to all transfer portal users.',
                'icon' => 'megaphone',
                'route' => route('transfer.announcements'),
                'gradient' => 'from-blue-600 to-indigo-700',
                'shadow' => 'shadow-blue-300',
                'text' => 'text-blue-700',
            ];
        }

        $appeals = [
            [
                'label' => 'Provincial Ministry Appeal Board',
                'desc' => 'Review National School and PMOE-stage transfer appeals.',
                'icon' => 'chat-bubble-left-right',
                'route' => route('transfer-board.provincial-ministry-teacher-appeal'),
                'gradient' => 'from-violet-500 to-fuchsia-600',
                'shadow' => 'shadow-violet-200',
                'text' => 'text-violet-600',
                'office_levels' => ['OLID002'],
            ],
            [
                'label' => 'Province Appeal Board',
                'desc' => 'Review and process appeals for intra-provincial transfer decisions.',
                'icon' => 'chat-bubble-left-right',
                'route' => route('transfer-board.province-teacher-appeal'),
                'gradient' => 'from-rose-500 to-red-600',
                'shadow' => 'shadow-rose-200',
                'text' => 'text-rose-600',
                'office_levels' => ['OLID003'],
            ],
            [
                'label' => 'Zone Appeal Board',
                'desc' => 'Handle local school-level transfer appeals within zonal boundaries.',
                'icon' => 'chat-bubble-bottom-center-text',
                'route' => route('transfer-board.zone-teacher-appeal'),
                'gradient' => 'from-pink-500 to-rose-600',
                'shadow' => 'shadow-pink-200',
                'text' => 'text-pink-600',
                'office_levels' => ['OLID004'],
            ],
        ];

        $announcements = \App\Models\TransferAnnouncement::active()
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->title,
                    'desc' => $item->content,
                    'date' => 'Published: ' . $item->publish_date?->diffForHumans() ?? $item->created_at->diffForHumans(),
                    'link_text' => $item->link_text ?? 'Read More',
                    'link_route' => $item->link_route ?? '#',
                    'type' => $item->type
                ];
            })
            ->toArray();

        // Fallback for demo if empty
        if (empty($announcements)) {
            $announcements = [
                [
                    'title' => 'No Active Announcements',
                    'desc' => 'There are currently no active announcements for the transfer module.',
                    'date' => 'System',
                    'link_text' => $canManageAnnouncements ? 'Manage Announcements' : 'View Transfer Portal',
                    'link_route' => $canManageAnnouncements
                        ? route('transfer.announcements')
                        : route('my-transfer'),
                    'type' => 'info'
                ]
            ];
        }

        $boardVisibleToUser = function (array $card) use ($user, $canViewBoards, $currentOfficeLevelId): bool {
            if (!$canViewBoards || $user === null) {
                return false;
            }

            if ($user->hasRole('super admin')) {
                return true;
            }

            return in_array($currentOfficeLevelId, $card['office_levels'] ?? [], true);
        };

        $boards = collect($boards)
            ->filter($boardVisibleToUser)
            ->values()
            ->all();

        $appeals = collect($appeals)
            ->filter($boardVisibleToUser)
            ->values()
            ->all();

        return view('livewire.transfer-module.teacher.index-teachers-module', [
            'stats' => $stats,
            'boards' => $boards,
            'appeals' => $appeals,
            'tools' => $tools,
            'announcements' => $announcements,
            'policies' => $policies,
            'canViewPortal' => $canViewPortal,
            'canViewPolicies' => $canViewPolicies,
            'canBrowseActivePolicies' => $canBrowseActivePolicies,
            'canManagePolicies' => $canManagePolicies,
            'canViewBoards' => $canViewBoards,
            'canViewRequests' => $canViewRequests,
            'canViewScopedRequests' => $canViewScopedRequests,
            'canManageAnnouncements' => $canManageAnnouncements,
        ]);
    }
}
