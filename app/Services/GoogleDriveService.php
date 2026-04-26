<?php

declare(strict_types=1);

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Exception;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class GoogleDriveService
{
    protected Drive $driveService;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $fileName = config('filesystems.disks.google.credentials_file');
        $path = storage_path('app/'.$fileName);

        if (! file_exists($path)) {
            throw new \Exception("Credentials file MISSING at: $path");
        }

        if (is_dir($path)) {
            throw new \Exception("PATH IS A DIRECTORY, NOT A FILE: $path. Please delete the folder at this path and replace it with your JSON file.");
        }

        $client = new Client;
        $client->setAuthConfig($path);
        $client->addScope(Drive::DRIVE);

        $this->driveService = new Drive($client);
    }

    /**
     * @throws Exception
     */
    public function backupToDocs(string $quillHtml, string $title)
    {
        $quillHtml = preg_replace_callback('/<img([^>]+)>/', function (array $matches): string {
            $tag = $matches[1];
            $width = '250'; // Default to half page width

            // Check for Quill's style-based width (e.g., style="width: 50%;")
            if (preg_match('/width:\s*(\d+)%/', $tag, $styleMatch)) {
                $width = round(550 * ($styleMatch[1] / 100));
            }
            // Check for standard width attribute (e.g., width="300")
            elseif (preg_match('/width="(\d+)"/', $tag, $attrMatch)) {
                $width = min($attrMatch[1], 550);
            }

            // Extract only the SRC (ignore everything else to prevent the "ghost box")
            if (preg_match('/src="([^"]+)"/', $tag, $srcMatch)) {
                return '<img src="'.$srcMatch[1].'" width="'.$width.'">';
            }

            return $matches[0];
        }, $quillHtml);

        // Define CSS
        $quillCss = '
            .ql-align-center { text-align: center; }
            .ql-align-right { text-align: right; }
            .ql-align-justify { text-align: justify; }
            strong { font-weight: bold; }
            em { font-style: italic; }
            u { text-decoration: underline; }

            /* Lists */
            ul {
                list-style-type: disc;
                margin-top: 8pt;
                margin-bottom: 8pt;
                margin-left: 15pt;
                color: #52525b; /* zinc-600 */
            }
            ol {
                list-style-type: decimal;
                margin-top: 8pt;
                margin-bottom: 8pt;
                margin-left: 15pt;
                color: #52525b;
            }
            li {
                margin-bottom: 4pt;
            }

            /* Handling Quill-specific list attributes */
            li[data-list="bullet"] { list-style-type: disc; }
            li[data-list="ordered"] { list-style-type: decimal; }

            /* Blockquote */
            blockquote {
                border-left: 4px solid #d4d4d8; /* zinc-300 */
                padding-left: 16px;
                font-style: italic;
                color: #71717a; /* zinc-500 */
                margin-top: 12pt;
                margin-bottom: 12pt;
            }
        ';

        // Convert to Inline
        $inliner = new CssToInlineStyles;
        $finalHtml = $inliner->convert("<html><body>{$quillHtml}</body></html>", $quillCss);

        $rootFolderId = config('filesystems.disks.google.folder_id');
        $yearFolderName = date('Y');
        $dateFolderName = date('F j, Y');

        $yearFolderId = $this->getOrCreateFolder($yearFolderName, $rootFolderId);
        $destinationFolderId = $this->getOrCreateFolder($dateFolderName, $yearFolderId);

        // Search for an existing file with the same title in this specific folder
        $query = "name = '".str_replace("'", "\'", $title)."' and '{$destinationFolderId}' in parents and trashed = false";
        $results = $this->driveService->files->listFiles([
            'q' => $query,
            'fields' => 'files(id, name)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        $existingFiles = $results->getFiles();

        if (count($existingFiles) > 0) {
            // Update existing file
            $fileId = $existingFiles[0]->getId();

            return $this->driveService->files->update($fileId, new Drive\DriveFile, [
                'data' => $finalHtml,
                'mimeType' => 'text/html',
                'uploadType' => 'multipart',
                'supportsAllDrives' => true,
            ]);
        }
        // Create new file
        $fileMetadata = new Drive\DriveFile([
            'name' => $title,
            'mimeType' => 'application/vnd.google-apps.document',
            'parents' => [$destinationFolderId],
        ]);

        return $this->driveService->files->create($fileMetadata, [
            'data' => $finalHtml,
            'mimeType' => 'text/html',
            'uploadType' => 'multipart',
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);
    }

    /**
     * @throws Exception
     */
    private function getOrCreateFolder(string $name, string $parentId): string
    {
        $query = "name = '{$name}' and '{$parentId}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false";

        $results = $this->driveService->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true,
        ]);

        $files = $results->getFiles();

        if (count($files) > 0) {
            return $files[0]->getId();
        }

        // Create the folder if not found
        $folderMetadata = new Drive\DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$parentId],
        ]);

        $folder = $this->driveService->files->create($folderMetadata, [
            'fields' => 'id',
            'supportsAllDrives' => true,
        ]);

        return $folder->id;
    }

    public function getDriveService(): Drive
    {
        return $this->driveService;
    }
}
