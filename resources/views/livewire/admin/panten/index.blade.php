<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Pant Management') }}</flux:heading>
            <flux:subheading>{{ __('Monitor recycling status and submit check or swish payments.') }}</flux:subheading>
        </div>

        @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer']))
            @if(!$hasIncomplete)
                <flux:modal.trigger name="confirm-activate-alert-modal">
                    <flux:button variant="primary" icon="bell" class="cursor-pointer">
                        {{ __('Activate Pant Alert') }}
                    </flux:button>
                </flux:modal.trigger>
            @else
                <flux:tooltip :content="__('A pant alert is already active or pending confirmation.')">
                    <flux:button variant="primary" icon="bell" disabled>
                        {{ __('Activate Pant Alert') }}
                    </flux:button>
                </flux:tooltip>
            @endif
        @endif
    </div>

    <flux:separator />



    <div class="gap-6">
        {{-- Main/Active Section --}}
        @if($activeAlert)
            <flux:card class="border-zinc-200 dark:border-zinc-800 space-y-4">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-zinc-100 dark:bg-zinc-800 rounded-lg text-zinc-500 dark:text-zinc-400">
                            <flux:icon.exclamation-triangle class="size-6" />
                        </div>
                        <div>
                            <flux:heading>{{ __('Recycling Bins are Full!') }}</flux:heading>
                            <flux:subheading>
                                {{ __('Alert active since') }} {{ $activeAlert->created_at->format('Y-m-d H:i') }}
                            </flux:subheading>
                        </div>
                    </div>
                    <flux:badge color="zinc" size="sm" class="uppercase font-semibold tracking-wider">{{ __('Active') }}</flux:badge>
                </div>

                <flux:separator />

                <div class="space-y-4 text-sm text-zinc-700 dark:text-zinc-300">
                    <p>{{ __('Please return the bottles/cans to a shop. Once recycled, submit the details here.') }}</p>

                    <div class="bg-zinc-50 dark:bg-zinc-900/50 border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 space-y-2">
                        <span class="text-xs uppercase font-semibold text-zinc-450 dark:text-zinc-400 tracking-wider">{{ __('Swish Account for Pant') }}</span>
                        <div class="flex items-center justify-between">
                                <span class="text-lg font-mono font-bold text-zinc-900 dark:text-white">
                                    {{ $activeAlert->receiver_swish !== 'unset' ? $activeAlert->receiver_swish : __('Not Configured') }}
                                </span>
                            @if($activeAlert->receiver_swish === 'unset')
                                <flux:badge color="red" size="sm">{{ __('Admin Action Required') }}</flux:badge>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2">
                    <div>
                        @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer']))
                            <flux:modal.trigger name="confirm-stop-alert-modal">
                                <flux:button variant="danger" icon="x-mark" class="cursor-pointer">
                                    {{ __('Stop Alert') }}
                                </flux:button>
                            </flux:modal.trigger>
                        @endif
                    </div>
                    @can('manage panten')
                        <flux:button variant="primary" icon="check" wire:click="openCompleteModal" class="cursor-pointer">
                            {{ __('Complete recycling & submit') }}
                        </flux:button>
                    @endcan
                </div>
            </flux:card>
        @else
            <flux:card class="flex flex-col items-center justify-center py-16 text-center border border-dashed border-zinc-305 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950/5">
                <div class="flex justify-center w-full mb-4">
                    <flux:icon.check-circle class="size-16 text-zinc-300 dark:text-zinc-750" />
                </div>
                <flux:heading>{{ __('All Clean!') }}</flux:heading>
                <flux:subheading>{{ __('There are no active pant alerts. Recycle bins are not full.') }}</flux:subheading>
            </flux:card>
        @endif
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            {{-- Past Alerts Table --}}
            <flux:card class="lg:col-span-2 space-y-4">
                <flux:heading>{{ __('Past Recycles') }}</flux:heading>

                @if($pastAlerts->isEmpty())
                    <flux:text class="text-center py-8 text-zinc-400">{{ __('No completed pant alerts found.') }}</flux:text>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Date') }}</flux:table.column>
                            <flux:table.column>{{ __('Amount') }}</flux:table.column>
                            <flux:table.column>{{ __('Completed By') }}</flux:table.column>
                            <flux:table.column>{{ __('Method') }}</flux:table.column>
                            <flux:table.column>{{ __('Receipt') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach($pastAlerts as $alert)
                                <flux:table.row :key="$alert->id">
                                    <flux:table.cell>
                                        <div class="flex flex-col">
                                            <span class="font-medium text-zinc-800 dark:text-zinc-200">{{ $alert->created_at->format('Y-m-d') }}</span>
                                            <span class="text-xs text-zinc-450 dark:text-zinc-400">{{ $alert->created_at->format('H:i') }}</span>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <span class="font-bold text-zinc-900 dark:text-white">{{ number_format($alert->sek_received, 2) }} kr</span>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex flex-wrap gap-2 items-center">
                                            @foreach($alert->completedByUsers as $user)
                                                <div class="flex items-center gap-1.5" title="{{ $user->name }}">
                                                    <flux:avatar size="xs" circle :initials="$user->initials()" :src="$user->profile_picture" />
                                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($alert->receipt_path)
                                            <flux:badge color="zinc" size="sm">{{ __('Check Uploaded') }}</flux:badge>
                                        @else
                                            <flux:badge color="zinc" size="sm">{{ __('Swish') }}</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-2">
                                            @if(auth()->user()->hasAnyRole(['admin', 'super-admin', 'maintainer']))
                                                @if($alert->receipt_path)
                                                    <flux:button size="xs" variant="ghost" icon="eye" wire:click="viewPhoto({{ $alert->id }})" class="cursor-pointer" />
                                                @endif

                                                @if($alert->admin_user_id)
                                                    <flux:badge color="zinc" size="sm" title="{{ __('Confirmed by') }} {{ $alert->admin?->name }}">
                                                        {{ __('Confirmed') }}
                                                    </flux:badge>
                                                @else
                                                    <flux:button size="xs" variant="primary" wire:click="confirmReceipt({{ $alert->id }})" class="cursor-pointer">
                                                        {{ __('Confirm Receipt') }}
                                                    </flux:button>
                                                    <flux:button size="xs" variant="danger" wire:click="declineCompletion({{ $alert->id }})" class="cursor-pointer">
                                                        {{ __('Decline') }}
                                                    </flux:button>
                                                @endif

                                                <flux:button size="xs" variant="ghost" icon="trash" wire:click="deleteAlert({{ $alert->id }})" class="cursor-pointer text-red-500 hover:text-red-600" />
                                            @else
                                                @if($alert->admin_user_id)
                                                    <flux:badge color="zinc" size="sm">{{ __('Confirmed') }}</flux:badge>
                                                @else
                                                    <flux:badge color="zinc" size="sm">{{ __('Pending Confirm') }}</flux:badge>
                                                @endif
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                    <div class="mt-4">
                        {{ $pastAlerts->links() }}
                    </div>
                @endif
            </flux:card>

            {{-- Leaderboard --}}
            <flux:card class="space-y-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon.trophy variant="outline" class="size-6 text-zinc-400" />
                    <flux:heading>{{ __('Recycling Leaderboard') }}</flux:heading>
                </div>

                @if(empty($leaderboard))
                    <flux:text class="text-zinc-400 text-sm">{{ __('No recycling data yet.') }}</flux:text>
                @else
                    <div class="space-y-3">
                        @foreach($leaderboard as $index => $item)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-bold text-zinc-450 dark:text-zinc-400 w-4">#{{ $index + 1 }}</span>
                                    <flux:avatar size="sm" circle :initials="$item->user->initials()" :src="$item->user->profile_picture" />
                                    <span class="text-sm font-medium text-zinc-950 dark:text-white">{{ $item->user->name }}</span>
                                </div>
                                <flux:badge color="zinc" size="sm">{{ $item->count }} {{ trans_choice('recycle|recycles', $item->count) }}</flux:badge>
                            </div>
                        @endforeach
                    </div>
                @endif
            </flux:card>
        </div>
    </div>

    {{-- Bottom Statistics --}}
    <div class="space-y-4">
        <flux:heading>{{ __('Statistics') }}</flux:heading>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <flux:card class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <flux:icon.calendar variant="outline" class="size-6 text-zinc-400" />
                    <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collected This Year') }}</span>
                </div>
                <div class="text-3xl mt-1 font-bold">{{ number_format($yearlySek, 2) }} kr</div>
            </flux:card>

            <flux:card class="flex flex-col gap-2">
                <div class="flex items-center gap-3">
                    <flux:icon.arrow-path-rounded-square variant="outline" class="size-6 text-zinc-400" />
                    <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Recycles This Year') }}</span>
                </div>
                <div class="text-3xl mt-1 font-bold">{{ $yearlyCount }}</div>
            </flux:card>
        </div>
    </div>

    {{-- Modals --}}
    <flux:modal name="complete-alert-modal" class="max-w-md w-full" wire:model="showCompleteModal">
        <form wire:submit="completeAlert" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Complete Pant Alert') }}</flux:heading>
                <flux:subheading>{{ __('Submit recycling details to mark this alert as complete.') }}</flux:subheading>
            </div>

            <flux:separator />

            <div class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('Method') }}</flux:label>
                    <flux:select wire:model.live="completionMethod">
                        <flux:select.option value="swish">{{ __('Sent to Swish') }}</flux:select.option>
                        <flux:select.option value="check">{{ __('Uploaded Check Photo') }}</flux:select.option>
                    </flux:select>
                    <flux:error name="completionMethod" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Amount Received (SEK)') }}</flux:label>
                    <flux:input type="number" step="0.01" wire:model="sekReceived" placeholder="e.g. 150.50" required />
                    <flux:error name="sekReceived" />
                </flux:field>

                <flux:checkbox.group wire:model="completedBy" label="{{ __('Who participated in recycling?') }}">
                    <div class="max-h-40 overflow-y-auto border border-zinc-200 dark:border-zinc-800 rounded-lg p-3 space-y-2 mt-2">
                        @foreach($usersList as $user)
                            <div class="flex items-center gap-3">
                                <flux:checkbox value="{{ $user->id }}" />
                                <flux:avatar size="xs" circle :initials="$user->initials()" :src="$user->profile_picture" />
                                <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
                            </div>
                        @endforeach
                    </div>
                    <flux:error name="completedBy" />
                </flux:checkbox.group>

                @if($completionMethod === 'check')
                    <flux:field>
                        <flux:label>{{ __('Check Photo') }}</flux:label>
                        <div class="mt-1 space-y-3">
                            @if($receiptPhoto)
                                <div class="relative w-full max-h-40 overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                                    <img src="{{ $receiptPhoto->temporaryUrl() }}" class="w-full h-auto object-cover" />
                                </div>
                            @endif
                            <input type="file" wire:model="receiptPhoto" accept="image/*" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-orange-50 dark:file:bg-zinc-800 file:text-orange-700 dark:file:text-zinc-200 hover:file:bg-orange-100 dark:hover:file:bg-zinc-700 cursor-pointer" />
                        </div>
                        <flux:error name="receiptPhoto" />
                    </flux:field>
                @endif
            </div>

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" class="cursor-pointer">{{ __('Submit Completion') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="view-photo-modal" class="max-w-lg w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Check Photo Evidence') }}</flux:heading>
                <flux:subheading>{{ __('Verify the physical check received by the member.') }}</flux:subheading>
            </div>

            <flux:separator />

            @if($viewingPhotoPath)
                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-800">
                    <img src="{{ asset('storage/' . $viewingPhotoPath) }}" class="w-full h-auto object-contain max-h-[30rem]" />
                </div>
            @endif

            <flux:separator />

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Close') }}</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="confirm-receipt-modal" class="min-w-[22rem]">
        <form wire:submit="executeConfirmReceipt" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Confirm receipt?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Are you sure you want to confirm receipt of this recycling payment?') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary" class="cursor-pointer">{{ __('Confirm') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-activate-alert-modal" class="min-w-[22rem]">
        <form wire:submit="activateAlert" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Activate Pant Alert?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Are you sure you want to activate a new pant alert? This will notify members to recycling action.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary" class="cursor-pointer">{{ __('Activate') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-stop-alert-modal" class="min-w-[22rem]">
        <form wire:submit="stopAlert" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Stop Pant Alert?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Are you sure you want to stop this pant alert? This will cancel the alert and remove it.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger" class="cursor-pointer">{{ __('Stop') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-decline-completion-modal" class="min-w-[22rem]">
        <form wire:submit="executeDeclineCompletion" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Decline completion?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Are you sure you want to decline this completion submission? This will return the alert to an active state, allowing members to submit details again.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger" class="cursor-pointer">{{ __('Decline') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="confirm-delete-alert-modal" class="min-w-[22rem]">
        <form wire:submit="executeDeleteAlert" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete Alert?') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ __('Are you sure you want to delete this alert from history? This action cannot be undone.') }}
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />

                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger" class="cursor-pointer">{{ __('Delete') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
