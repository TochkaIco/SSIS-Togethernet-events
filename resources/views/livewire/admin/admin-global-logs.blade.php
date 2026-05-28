<div>
    <div class="flex items-center justify-between">
        <h1 class="text-xl md:text-2xl font-semibold">{{ __('Global Logs') }}</h1>

        <flux:modal.trigger name="confirm-log-clearing">
            <flux:button variant="filled" color="red" icon="trash" size="sm" class="cursor-pointer">{{ __('Clear Old Logs') }}</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:separator class="my-6" />

    <flux:modal name="confirm-log-clearing" class="md:w-100">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Clear Old Logs') }}</flux:heading>
                <flux:subheading>
                    {{ __('Remove system logs older than a certain period. This action cannot be undone.') }}
                </flux:subheading>
            </div>

            <flux:field>
                <flux:label>{{ __('Keep logs from the last') }}</flux:label>
                <flux:select wire:model="monthsToKeep">
                    <flux:select.option value="1">{{ __('1 month') }}</flux:select.option>
                    <flux:select.option value="2">{{ __('2 months') }}</flux:select.option>
                    <flux:select.option value="3">{{ __('3 months') }}</flux:select.option>
                    <flux:select.option value="6">{{ __('6 months') }}</flux:select.option>
                    <flux:select.option value="12">{{ __('12 months') }}</flux:select.option>
                </flux:select>
            </flux:field>

            <div class="flex gap-2 justify-end">
                <flux:modal.close>
                    <flux:button variant="ghost" class="cursor-pointer">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button
                    wire:click="clearOldLogs"
                    variant="danger"
                    class="cursor-pointer"
                >
                    {{ __('Clear Logs') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <div class="space-y-4">
        @php
            $getTargetUser = function($log) use ($targetUsers) {
                $id = $log->details['target_user_id'] ?? $log->details['user_id'] ?? null;
                return $id ? $targetUsers->get($id) : null;
            };
        @endphp

        {{-- Desktop Table View --}}
        <div class="hidden md:block">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Action') }}</flux:table.column>
                    <flux:table.column>{{ __('Type') }}</flux:table.column>
                    <flux:table.column>{{ __('By User') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column>{{ __('Target / Details') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($logs as $log)
                        @php $targetUser = $getTargetUser($log); @endphp
                        <flux:table.row>
                            <flux:table.cell class="font-medium">
                                <div class="flex items-center gap-2">
                                    <flux:icon :icon="match($log->action_type) {
                                        'meeting' => 'calendar',
                                        'event' => 'layout-grid',
                                        'user' => 'users',
                                        'impersonation' => 'user',
                                        'feedback' => 'chat-bubble-left-right',
                                        'config' => 'cog-6-tooth',
                                        'system' => 'server',
                                        default => 'information-circle',
                                    }" size="sm" class="text-zinc-500" />
                                    {{ $log->action_title }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="match($log->action_type) {
                                    'meeting' => 'blue',
                                    'event' => 'green',
                                    'user' => 'purple',
                                    'impersonation' => 'red',
                                    'feedback' => 'orange',
                                    'config' => 'indigo',
                                    'system' => 'zinc',
                                    default => 'zinc',
                                }">
                                    {{ ucfirst($log->action_type) }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    @if($log->user)
                                        <flux:avatar :src="$log->user->profile_picture" :initials="$log->user->initials()" size="xs" />
                                        <flux:text :href="route('admin.user.profile', $log->user)" wire:navigate class="text-sm hover:text-orange-300 hover:underline cursor-pointer">{{ $log->user->name }}</flux:text>
                                    @else
                                        <span class="text-zinc-400 italic text-xs">{{ __('System / Deleted User') }}</span>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i') }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex flex-col gap-1">
                                    @if($targetUser)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-zinc-500">{{ __('Target') }}:</span>
                                            <flux:text :href="route('admin.user.profile', $targetUser)" wire:navigate class="text-sm font-medium hover:text-orange-300 hover:underline cursor-pointer">
                                                {{ $targetUser->name }}
                                            </flux:text>
                                        </div>
                                    @endif

                                    <div class="max-w-xs truncate text-[10px] text-zinc-400" title="{{ json_encode($log->details) }}">
                                        @if($log->details)
                                            @foreach($log->details as $key => $value)
                                                @continue(in_array($key, ['target_user_id', 'user_id']))
                                                <span class="font-semibold">{{ str_replace('_', ' ', $key) }}:</span> {{ is_array($value) ? implode(', ', $value) : $value }}{{ !$loop->last ? ' | ' : '' }}
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>

        {{-- Mobile Stacked View --}}
        <div class="md:hidden space-y-4">
            @foreach ($logs as $log)
                @php $targetUser = $getTargetUser($log); @endphp
                <flux:card class="space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <flux:icon :icon="match($log->action_type) {
                                'meeting' => 'calendar',
                                'event' => 'layout-grid',
                                'user' => 'users',
                                'impersonation' => 'user',
                                'feedback' => 'chat-bubble-left-right',
                                'config' => 'cog-6-tooth',
                                'system' => 'server',
                                default => 'information-circle',
                            }" size="sm" class="text-zinc-500" />
                            <flux:heading size="sm">{{ $log->action_title }}</flux:heading>
                        </div>
                        <flux:badge size="sm" :color="match($log->action_type) {
                            'meeting' => 'blue',
                            'event' => 'green',
                            'user' => 'purple',
                            'impersonation' => 'red',
                            'feedback' => 'orange',
                            'config' => 'indigo',
                            'system' => 'zinc',
                            default => 'zinc',
                        }">
                            {{ ucfirst($log->action_type) }}
                        </flux:badge>
                    </div>

                    <div class="grid grid-cols-2 gap-x-3 gap-y-2 mt-2 text-sm">
                        <div class="text-zinc-500">{{ __('By') }}:</div>
                        <div class="flex items-center gap-1 min-w-0">
                            @if($log->user)
                                <flux:avatar :src="$log->user->profile_picture" :initials="$log->user->initials()" size="xs" />
                                <flux:link :href="route('admin.user.profile', $log->user)" wire:navigate class="truncate">{{ $log->user->name }}</flux:link>
                            @else
                                <span class="text-zinc-400 italic text-xs">{{ __('System') }}</span>
                            @endif
                        </div>

                        @if($targetUser)
                            <div class="text-zinc-500">{{ __('Target') }}:</div>
                            <div class="flex items-center gap-1 min-w-0">
                                <flux:avatar :src="$targetUser->profile_picture" :initials="$targetUser->initials()" size="xs" />
                                <flux:link :href="route('admin.user.profile', $targetUser)" wire:navigate class="truncate font-medium">{{ $targetUser->name }}</flux:link>
                            </div>
                        @endif

                        <div class="text-zinc-500">{{ __('Date') }}:</div>
                        <div class="text-zinc-700 dark:text-zinc-300">{{ $log->created_at->format('Y-m-d H:i') }}</div>
                    </div>

                    @if($log->details && count(array_diff(array_keys($log->details), ['target_user_id', 'user_id'])) > 0)
                        <div class="p-2 rounded bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 text-[11px] text-zinc-600 dark:text-zinc-400">
                            @foreach($log->details as $key => $value)
                                @continue(in_array($key, ['target_user_id', 'user_id']))
                                <div><span class="font-semibold text-zinc-900 dark:text-zinc-100">{{ str_replace('_', ' ', $key) }}:</span> {{ is_array($value) ? implode(', ', $value) : $value }}</div>
                            @endforeach
                        </div>
                    @endif
                </flux:card>
            @endforeach
        </div>

        <flux:pagination :paginator="$logs" />
    </div>
</div>
