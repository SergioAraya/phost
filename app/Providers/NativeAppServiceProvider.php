<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $window = Window::open()
            ->title('phost')
            ->hideMenu()
            ->width(1200)
            ->height(740)
            ->minWidth(700)
            ->minHeight(600);

        // Hidden title bars keep the macOS traffic lights. On Linux they remove
        // minimize and close, so the desktop title bar stays visible there.
        if (PHP_OS_FAMILY === 'Darwin') {
            $window->titleBarHidden();
        }
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
            'max_execution_time' => -1,
        ];
    }
}
