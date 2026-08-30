<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Session\Session;

final readonly class SafeIntendedUrl
{
    public function __construct(
        private Session $session,
        private ConfigRepository $config,
    ) {}

    public function pull(string $fallback): string
    {
        $intended = $this->session->pull('url.intended');

        if (! is_string($intended) || $intended === '') {
            return $fallback;
        }

        if (preg_match('/[\\\\\x00-\x1F\x7F]/', $intended) === 1) {
            return $fallback;
        }

        if (str_starts_with($intended, '/') && ! str_starts_with($intended, '//')) {
            return $intended;
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

        $targetScheme = strtolower((string) ($target['scheme'] ?? ''));
        $applicationScheme = strtolower((string) ($application['scheme'] ?? ''));
        $targetHost = strtolower((string) ($target['host'] ?? ''));
        $applicationHost = strtolower((string) ($application['host'] ?? ''));

        if (
            ! in_array($targetScheme, ['http', 'https'], true)
            || $targetScheme !== $applicationScheme
            || $targetHost === ''
            || $targetHost !== $applicationHost
            || ($target['port'] ?? null) !== ($application['port'] ?? null)
        ) {
            return $fallback;
        }

        return $intended;
    }
}
