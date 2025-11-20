<?php
declare(strict_types=1);

namespace App\Core;

/**
 * View Renderer
 *
 * Renders views with layouts and data
 */
class View
{
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Render a view with layout
     */
    public function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        // Extract data to variables
        extract($data);

        // Make app available in views
        $app = $this->app;
        $auth = $this->app->auth();
        $user = $auth->check() ? $auth->user() : null;

        // Capture view content
        ob_start();
        $viewFile = APP_PATH . "/Views/{$view}.php";

        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }

        require $viewFile;
        $content = ob_get_clean();

        // Render layout
        if ($layout) {
            $layoutFile = APP_PATH . "/Views/layouts/{$layout}.php";

            if (!file_exists($layoutFile)) {
                throw new \Exception("Layout file not found: {$layoutFile}");
            }

            require $layoutFile;
        } else {
            echo $content;
        }
    }
}
