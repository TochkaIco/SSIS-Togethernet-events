<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\GoogleDriveService;
use Google\Service\Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BackupMeetingToGoogleDrive implements ShouldQueue
{
    use Queueable;

    protected $html;

    protected $title;

    protected $meeting_starts_at;

    public function __construct($html, $title, $meeting_starts_at)
    {
        $this->html = $html;
        $this->title = $title;
        $this->meeting_starts_at = $meeting_starts_at;
    }

    /**
     * @throws Exception
     */
    public function handle(GoogleDriveService $service): void
    {
        $service->backupToDocs($this->html, $this->title, $this->meeting_starts_at);
    }
}
