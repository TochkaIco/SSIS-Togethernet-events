<div class="md:min-w-5xl space-y-6" x-data="{ showArticleModal: false, showCategoryModal: false, showImportModal: false }">
    @if(!$kiosk)
        <div class="flex flex-col items-center justify-center py-16 gap-6">
            <flux:icon.shopping-bag class="size-16 text-muted-foreground" />
            <p class="text-muted-foreground text-lg">No kiosk configured for this event.</p>
            @can('manage kiosk')
                <flux:button variant="primary" wire:click="createKiosk" class="cursor-pointer">
                    {{ __('Create Kiosk') }}
                </flux:button>
            @endcan
        </div>
    @else
        <div class="flex items-center justify-between">
            <flux:navbar class="-mb-px">
                <flux:navbar.item wire:click="setSubTab('articles')" :current="$subTab === 'articles'" class="cursor-pointer">
                    {{ __('Articles') }}
                </flux:navbar.item>
                <flux:navbar.item wire:click="setSubTab('categories')" :current="$subTab === 'categories'" class="cursor-pointer">
                    {{ __('Categories') }}
                </flux:navbar.item>
                <flux:navbar.item wire:click="setSubTab('transactions')" :current="$subTab === 'transactions'" class="cursor-pointer">
                    {{ __('Transactions') }}
                </flux:navbar.item>
                <flux:navbar.item wire:click="setSubTab('sell')" :current="$subTab === 'sell'" class="cursor-pointer">
                    {{ __('Sell') }}
                </flux:navbar.item>
            </flux:navbar>

            <div class="flex gap-2">
                @can('manage kiosk')
                    @if($kiosk->articles->isEmpty() && $kiosk->categories->isEmpty())
                        <flux:button variant="ghost" wire:click="openImportModal" class="cursor-pointer">
                            <flux:icon.arrow-path class="size-4 mr-1" />
                            {{ __('Import') }}
                        </flux:button>
                    @endif
                @endcan
            </div>
        </div>

        @if($subTab === 'articles')
            <div class="space-y-4">
                <div class="flex justify-end">
                    @can('manage kiosk')
                        <flux:button icon="plus" wire:click="openArticleModal" class="cursor-pointer">
                            {{ __('Add Article') }}
                        </flux:button>
                    @endcan
                </div>

                @if($articles->isEmpty())
                    <flux:callout variant="subtle" icon="information-circle">
                        {{ __('No articles yet. Add some to get started.') }}
                    </flux:callout>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($articles as $article)
                            <div class="relative border rounded-lg p-4 h-44 flex flex-col justify-between overflow-hidden group">
                                @if($article->image_url)
                                    <div class="absolute inset-0 z-0">
                                        <img src="{{ $article->image_url }}" alt="{{ $article->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                        <div class="absolute inset-0 bg-black/60 dark:bg-black/80"></div>
                                    </div>
                                @endif

                                <div class="relative z-10 flex justify-between items-start {{ $article->image_url ? 'text-white' : '' }}">
                                    <div>
                                        <h3 class="font-semibold text-lg">{{ $article->name }}</h3>
                                        @if($article->category)
                                            <span class="text-xs {{ $article->image_url ? 'text-zinc-300' : 'text-muted-foreground' }}">{{ $article->category->name }}</span>
                                        @endif
                                    </div>
                                    <span class="font-bold text-xl">{{ number_format($article->cost) }} kr</span>
                                </div>

                                <div class="relative z-10 flex justify-between items-center text-sm">
                                    <span class="px-2 py-0.5 rounded-full {{ $article->image_url ? 'bg-white/10 backdrop-blur-md' : 'bg-zinc-100 dark:bg-zinc-800' }} {{ $article->amount > 0 ? ($article->image_url ? 'text-green-300' : 'text-green-600') : 'text-red-500' }}">
                                        {{ __(':amount in stock', ['amount' => $article->amount]) }}
                                    </span>
                                    @can('manage kiosk')
                                        <div class="flex gap-1">
                                            <flux:button size="xs" variant="ghost" wire:click="openArticleModal({{ $article->id }})" class="cursor-pointer {{ $article->image_url ? 'text-white hover:bg-white/20' : '' }}">
                                                <flux:icon.pencil class="size-3" />
                                            </flux:button>
                                            <flux:button size="xs" variant="ghost" wire:click="confirmDeleteArticle({{ $article->id }})" class="cursor-pointer {{ $article->image_url ? 'text-white hover:bg-white/20' : '' }}">
                                                <flux:icon.trash class="size-3 {{ $article->image_url ? 'text-red-400' : 'text-red-500' }}" />
                                            </flux:button>
                                        </div>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @elseif($subTab === 'categories')
            <div class="space-y-4">
                <div class="flex justify-end">
                    @can('manage kiosk')
                        <flux:button icon="plus" wire:click="openCategoryModal" class="cursor-pointer">
                            {{ __('Add Category') }}
                        </flux:button>
                    @endcan
                </div>

                @if($categories->isEmpty())
                    <flux:callout variant="subtle" icon="information-circle">
                        {{ __('No categories yet. Add some to organize your articles.') }}
                    </flux:callout>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Name') }}</flux:table.column>
                            <flux:table.column>{{ __('Articles') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($categories as $category)
                                <flux:table.row :key="$category->id">
                                    <flux:table.cell>{{ $category->name }}</flux:table.cell>
                                    <flux:table.cell>{{ $category->articles->count() }}</flux:table.cell>
                                    <flux:table.cell align="end">
                                        @can('manage kiosk')
                                            <div class="flex gap-1 justify-end">
                                                <flux:button size="xs" class="cursor-pointer" variant="ghost" wire:click="openCategoryModal({{ $category->id }})">
                                                    <flux:icon.pencil class="size-3" />
                                                </flux:button>
                                                <flux:button size="xs" class="cursor-pointer" variant="ghost" wire:click="confirmDeleteCategory({{ $category->id }})">
                                                    <flux:icon.trash class="size-3 text-red-500" />
                                                </flux:button>
                                            </div>
                                        @endcan
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        @elseif($subTab === 'transactions')
            <div class="space-y-4">
                <div class="flex justify-end items-center space-x-3 bg-muted/50 p-4 rounded-lg">
                    <span class="font-medium text-muted-foreground">{{ __('Total Revenue:') }}</span>
                    <span class="text-2xl font-bold">{{ number_format($totalEarned) }} kr</span>
                </div>

                @if($purchases->isEmpty())
                    <flux:callout variant="subtle" icon="information-circle">
                        {{ __('No transactions recorded yet.') }}
                    </flux:callout>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Time') }}</flux:table.column>
                            <flux:table.column>{{ __('Operator') }}</flux:table.column>
                            <flux:table.column>{{ __('Items') }}</flux:table.column>
                            <flux:table.column>{{ __('Total') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($purchases as $purchase)
                                <flux:table.row :key="$purchase->id">
                                    <flux:table.cell>{{ $purchase->created_at->format('d/m/Y H:i') }}</flux:table.cell>
                                    <flux:table.cell>{{ $purchase->operator?->name ?? '—' }}</flux:table.cell>
                                    <flux:table.cell>{{ $purchase->items->sum('amount') }}</flux:table.cell>
                                    <flux:table.cell>{{ number_format($purchase->cost) }} kr</flux:table.cell>
                                    <flux:table.cell align="end">
                                        <div class="flex gap-x-1 justify-end">
                                            <flux:button size="xs" class="cursor-pointer" icon="eye" variant="ghost" wire:click="viewPurchase({{ $purchase->id }})" x-on:click="$flux.modal('purchase-modal').show()" />
                                            <flux:button size="xs" class="cursor-pointer" icon="trash" variant="ghost" wire:click="confirmDeletePurchase({{ $purchase->id }})" />
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                    <div class="flex justify-center">
                        {{ $purchases->links() }}
                    </div>
                @endif
            </div>
        @elseif($subTab === 'sell')
            <div class="space-y-6">
                <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
                    <h3 class="font-semibold text-lg">{{ __('Select Items') }}</h3>
                    <div class="w-full sm:w-64">
                        <flux:select wire:model.live="selectedSellCategoryId">
                            <flux:select.option value="">{{ __('All Categories') }}</flux:select.option>
                            @foreach($sellCategories as $category)
                                <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @forelse($articles as $article)
                                <div class="relative border rounded-lg p-4 h-44 flex flex-col justify-between overflow-hidden group {{ $article->amount === 0 ? 'opacity-60' : '' }}">
                                    @if($article->image_url)
                                        <div class="absolute inset-0 z-0">
                                            <img src="{{ $article->image_url }}" alt="{{ $article->name }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                                            <div class="absolute inset-0 bg-black/60 dark:bg-black/80"></div>
                                        </div>
                                    @endif

                                    <div class="relative z-10 flex justify-between items-start {{ $article->image_url ? 'text-white' : '' }}">
                                        <div>
                                            <h3 class="font-semibold text-lg">{{ $article->name }}</h3>
                                            @if($article->category)
                                                <span class="text-xs {{ $article->image_url ? 'text-zinc-300' : 'text-muted-foreground' }}">{{ $article->category->name }}</span>
                                            @endif
                                        </div>
                                        <span class="font-bold text-xl">{{ number_format($article->cost) }} kr</span>
                                    </div>

                                    <div class="relative z-10 flex justify-between items-center text-sm">
                                        <span class="px-2 py-0.5 rounded-full {{ $article->image_url ? 'bg-white/10 backdrop-blur-md' : 'bg-zinc-100 dark:bg-zinc-800' }} {{ $article->amount > 0 ? ($article->image_url ? 'text-green-300' : 'text-green-600') : 'text-red-500' }}">
                                            {{ __(':amount in stock', ['amount' => $article->amount]) }}
                                        </span>

                                        @if($article->amount > 0)
                                            <flux:button icon="plus" size="sm" variant="outline" wire:click="addToCart({{ $article->id }})" :disabled="($cart[$article->id] ?? 0) >= $article->amount" class="cursor-pointer {{ $article->image_url ? 'text-white border-white/30 hover:bg-white/20' : '' }}" />
                                        @else
                                            <flux:badge variant="danger" size="sm">{{ __('Out of Stock') }}</flux:badge>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="md:col-span-2">
                                    <flux:callout variant="subtle">{{ __('No articles available.') }}</flux:callout>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="border rounded-lg p-4 space-y-4 h-fit sticky top-4">
                        <h3 class="font-semibold text-lg">{{ __('Cart') }}</h3>
                        @php
                            $cartTotal = 0;
                            $cartCount = 0;
                        @endphp
                        @forelse($cart as $articleId => $quantity)
                            @if($quantity > 0)
                                @php
                                    $article = $articles->find($articleId);
                                    if ($article) {
                                        $cartTotal += $article->cost * $quantity;
                                        $cartCount += $quantity;
                                    }
                                @endphp
                                <div class="flex justify-between items-center">
                                    <div>
                                        <span class="font-medium">{{ $article?->name }}</span>
                                        <span class="text-sm text-muted-foreground"> x {{ $quantity }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">{{ number_format(($article?->cost ?? 0) * $quantity) }} kr</span>
                                        <flux:button size="xs" class="cursor-pointer" variant="ghost" wire:click="$set('cart.{{ $articleId }}', 0)">
                                            <flux:icon.x-mark class="size-3" />
                                        </flux:button>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <p class="text-muted-foreground text-sm">{{ __('Cart is empty.') }}</p>
                        @endforelse

                        @if($cartCount > 0)
                            <flux:separator />
                            <div class="flex justify-between items-center">
                                <span class="font-semibold">Total</span>
                                <span class="font-bold text-xl">{{ number_format($cartTotal) }} kr</span>
                            </div>
                            <flux:button variant="primary" class="w-full cursor-pointer" wire:click="recordPurchase">
                                {{ __('Complete Purchase') }}
                            </flux:button>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    <flux:modal name="article-modal" wire:model="showArticleModal" class="max-w-md w-full">
        <form wire:submit="saveArticle">
            <flux:heading size="lg">{{ $editingArticleId ? __('Edit Article') : __('Add Article') }}</flux:heading>
            <flux:separator class="my-4" />
            <div class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('Name') }}</flux:label>
                    <flux:input wire:model="articleName" required />
                    <flux:error name="articleName" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Category') }}</flux:label>
                    <flux:select required wire:model="articleCategoryId">
                        <flux:select.option value="">{{ __('No category') }}</flux:select.option>
                        @foreach($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
                <div class="grid grid-cols-2 gap-4">
                    <flux:field>
                        <flux:label>{{ __('Price (kr)') }}</flux:label>
                        <flux:input type="number" wire:model="articleCost" min="0" required />
                        <flux:error name="articleCost" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('Stock') }}</flux:label>
                        <flux:input type="number" wire:model="articleAmount" min="0" required />
                        <flux:error name="articleAmount" />
                    </flux:field>
                </div>
                <flux:field>
                    <flux:label>{{ __('Image URL') }}</flux:label>
                    <flux:input type="url" wire:model="articleImageUrl" placeholder="https://..." />
                    <flux:error name="articleImageUrl" />
                </flux:field>
            </div>
            <flux:separator class="my-4" />
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" class="cursor-pointer" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="category-modal" wire:model="showCategoryModal" class="max-w-sm w-full">
        <form wire:submit="saveCategory">
            <flux:heading size="lg">{{ $editingCategoryId ? __('Edit Category') : __('Add Category') }}</flux:heading>
            <flux:separator class="my-4" />
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input wire:model="categoryName" required />
                <flux:error name="categoryName" />
            </flux:field>
            <flux:separator class="my-4" />
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button class="cursor-pointer" variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" class="cursor-pointer" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="import-modal" wire:model="showImportModal" class="max-w-sm w-full">
        <form wire:submit="importFromEvent">
            <flux:heading size="lg">{{ __('Import from Event') }}</flux:heading>
            <flux:subheading>{{ __('Copy articles and categories from another event.') }}</flux:subheading>
            <flux:separator class="my-4" />
            <flux:field>
                <flux:label>{{ __('Source Event') }}</flux:label>
                <flux:select wire:model="importEventId" required>
                    <flux:select.option value="">{{ __('Select an event...') }}</flux:select.option>
                    @foreach($availableEvents as $event)
                        <flux:select.option value="{{ $event->id }}">{{ $event->title }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="importEventId" />
            </flux:field>
            <flux:separator class="my-4" />
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="cursor-pointer">{{ __('Import') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    @if($selectedPurchaseId)
        @php
            $purchase = $kiosk->purchases()->with(['operator', 'items.article'])->find($selectedPurchaseId);
        @endphp
        <flux:modal name="purchase-modal" class="max-w-md w-full">
            <flux:heading size="lg">{{ __('Transaction Details') }}</flux:heading>
            <flux:separator class="my-4" />
            @if($purchase)
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">{{ __('Time') }}</span>
                            <p class="font-medium">{{ $purchase->created_at->format('d/m/Y H:i:s') }}</p>
                        </div>
                        <div>
                            <span class="text-muted-foreground">{{ __('Operator') }}</span>
                            <p class="font-medium">{{ $purchase->operator?->name ?? '—' }}</p>
                        </div>
                    </div>
                    <flux:separator />
                    <div>
                        <h4 class="font-semibold mb-2">{{ __('Items') }}</h4>
                        @foreach($purchase->items as $item)
                            <div class="flex justify-between text-sm py-1">
                                <span>{{ $item->article?->name ?? 'Deleted article' }} x {{ $item->amount }}</span>
                                <span class="font-medium">{{ number_format($item->cost) }} kr</span>
                            </div>
                        @endforeach
                    </div>
                    <flux:separator />
                    <div class="flex justify-between items-center">
                        <span class="font-semibold">{{ __('Total') }}</span>
                        <span class="font-bold text-xl">{{ number_format($purchase->cost) }} kr</span>
                    </div>
                </div>
            @endif
            <flux:separator class="my-4" />
            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </flux:modal>
    @endif

    <flux:modal name="delete-article-modal" class="min-w-[22rem]">
        <form wire:submit="deleteArticle">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete article?') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __("You're about to delete this article. This action cannot be reversed.") }}
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="danger" class="cursor-pointer">{{ __('Delete article') }}</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-category-modal" class="min-w-[22rem]">
        <form wire:submit="deleteCategory">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete category?') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __("You're about to delete this category. This action cannot be reversed.") }}
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="danger" class="cursor-pointer">{{ __('Delete category') }}</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-purchase-modal" class="min-w-[22rem]">
        <form wire:submit="deletePurchase">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ __('Delete purchase?') }}</flux:heading>
                    <flux:text class="mt-2">
                        {{ __("You're about to delete this purchase. This will restore the stock of the items in the purchase. This action cannot be reversed.") }}
                    </flux:text>
                </div>

                <div class="flex gap-2">
                    <flux:spacer />

                    <flux:modal.close>
                        <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>

                    <flux:button type="submit" variant="danger" class="cursor-pointer">{{ __('Delete purchase') }}</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
