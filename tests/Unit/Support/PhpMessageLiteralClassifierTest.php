<?php

declare(strict_types=1);

use PawCircle\Scripts\Support\PhpMessageLiteralClassifier;

test('operator log messages are not classified as user-facing text', function (): void {
    $path = dirname(__DIR__, 3).'/scripts/Support/PhpMessageLiteralClassifier.php';

    if (is_file($path)) {
        require_once $path;
    }

    expect(class_exists(PhpMessageLiteralClassifier::class), $path)->toBeTrue();

    $tokens = token_get_all(<<<'PHP'
        <?php

        $message = $failed
            ? 'Slow streamed request failed.'
            : 'Slow request completed.';
        $this->logger->warning($message, []);
        logger()->error('Background synchronization failed.');
        $logger->withContext($service->warning('This warning is shown to the user.'));
        abort(422, 'This message must remain user-facing.');
        PHP);
    $classifications = [];

    foreach ($tokens as $index => $token) {
        if (is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) {
            $classifications[trim($token[1], "'")] = PhpMessageLiteralClassifier::isDiagnostic(
                $tokens,
                $index,
            );
        }
    }

    expect($classifications)->toMatchArray([
        'Slow streamed request failed.' => true,
        'Slow request completed.' => true,
        'Background synchronization failed.' => true,
        'This warning is shown to the user.' => false,
        'This message must remain user-facing.' => false,
    ]);
});
