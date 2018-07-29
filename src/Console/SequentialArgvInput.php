<?php

namespace uuf6429\DockerEtl\Console;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputDefinition;

/**
 * A class base on {@see \Symfony\Component\Console\Input\ArgvInput}, however it keeps track of option order without
 * parsing into arrays (eg; "-a 1 -a 2 -b 3 -a 4" => [[a, 1], [a, 2], [b, 3], [a, 4]]).
 */
class SequentialArgvInput extends ArgvInput
{
    /**
     * @var string[]
     */
    private $sourceTokens;

    /**
     * @var array[]
     */
    private $parsedTokens;

    public function __construct(array $argv = null, InputDefinition $definition = null)
    {
        $argv = $argv === null ? $_SERVER['argv'] : $argv;
        $this->sourceTokens = array_slice($argv, 1);

        parent::__construct($argv, $definition);
    }

    protected function parse()
    {
        parent::parse();

        $this->parsedTokens = [];
        $tokens = $this->sourceTokens;
        while ($token = array_shift($tokens) !== null) {
            // TODO parse token
            // Note: we may have any of these combinations:
            // ['--key=value']          - simply parse kv pair
            // ['--key', 'value']       - shift value (ensure that option value is required OR value to be popped is not an option)
            // ['-k=value']
            // ['-k', 'value']
            // ['--key', '--other=bbb'] - for value-less options (in this case, do not shift)
            $this->parsedTokens[] = [$token];
        }
    }

    public function getParsedOptions()
    {
        return $this->parsedTokens;
    }
}
