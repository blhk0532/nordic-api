<?php

namespace Tests\Feature;

use App\Filament\Resources\SwedenPersoners\Pages\ListSwedenPersoners;
use App\Models\User;
use Filament\AdvancedExport\Jobs\ProcessExportJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class DebugExportQueueTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_debug_export_queue_dispatching()
    {
        Queue::fake();
        
        $user = User::factory()->create();
        
        Livewire::actingAs($user)
            ->test(ListSwedenPersoners::class)
            ->fillForm([
                'export_format' => 'xlsx',
                'order_column' => 'created_at',
                'order_direction' => 'desc',
                'columns' => [
                    ['field' => 'fornamn', 'title' => 'First Name'],
                ],
            ])
            ->call('callMountedAction', 'export');
            
        // Check if job was pushed
        Queue::assertPushed(ProcessExportJob::class);
    }
}
