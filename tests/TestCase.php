<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Tes yang memakai seeder otomatis masuk sebagai admin hasil seeder. */
    protected bool $masukSebagaiAdmin = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (($this->seed ?? false) && $this->masukSebagaiAdmin && ($admin = User::where('role', 'admin')->first())) {
            $this->actingAs($admin);
        }
    }
}
