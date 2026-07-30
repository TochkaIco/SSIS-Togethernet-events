<?php

declare(strict_types=1);

use App\Jobs\BackupMeetingToGoogleDrive;
use App\Services\GoogleDriveService;
use Mockery\Expectation;
use Mockery\MockInterface;

use function Pest\Laravel\mock;

test('it calls backupToDocs on GoogleDriveService when handled', function () {
    $html = '<p>Test Meeting Protocol</p>';
    $title = 'Meeting Title';
    $startsAt = '2026-07-30T17:00:00';

    /** @var GoogleDriveService&MockInterface $googleDriveServiceMock */
    $googleDriveServiceMock = mock(GoogleDriveService::class);

    /** @var Expectation $expectation */
    $expectation = $googleDriveServiceMock->shouldReceive('backupToDocs');
    $expectation->once()
        ->with($html, $title, $startsAt)
        ->andReturn(null);

    $job = new BackupMeetingToGoogleDrive($html, $title, $startsAt);
    $job->handle($googleDriveServiceMock);
});
