<?php

namespace Tests;

use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $_ENV['SESSION_DRIVER'] = 'array';
        $_SERVER['SESSION_DRIVER'] = 'array';
        $_ENV['SESSION_CONNECTION'] = 'sqlite';
        $_SERVER['SESSION_CONNECTION'] = 'sqlite';
        $_ENV['DB_CONNECTION'] = 'sqlite';
        $_SERVER['DB_CONNECTION'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_SERVER['DB_DATABASE'] = ':memory:';

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        config()->set('app.key', 'base64:9P9d5Slm5bRtLqsCytXbM+X1fUPXw6Meru+nzHg9xbk=');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('database.default', 'testing');
        config()->set('session.driver', 'database');
        config()->set('session.connection', 'testing');
        $app['db']->setDefaultConnection('testing');

        return $app;
    }
}

