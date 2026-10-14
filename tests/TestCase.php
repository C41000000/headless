<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $this->ensureTestingDatabase($app);

        return $app;
    }

    /**
     * Trava contra rodar RefreshDatabase no banco de desenvolvimento: só aceita
     * SQLite em memória ou um banco cujo nome contenha "test".
     */
    private function ensureTestingDatabase(Application $app): void
    {
        $database = (string) $app['config']->get('database.connections.'.$app['config']->get('database.default').'.database');

        if ($database !== ':memory:' && ! str_contains($database, 'test')) {
            throw new RuntimeException("Testes abortados: o banco [{$database}] não parece ser de teste.");
        }
    }
}
