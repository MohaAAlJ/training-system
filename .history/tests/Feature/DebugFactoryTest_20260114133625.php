<?php

namespace Tests\Feature;

use App\Models\Trainee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugFactoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_trainee_using_factory()
    {
        $trainee = Trainee::factory()->create();
        $this->assertNotNull($trainee->id);
    }
}
