<?php

namespace uuf6429\DockerEtl;

trait ReflectXDebugConfigTrait
{
    /**
     * @return string[]
     */
    protected function buildPhpArgs()
    {
        $xdebugEnabledFn = 'xdebug_is_enabled';

        if (!function_exists($xdebugEnabledFn) || !$xdebugEnabledFn()) {
            return [];
        }

        if (!getenv('XDEBUG_PATH')) {
            trigger_error('Could not set up XDebug; XDEBUG_PATH environment variable was not set.', E_USER_WARNING);

            return [];
        }

        return array_merge(
            [
                '-dzend_extension=' . getenv('XDEBUG_PATH')
            ],
            array_map(
                function ($key) {
                    return '-d' . $key . '=' . ini_get($key);
                },
                [
                    'xdebug.remote_enable',
                    'xdebug.remote_mode',
                    'xdebug.remote_port',
                    'xdebug.remote_host',
                ]
            )
        );
    }
}
