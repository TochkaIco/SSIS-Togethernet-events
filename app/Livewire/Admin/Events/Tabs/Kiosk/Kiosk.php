<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Events\Tabs\Kiosk;

use App\Models\Event;
use App\Models\EventKioskArticle;
use App\Models\EventKioskCategory;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Kiosk extends Component
{
    use WithPagination;

    public Event $event;

    #[Url]
    public string $subTab = 'articles';

    public bool $showArticleModal = false;

    public bool $showCategoryModal = false;

    public bool $showImportModal = false;

    public ?int $editingArticleId = null;

    public ?int $editingCategoryId = null;

    public string $articleName = '';

    public ?int $articleCategoryId = null;

    public int $articleCost = 0;

    public int $articleAmount = 0;

    public string $articleImageUrl = '';

    public string $categoryName = '';

    public ?int $importEventId = null;

    public array $cart = [];

    public ?int $selectedPurchaseId = null;

    public ?int $deletingArticleId = null;

    public ?int $deletingCategoryId = null;

    public ?int $deletingPurchaseId = null;

    public ?int $selectedSellCategoryId = null;

    protected $listeners = [
        'refreshKiosk' => '$refresh',
    ];

    public function mount(): void
    {
        $this->authorize('manage kiosk');
    }

    public function setSubTab(string $tab): void
    {
        $this->subTab = $tab;
        $this->resetPage();
    }

    public function createKiosk(): void
    {
        $this->authorize('manage kiosk');

        $this->event->kiosk()->create();
        Flux::toast(__('Kiosk created successfully.'));
        $this->dispatch('refreshKiosk');
    }

    public function openArticleModal(?int $articleId = null): void
    {
        $this->authorize('manage kiosk');

        $this->reset(['articleName', 'articleCategoryId', 'articleCost', 'articleAmount', 'articleImageUrl']);
        $this->editingArticleId = $articleId;

        if ($articleId) {
            $article = $this->event->kiosk?->articles()->find($articleId);
            if ($article) {
                $this->articleName = $article->name;
                $this->articleCategoryId = $article->category_id;
                $this->articleCost = $article->cost;
                $this->articleAmount = $article->amount;
                $this->articleImageUrl = $article->image_url ?? '';
            }
        }

        $this->showArticleModal = true;
    }

    public function saveArticle(): void
    {
        $this->authorize('manage kiosk');

        $kiosk = $this->event->kiosk;
        if (! $kiosk) {
            Flux::toast(__('No kiosk found.'), variant: 'warning');

            return;
        }

        $this->validate([
            'articleName' => 'required|string|max:255',
            'articleCategoryId' => [
                'nullable',
                Rule::exists('event_kiosk_categories', 'id')->where(function ($query) use ($kiosk) {
                    $query->where('kiosk_id', $kiosk->id);
                }),
            ],
            'articleCost' => 'required|integer|min:0',
            'articleAmount' => 'required|integer|min:0',
            'articleImageUrl' => 'nullable|url|string|max:500',
        ]);

        if ($this->editingArticleId) {
            $kiosk->articles()->findOrFail($this->editingArticleId)->update([
                'name' => $this->articleName,
                'category_id' => $this->articleCategoryId,
                'cost' => $this->articleCost,
                'amount' => $this->articleAmount,
                'image_url' => $this->articleImageUrl ?: null,
            ]);
            Flux::toast(__('Article updated successfully.'));
        } else {
            $kiosk->articles()->create([
                'name' => $this->articleName,
                'category_id' => $this->articleCategoryId,
                'cost' => $this->articleCost,
                'amount' => $this->articleAmount,
                'image_url' => $this->articleImageUrl ?: null,
            ]);
            Flux::toast(__('Article created successfully.'));
        }

        $this->showArticleModal = false;
        $this->dispatch('refreshKiosk');
    }

    public function confirmDeleteArticle(int $articleId): void
    {
        $this->authorize('manage kiosk');

        $this->deletingArticleId = $articleId;
        $this->modal('delete-article-modal')->show();
    }

    public function deleteArticle(): void
    {
        $this->authorize('manage kiosk');

        if ($this->deletingArticleId) {
            $this->event->kiosk?->articles()->findOrFail($this->deletingArticleId)->delete();
            Flux::toast(__('Article deleted.'));
            $this->deletingArticleId = null;
            $this->modal('delete-article-modal')->close();
            $this->dispatch('refreshKiosk');
        }
    }

    public function openCategoryModal(?int $categoryId = null): void
    {
        $this->authorize('manage kiosk');

        $this->reset(['categoryName']);
        $this->editingCategoryId = $categoryId;

        if ($categoryId) {
            $category = $this->event->kiosk?->categories()->find($categoryId);
            if ($category) {
                $this->categoryName = $category->name;
            }
        }

        $this->showCategoryModal = true;
    }

    public function saveCategory(): void
    {
        $this->authorize('manage kiosk');

        $kiosk = $this->event->kiosk;
        if (! $kiosk) {
            Flux::toast(__('No kiosk found.'), variant: 'warning');

            return;
        }

        $this->validate([
            'categoryName' => 'required|string|max:255',
        ]);

        if ($this->editingCategoryId) {
            EventKioskCategory::findOrFail($this->editingCategoryId)->update([
                'name' => $this->categoryName,
            ]);
            Flux::toast(__('Category updated.'));
        } else {
            $kiosk->categories()->create([
                'name' => $this->categoryName,
            ]);
            Flux::toast(__('Category created.'));
        }

        $this->showCategoryModal = false;
        $this->dispatch('refreshKiosk');
    }

    public function confirmDeleteCategory(int $categoryId): void
    {
        $this->authorize('manage kiosk');

        $this->deletingCategoryId = $categoryId;
        $this->modal('delete-category-modal')->show();
    }

    public function deleteCategory(): void
    {
        $this->authorize('manage kiosk');

        $kiosk = $this->event->kiosk;
        if (! $kiosk || ! $this->deletingCategoryId) {
            Flux::toast(__('No kiosk found or no category selected.'), variant: 'warning');

            return;
        }

        $category = $kiosk->categories()->findOrFail($this->deletingCategoryId);

        if ($category->articles()->exists()) {
            Flux::toast(__('Cannot delete category with articles.'), variant: 'warning');
            $this->deletingCategoryId = null;
            $this->modal('delete-category-modal')->close();

            return;
        }

        $category->delete();
        Flux::toast(__('Category deleted.'));
        $this->deletingCategoryId = null;
        $this->modal('delete-category-modal')->close();
        $this->dispatch('refreshKiosk');
    }

    public function openImportModal(): void
    {
        $this->authorize('manage kiosk');
        $this->reset(['importEventId']);
        $this->showImportModal = true;
    }

    /**
     * @throws \Throwable
     */
    public function importFromEvent(): void
    {
        $this->authorize('manage kiosk');

        $sourceEvent = Event::findOrFail($this->importEventId);

        if (! $sourceEvent->kiosk) {
            Flux::toast(__('Source event has no kiosk.'), variant: 'warning');

            return;
        }

        $kiosk = $this->event->kiosk;
        if (! $kiosk) {
            Flux::toast(__('No kiosk found.'), variant: 'warning');

            return;
        }

        DB::transaction(function () use ($sourceEvent, $kiosk) {
            $categoryMapping = [];

            // Get all categories linked to articles in the source kiosk
            $sourceCategories = $sourceEvent->kiosk->articles->pluck('category')->filter()->unique('id');

            foreach ($sourceCategories as $sourceCategory) {
                $newCategory = $kiosk->categories()->create([
                    'name' => $sourceCategory->name,
                ]);
                $categoryMapping[$sourceCategory->id] = $newCategory->id;
            }

            foreach ($sourceEvent->kiosk->articles as $sourceArticle) {
                $kiosk->articles()->create([
                    'name' => $sourceArticle->name,
                    'category_id' => $sourceArticle->category_id ? ($categoryMapping[$sourceArticle->category_id] ?? null) : null,
                    'cost' => $sourceArticle->cost,
                    'amount' => $sourceArticle->amount,
                    'image_url' => $sourceArticle->image_url,
                ]);
            }
        });

        Flux::toast(__('Kiosk imported successfully.'));
        $this->showImportModal = false;
        $this->dispatch('refreshKiosk');
    }

    public function addToCart(int $articleId): void
    {
        $this->authorize('manage kiosk');

        $article = $this->event->kiosk?->articles()->find($articleId);

        if (! $article) {
            Flux::toast(__('Article not found.'), variant: 'warning');

            return;
        }

        $currentInCart = $this->cart[$articleId] ?? 0;

        if ($currentInCart >= $article->amount) {
            Flux::toast(__('Not enough stock.'), variant: 'warning');

            return;
        }

        $this->cart[$articleId] = $currentInCart + 1;
    }

    /**
     * @throws \Throwable
     */
    public function recordPurchase(): void
    {
        $this->authorize('manage kiosk');

        $kiosk = $this->event->kiosk;
        if (! $kiosk) {
            Flux::toast(__('No kiosk found.'), variant: 'warning');

            return;
        }

        if (array_filter($this->cart) === []) {
            Flux::toast(__('Cart is empty.'), variant: 'warning');

            return;
        }

        $totalCost = 0;
        $toProcess = [];

        foreach ($this->cart as $articleId => $quantity) {
            if ($quantity > 0) {
                $article = $kiosk->articles()->find($articleId);
                if (! $article) {
                    continue;
                }

                if ($article->amount < $quantity) {
                    Flux::toast(__('Not enough stock for :name.', ['name' => $article->name]), variant: 'warning');

                    return;
                }

                $totalCost += $article->cost * $quantity;
                $toProcess[] = [
                    'article' => $article,
                    'quantity' => $quantity,
                ];
            }
        }

        DB::transaction(function () use ($kiosk, $totalCost, $toProcess) {
            $purchase = $kiosk->purchases()->create([
                'kiosk_id' => $kiosk->id,
                'operator_id' => auth()->id(),
                'cost' => $totalCost,
            ]);

            foreach ($toProcess as $item) {
                /** @var EventKioskArticle $article */
                $article = $item['article'];
                $quantity = $item['quantity'];

                $purchase->items()->create([
                    'article_id' => $article->id,
                    'amount' => $quantity,
                    'cost' => $article->cost * $quantity,
                ]);

                $article->decrement('amount', $quantity);
            }
        });

        Flux::toast(__('Purchase recorded.'));
        $this->cart = [];
        $this->dispatch('refreshKiosk');
    }

    public function viewPurchase(int $purchaseId): void
    {
        $this->selectedPurchaseId = $purchaseId;
    }

    public function confirmDeletePurchase(int $purchaseId): void
    {
        $this->authorize('manage kiosk');

        $this->deletingPurchaseId = $purchaseId;
        $this->modal('delete-purchase-modal')->show();
    }

    /**
     * @throws \Throwable
     */
    public function deletePurchase(): void
    {
        $this->authorize('manage kiosk');

        if ($this->deletingPurchaseId) {
            $purchase = $this->event->kiosk?->purchases()->findOrFail($this->deletingPurchaseId);

            DB::transaction(function () use ($purchase) {
                foreach ($purchase->items as $item) {
                    $item->article?->increment('amount', $item->amount);
                }

                $purchase->delete();
            });

            Flux::toast(__('Purchase deleted and stock restored.'));
            $this->deletingPurchaseId = null;
            $this->modal('delete-purchase-modal')->close();
            $this->dispatch('refreshKiosk');
        }
    }

    public function render()
    {
        $kiosk = $this->event->kiosk;

        $categories = $kiosk->categories ?? collect();

        $sellCategories = $categories->whereIn('id', $kiosk?->articles->pluck('category_id')->filter()->unique() ?? []);

        $articles = $kiosk?->articles()
            ->when($this->selectedSellCategoryId, fn ($query) => $query->where('category_id', $this->selectedSellCategoryId))
            ->get() ?? collect();

        $purchases = $kiosk?->purchases()
            ->with(['operator', 'items.article'])
            ->orderBy('created_at', 'desc')
            ->paginate(15) ?? collect();

        $totalEarned = $kiosk?->purchases()->sum('cost') ?? 0;

        $availableEvents = Event::where('id', '!=', $this->event->id)
            ->whereHas('kiosk')
            ->orderBy('title')
            ->get();

        return view('livewire.admin.events.tabs.kiosk.kiosk', [
            'kiosk' => $kiosk,
            'categories' => $categories,
            'sellCategories' => $sellCategories,
            'articles' => $articles,
            'purchases' => $purchases,
            'totalEarned' => $totalEarned,
            'availableEvents' => $availableEvents,
        ]);
    }
}
