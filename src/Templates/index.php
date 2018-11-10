<?php

namespace LaravelORM;

class Templates {
    protected static $engine;
    protected static $context;

    protected static function getEngine() {
        if (self::$engine === null) {
            self::$engine = new \Mustache_Engine([
                'loader' => new \Mustache_Loader_FilesystemLoader(dirname(__FILE__)),
                'partials_loader' => new \Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/partials'),
                'escape' => function($text) {
                    return $text;
                },
                'helpers' => self::getHelpers(),
                'pragmas' => [ \Mustache_Engine::PRAGMA_FILTERS ]
            ]);

            // Workaround to access to context data
            self::$context = new \ReflectionProperty(\Mustache_LambdaHelper::class, 'context');
            self::$context->setAccessible(true);
        }

        return self::$engine;
    }

    protected static function getHelpers() {
        $encode = function ($value) {
            $value = json_encode($value);

            if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
                $value[0] = "'";
                $value[strlen($value) - 1] = "'";
            }

            return $value;
        };

        return [
            'keyValue' => function ($text, $helper) {
                $context = self::$context->getValue($helper)->last();
                $engine = self::getEngine();
                $loader = $engine->getLoader();
                $engine->setLoader(new \Mustache_Loader_StringLoader);
                $result = '';

                foreach ($context as $key => $value) {
                    $result .= $engine->render($text, ['key' => $key, 'value' => $value]);
                }

                $engine->setLoader($loader);
                return $result;
            },
            'parameters' => function ($value) use ($encode) {
                $result = [];

                if (!is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $parameter) {
                    $result[] = $encode($parameter);
                }

                return implode(', ', $result);
            },
            'encode' => $encode
        ];
    }

    public static function render($path, $params = []) {
        return self::getEngine()->render($path, $params);
    }
}
