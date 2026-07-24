<?php

namespace Tests\Feature;

use Tests\TestCase;

class MaintenancePageTest extends TestCase
{
    public function test_branded_laravel_maintenance_page_can_render(): void
    {
        $html = view('errors.503')->render();

        $this->assertStringContainsString('Museum Azman', $html);
        $this->assertStringContainsString('We’ll be back shortly.', $html);
        $this->assertStringContainsString('Scheduled Maintenance', $html);
    }

    public function test_static_apache_maintenance_fallback_exists(): void
    {
        $html = file_get_contents(public_path('503.html'));

        $this->assertIsString($html);
        $this->assertStringContainsString('We’ll be back shortly.', $html);
    }
}
