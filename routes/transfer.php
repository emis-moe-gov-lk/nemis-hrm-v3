<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\TransferModule\Teacher\IndexTeachersModule;
use App\Livewire\TransferModule\Teacher\TeacherTransferPolicy;
use App\Livewire\TransferModule\Teacher\TeacherTransferRequest;
use App\Livewire\TransferModule\Teacher\AnnouncementManagement;
use App\Livewire\Teacher\Transfer\TeacherTransferApplicationView;
use App\Livewire\TransferModule\Teacher\TeacherTransferPolicyList;
use App\Livewire\TransferModule\Teacher\TeacherTransferPolicyView;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\ProvinceTeacherAppealBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\ProvinceTeacherTransferBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\ProvincialMinistryTeacherAppealBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\ProvincialMinistryTeacherTransferBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\ZoneTeacherAppealBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\ZoneTeacherTransferBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\NationalTeacherTransferBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\TeacherProfileForAppealBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\TeacherProfileForTeacherTransferBoard;
use App\Livewire\TransferModule\Teacher\TeacherTransferBoard\InstitutionProfileForTeacherTransferBoard;
use App\Http\Controllers\Pdf\TeacherTransferApplicationPdfController;
use App\Http\Controllers\Pdf\TeacherTransferBoardAppealReportPdfController;
use App\Http\Controllers\Pdf\TeacherTransferBoardDecisionReportPdfController;
use App\Http\Controllers\Pdf\TeacherTransferBoardSchoolBalanceReportPdfController;
use App\Livewire\Teacher\Transfer\TeacherTransferPotal;
use App\Livewire\Teacher\Transfer\TeacherMutualTransfer;
use App\Livewire\Teacher\Transfer\TeacherSpecialRequest;
use App\Livewire\Teacher\Transfer\TeacherAnnualTransfer;
use App\Livewire\Teacher\Transfer\TeacherTransferApplication;
use App\Livewire\Teacher\Transfer\TeacherTransferPolicyRequests;
use App\Livewire\Teacher\Transfer\TeacherTransferGuidelines;
use App\Livewire\Institutions\Transfer\TransferRequests;
use App\Livewire\Offices\Zeo\Transfer\TeachersTransferRequests;
use App\Livewire\TransferModule\Teacher\TeacherTransferController;
use Livewire\Volt\Volt;
use App\Http\Controllers\TransferModule\Teacher\Reports\CurrentAppointmentDurationPdfController;

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Transfer Module
    |--------------------------------------------------------------------------
    */
    Route::get('transfer/index-teachers-module', IndexTeachersModule::class)
        ->name('transfer.index-module')
        ->middleware(['can:transfer.portal.view']);

    Volt::route(
        'transfer/reports/current-appointment-duration',
        'transfer-module.teacher.reports.current-appointment-duration-report'
    )
        ->name('transfer.reports.current-appointment-duration')
        ->middleware(['can:transfer.portal.view']);

    Route::get(
        'transfer/reports/current-appointment-duration/pdf',
        CurrentAppointmentDurationPdfController::class
    )
        ->name('transfer.reports.current-appointment-duration.pdf')
        ->middleware(['can:transfer.portal.view']);

    Route::get('transfer/transfer-policies', TeacherTransferPolicyList::class)
        ->name('transfer.transfer-policies')
        ->middleware(['can:transfer.policy.view']);

    Route::get('transfer/teachers-transfer-policy/{id}/view', TeacherTransferPolicyView::class)
        ->name('transfer.transfer-policy.view')
        ->middleware(['can:transfer.policy.view']);

    Route::get('transfer/teachers-transfer-policy/create', TeacherTransferPolicy::class)
        ->name('transfer.transfer-policy')
        ->middleware(['can:transfer.policy.manage']);

    Route::get('transfer/teachers-transfer-policy/{id}/edit', TeacherTransferPolicy::class)
        ->name('transfer.transfer-policy.edit')
        ->middleware(['can:transfer.policy.manage']);

    Route::get('transfer/teacher-transfer-request', TeacherTransferRequest::class)
        ->name('transfer.teacher-transfer-request')
        ->middleware(['can:transfer.request.view']);

    Route::get('transfer/teacher-policies/{policyId}/requests', TeacherTransferPolicyRequests::class)
        ->name('transfer.teacher-policy.requests')
        ->middleware(['can:transfer.teacher-self-service']);

    Route::get('transfer/announcements', AnnouncementManagement::class)
        ->name('transfer.announcements')
        ->middleware(['can:transfer.portal.view']);

    Route::get('transfer/teacher-transfer-application/{id}/view', TeacherTransferApplicationView::class)
        ->name('transfer.teacher-transfer-application.view');

    Route::get('transfer/teacher-transfer-application/{id}/download', [TeacherTransferApplicationPdfController::class, 'download'])
        ->name('transfer.teacher-transfer-application.download');

    Route::get('transfer/transfer-board/{boardId}/decision-report/download', [TeacherTransferBoardDecisionReportPdfController::class, 'download'])
        ->name('transfer.transfer-board.decision-report.download')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/transfer-board/{boardId}/appeal-report/download', [TeacherTransferBoardAppealReportPdfController::class, 'download'])
        ->name('transfer.transfer-board.appeal-report.download')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/transfer-board/{boardId}/school-balance-report/{type}/download', [TeacherTransferBoardSchoolBalanceReportPdfController::class, 'download'])
        ->name('transfer.transfer-board.school-balance-report.download')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/{id}/teacher-transfer-controller', TeacherTransferController::class)
        ->name('transfer.teacher-transfer-controller')
        ->middleware(['can:transfer.board.view']);


    /*
    |--------------------------------------------------------------------------
    | Teacher Transfer Potal
    |--------------------------------------------------------------------------
    */
    // Route::get('my-transfer/teacher-transfer-potal', TeacherTransferPotal::class)
    //     ->name('my-transfer.teacher-transfer-potal')
    //     ->middleware(['can:transfer.teacher-self-service']);

    Route::get('my-transfer/teacher-transfer-application/{id?}', TeacherTransferApplication::class)
        ->name('my-transfer.teacher-transfer-application')
        ->middleware(['can:transfer.teacher-self-service']);

    Route::get('my-transfer/teacher-annual-transfer', TeacherAnnualTransfer::class)
        ->name('my-transfer.teacher-annual-transfer')
        ->middleware(['can:transfer.teacher-self-service']);

    Route::get('my-transfer/teacher-mutual-transfer', TeacherMutualTransfer::class)
        ->name('my-transfer.teacher-mutual-transfer')
        ->middleware(['can:transfer.teacher-self-service']);

    Route::get('my-transfer/teacher-special-request', TeacherSpecialRequest::class)
        ->name('my-transfer.teacher-special-request')
        ->middleware(['can:transfer.teacher-self-service']);

    Route::get('my-transfer/teacher-transfer-guidelines', TeacherTransferGuidelines::class)
        ->name('my-transfer.teacher-transfer-guidelines')
        ->middleware(['can:transfer.teacher-self-service']);


    /*
    |--------------------------------------------------------------------------
    | Institution Transfer Requests
    |--------------------------------------------------------------------------
    */
    Route::get('offices/institutions/{id}/profile/institution-transfer-requests', TransferRequests::class)
        ->name('offices.institutions.institution-transfer-requests')
        ->middleware(['can:transfer.institution-request.view']);

    /*
    |--------------------------------------------------------------------------
    | Zone Transfer Requests
    |--------------------------------------------------------------------------
    */
    Route::get('offices/zeo/{id}/profile/teachers-transfer-requests', TeachersTransferRequests::class)
        ->name('offices.zeo.profile.teachers-transfer-requests')
        ->middleware(['can:transfer.zeo-request.view']);

    /*
    |--------------------------------------------------------------------------
    | Teacher Transfer Board
    |--------------------------------------------------------------------------
    */

    Route::get('transfer/province-teacher-transfer-board', ProvinceTeacherTransferBoard::class)
        ->name('transfer-board.province-teacher-transfer')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/province-teacher-appeal-board', ProvinceTeacherAppealBoard::class)
        ->name('transfer-board.province-teacher-appeal')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/provincial-ministry-teacher-transfer-board', ProvincialMinistryTeacherTransferBoard::class)
        ->name('transfer-board.provincial-ministry-teacher-transfer')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/provincial-ministry-teacher-appeal-board', ProvincialMinistryTeacherAppealBoard::class)
        ->name('transfer-board.provincial-ministry-teacher-appeal')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/zone-teacher-transfer-board', ZoneTeacherTransferBoard::class)
        ->name('transfer-board.zone-teacher-transfer')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/zone-teacher-appeal-board', ZoneTeacherAppealBoard::class)
        ->name('transfer-board.zone-teacher-appeal')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/national-teacher-transfer-board', NationalTeacherTransferBoard::class)
        ->name('transfer-board.national-teacher-transfer')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/teacher-profile-for-transfer-board/{applicationId}/institution/{institutionId}', InstitutionProfileForTeacherTransferBoard::class)
        ->name('transfer-board.institution-profile-for-transfer-board')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/teacher-profile-for-transfer-board/{id}', TeacherProfileForTeacherTransferBoard::class)
        ->name('transfer-board.teacher-profile-for-transfer-board')
        ->middleware(['can:transfer.board.view']);

    Route::get('transfer/teacher-profile-for-appeal-board/{id}', TeacherProfileForAppealBoard::class)
        ->name('transfer-board.teacher-profile-for-appeal-board')
        ->middleware(['can:transfer.board.view']);
});
