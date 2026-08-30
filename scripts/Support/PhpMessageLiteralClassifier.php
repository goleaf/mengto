<?php

declare(strict_types=1);

namespace PawCircle\Scripts\Support;

final class PhpMessageLiteralClassifier
{
    /** @var list<string> */
    private const array LOG_METHODS = [
        'alert',
        'critical',
        'debug',
        'emergency',
        'error',
        'info',
        'notice',
        'warning',
    ];

    /**
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     */
    public static function isDiagnostic(array $tokens, int $index): bool
    {
        if (self::isFirstLoggerArgument($tokens, $index)) {
            return true;
        }

        $variable = self::assignedVariable($tokens, $index);

        if ($variable === null) {
            return false;
        }

        $statementEnd = self::statementEnd($tokens, $index);

        if ($statementEnd === null) {
            return false;
        }

        $count = count($tokens);

        for ($candidate = $statementEnd + 1; $candidate < $count; $candidate++) {
            $token = $tokens[$candidate];

            if (! is_array($token) || $token[0] !== T_VARIABLE || $token[1] !== $variable) {
                continue;
            }

            return self::isFirstLoggerArgument($tokens, $candidate);
        }

        return false;
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     */
    private static function assignedVariable(array $tokens, int $index): ?string
    {
        for ($candidate = $index - 1; $candidate >= 0; $candidate--) {
            $token = $tokens[$candidate];

            if (in_array($token, [';', '{', '}'], true)) {
                return null;
            }

            if ($token !== '=') {
                continue;
            }

            $variable = self::previousSignificantToken($tokens, $candidate);

            return is_array($variable)
                && is_array($variable['token'])
                && $variable['token'][0] === T_VARIABLE
                    ? $variable['token'][1]
                    : null;
        }

        return null;
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     */
    private static function statementEnd(array $tokens, int $index): ?int
    {
        $roundDepth = 0;
        $squareDepth = 0;
        $curlyDepth = 0;
        $count = count($tokens);

        for ($candidate = $index + 1; $candidate < $count; $candidate++) {
            $token = $tokens[$candidate];

            if (! is_string($token)) {
                continue;
            }

            match ($token) {
                '(' => $roundDepth++,
                ')' => $roundDepth = max(0, $roundDepth - 1),
                '[' => $squareDepth++,
                ']' => $squareDepth = max(0, $squareDepth - 1),
                '{' => $curlyDepth++,
                '}' => $curlyDepth = max(0, $curlyDepth - 1),
                default => null,
            };

            if (
                $token === ';'
                && $roundDepth === 0
                && $squareDepth === 0
                && $curlyDepth === 0
            ) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     */
    private static function isFirstLoggerArgument(array $tokens, int $index): bool
    {
        $openingParenthesis = self::previousSignificantToken($tokens, $index);

        if ($openingParenthesis === null || $openingParenthesis['token'] !== '(') {
            return false;
        }

        $method = self::previousSignificantToken($tokens, $openingParenthesis['index']);

        if (
            $method === null
            || ! is_array($method['token'])
            || $method['token'][0] !== T_STRING
            || ! in_array(strtolower($method['token'][1]), self::LOG_METHODS, true)
        ) {
            return false;
        }

        $operator = self::previousSignificantToken($tokens, $method['index']);

        if (
            $operator === null
            || ! is_array($operator['token'])
            || ! in_array($operator['token'][0], [T_OBJECT_OPERATOR, T_DOUBLE_COLON], true)
        ) {
            return false;
        }

        $receiver = self::previousSignificantToken($tokens, $operator['index']);

        if ($receiver === null) {
            return false;
        }

        if (is_array($receiver['token'])) {
            return self::isLoggerName($receiver['token']);
        }

        if ($receiver['token'] !== ')') {
            return false;
        }

        return self::isLoggerFactoryCall($tokens, $receiver['index']);
    }

    /** @param array{0: int, 1: string, 2: int} $token */
    private static function isLoggerName(array $token): bool
    {
        return in_array($token[0], [T_STRING, T_VARIABLE], true)
            && in_array(strtolower(ltrim($token[1], '$')), ['log', 'logger'], true);
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     */
    private static function isLoggerFactoryCall(array $tokens, int $closingParenthesis): bool
    {
        $depth = 0;

        for ($candidate = $closingParenthesis; $candidate >= 0; $candidate--) {
            $token = $tokens[$candidate];

            if ($token === ')') {
                $depth++;
            } elseif ($token === '(') {
                $depth--;
            }

            if ($depth !== 0) {
                continue;
            }

            $factory = self::previousSignificantToken($tokens, $candidate);

            return $factory !== null
                && is_array($factory['token'])
                && $factory['token'][0] === T_STRING
                && strtolower($factory['token'][1]) === 'logger';
        }

        return false;
    }

    /**
     * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
     * @return array{index: int, token: array{0: int, 1: string, 2: int}|string}|null
     */
    private static function previousSignificantToken(array $tokens, int $index): ?array
    {
        for ($candidate = $index - 1; $candidate >= 0; $candidate--) {
            $token = $tokens[$candidate];

            if (
                is_array($token)
                && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)
            ) {
                continue;
            }

            return ['index' => $candidate, 'token' => $token];
        }

        return null;
    }
}
