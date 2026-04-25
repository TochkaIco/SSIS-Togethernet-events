<?php

use App\Livewire\Admin\AdminFeedbackView;
use App\Livewire\FeedbackModal;
use App\Models\Feedback;
use App\Models\User;
use Livewire\Livewire;

test('authenticated user can submit feedback with their identity', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(FeedbackModal::class)
        ->set('type', 'bug')
        ->set('comment', 'This is a test bug report')
        ->set('anonymous', false)
        ->call('save');

    $feedback = Feedback::first();
    expect($feedback->user_id)->toBe($user->id)
        ->and($feedback->comment)->toBe('This is a test bug report');
});

test('authenticated user can submit feedback anonymously', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(FeedbackModal::class)
        ->set('type', 'feature')
        ->set('comment', 'This is an anonymous feature request')
        ->set('anonymous', true)
        ->call('save');

    $feedback = Feedback::first();
    expect($feedback->user_id)->toBeNull()
        ->and($feedback->comment)->toBe('This is an anonymous feature request');
});

test('guest can submit feedback', function () {
    Livewire::test(FeedbackModal::class)
        ->set('type', 'qol')
        ->set('comment', 'This is a guest feedback')
        ->call('save');

    $feedback = Feedback::first();
    expect($feedback->user_id)->toBeNull()
        ->and($feedback->comment)->toBe('This is a guest feedback');
});

test('admin can see feedback with user information', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    $user = User::factory()->create(['name' => 'Test User']);
    Feedback::factory()->create([
        'user_id' => $user->id,
        'comment' => 'Visible user feedback',
    ]);

    Livewire::test(AdminFeedbackView::class)
        ->assertSee('Test User')
        ->assertSee('Visible user feedback');
});

test('admin can see anonymous feedback as Guest', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Feedback::factory()->create([
        'user_id' => null,
        'comment' => 'Anonymous feedback',
    ]);

    Livewire::test(AdminFeedbackView::class)
        ->assertSee('Guest')
        ->assertSee('Anonymous feedback');
});

test('admin can filter feedback by comment', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Feedback::factory()->create(['comment' => 'First unique comment']);
    Feedback::factory()->create(['comment' => 'Second unique comment']);

    Livewire::test(AdminFeedbackView::class)
        ->set('search', 'First')
        ->assertSee('First unique comment')
        ->assertDontSee('Second unique comment');
});

test('admin can filter for only unresolved feedback', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);

    Feedback::factory()->create(['comment' => 'Unresolved feedback', 'is_finished' => false]);
    Feedback::factory()->create(['comment' => 'Resolved feedback', 'is_finished' => true]);

    Livewire::test(AdminFeedbackView::class)
        ->set('filterResolved', true)
        ->assertSee('Unresolved feedback')
        ->assertDontSee('Resolved feedback');
});
