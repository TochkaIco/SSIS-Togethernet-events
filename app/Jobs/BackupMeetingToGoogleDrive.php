<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\GoogleDriveService;
use Google\Service\Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BackupMeetingToGoogleDrive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $html;

    protected $title;

    public function __construct($html, $title)
    {
        $this->html = $html;
        $this->title = $title;
    }

    /**
     * @throws Exception
     */
    public function handle(GoogleDriveService $service): void
    {
        $service->backupToDocs($this->html, $this->title);
    }
}
