<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Documentation extends Component
{
    public string $content = '';

    public string $title = '';

    public array $pages = [];

    public string $currentPage = '';

    /**
     * @throws FileNotFoundException
     */
    public function mount(?string $page = null): void
    {
        $this->pages = $this->getAvailablePages();

        $page ??= array_key_first($this->pages) ?? 'ARCHITECTURE';

        if (! in_array($page, array_keys($this->pages))) {
            throw new NotFoundHttpException;
        }

        $this->currentPage = $page;
        $this->loadPage($page);
    }

    protected function getAvailablePages(): array
    {
        if (! File::isDirectory(base_path('docs'))) {
            return [];
        }

        $files = File::files(base_path('docs'));
        $pages = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'md') {
                $name = $file->getFilenameWithoutExtension();
                $pages[$name] = Str::headline($name);
            }
        }

        return $pages;
    }

    /**
     * @throws FileNotFoundException
     */
    public function loadPage(string $page): void
    {
        $path = base_path("docs/{$page}.md");

        if (! File::exists($path)) {
            throw new NotFoundHttpException;
        }

        $this->content = Str::markdown(File::get($path));
        $this->title = $this->pages[$page] ?? Str::headline($page);
        $this->currentPage = $page;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.documentation')->title(__('Documentation'));
    }
}
