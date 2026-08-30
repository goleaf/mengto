<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SafeIntendedUrl
{
    /** @var list<string> */
    private const array LIFECYCLE_ROUTE_NAMES = [
        'login',
        'register',
        'password.request',
        'password.reset',
        'password.confirm',
        'verification.notice',
        'verification.verify',
        'onboarding.show',
        'logout',
        'locale.update',
        'default-livewire.update',
        'livewire.upload-file',
        'livewire.preview-file',
    ];

    public function __construct(
        private Session $session,
        private ConfigRepository $config,
        private Router $router,
    ) {}

    public function pull(string $fallback): string
    {
        $intended = $this->session->pull('url.intended');

        if (! is_string($intended) || $intended === '') {
            return $fallback;
        }

        $decoded = rawurldecode($intended);

        if (
            preg_match('/[\\\\\x00-\x1F\x7F]/', $intended) === 1
            || preg_match('/[\\\\\x00-\x1F\x7F]/', $decoded) === 1
            || str_starts_with($decoded, '//')
        ) {
            return $fallback;
        }

        $applicationUrl = $this->config->get('app.url');

        if (! is_string($applicationUrl)) {
            return $fallback;
        }

        $target = parse_url($intended);
        $application = parse_url($applicationUrl);

        if (! is_array($target) || ! is_array($application)) {
            return $fallback;
        }

        if (isset($target['user']) || isset($target['pass'])) {
            return $fallback;
        }

        $isRootRelative = str_starts_with($intended, '/')
            && ! str_starts_with($intended, '//');
        $targetScheme = strtolower((string) ($target['scheme'] ?? ''));
        $applicationScheme = strtolower((string) ($application['scheme'] ?? ''));
        $targetHost = strtolower((string) ($target['host'] ?? ''));
        $applicationHost = strtolower((string) ($application['host'] ?? ''));

        if (! $isRootRelative && (
            ! in_array($targetScheme, ['http', 'https'], true)
            || $targetScheme !== $applicationScheme
            || $targetHost === ''
            || $targetHost !== $applicationHost
            || ($target['port'] ?? null) !== ($application['port'] ?? null)
        )) {
            return $fallback;
        }

        $path = $target['path'] ?? '/';

        if (! is_string($path) || ! $this->isProductRoute($path, $target['query'] ?? null)) {
            return $fallback;
        }

        return $intended;
    }

    private function isProductRoute(string $path, mixed $query): bool
    {
        $uri = $path;

        if (is_string($query) && $query !== '') {
            $uri .= '?'.$query;
        }

        try {
            $route = $this->router->getRoutes()->match(Request::create($uri, 'GET'));
        } catch (MethodNotAllowedHttpException|NotFoundHttpException) {
            return false;
        }

        $routeName = $route->getName();

        return is_string($routeName)
            && ! in_array($routeName, self::LIFECYCLE_ROUTE_NAMES, true);
    }
}
