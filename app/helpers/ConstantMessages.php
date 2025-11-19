<?php

if (!function_exists('constant_message')) {
    /**
     * Resolve a response template from config/constants.php and replace placeholders.
     */
    function constant_message(string $key, array $replacements = []): array
    {
        $definition = config("constants.$key");

        if (!is_array($definition)) {
            throw new \InvalidArgumentException("Constant [$key] is not defined.");
        }

        $message = $definition['message'] ?? '';

        if ($replacements !== []) {
            $replacePairs = [];
            foreach ($replacements as $placeholder => $value) {
                $replacePairs[':' . $placeholder] = $value;
            }
            $message = strtr($message, $replacePairs);
        }

        return array_merge($definition, [
            'message' => $message,
        ]);
    }
}




