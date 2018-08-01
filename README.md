# docker-etl :watermelon:

<!-- TODO badges go here -->

- **E**xtract container configuration from docker or files
- **T**ransform it - add, remove or replace configuration
- **L**oad it back into docker, files or console

## :floppy_disk: Installation

There are a few ways you can get this up and running:

1. Via [:musical_score: Composer (Globally)](https://getcomposer.org/):
   ```bash
   $ compose global require uuf6429/docker-etl
   ```
   Make sure that Composer binaries are in your `PATH` (if not, either set it up or instead use `composer docker-etl ...`).

2. Via [:whale: Docker Hub](https://hub.docker.com/r/uuf6429):
   ```bash
   $ docker pull uuf6429/docker-etl
   ```
   Later on, you can run it like so (privileged mode required):
   ```bash
   $ docker run --rm -v /var/run/docker.sock:/var/run/docker.sock uuf6429/docker-etl
   ```

3. Via [:five: PHIVE (as a phar)](https://phar.io/):
   ```bash
   $ phive install uuf6429/docker-etl
   ```

4. Or you can download the desired [:octocat: GitHub release](https://github.com/uuf6429/docker-etl/releases).

## Concepts

### Argument Order

Most arguments' order is important. This is due to [batch processing](#-batch-processing); you will be able
to influence the application and configuration state with every CLI argument.

### Batch Processing

This app allows you perform several actions after each other in one go.
For example, you can load config for a container, change volumes, generate a docker-compose file and then do a few more
actions on a completely different container:
```bash
$ docker-etl --extract-from-docker-cmd=my-service \
             --add=volumes=host/data:/app \
             --load-into-docker-compose=host/docker-compose.yml:my-service \
             --reset \
             --extract-from-docker-cmd=my-service2 \
             ...
```

### State Tokenizer

To avoid incompatibilities and the possibility of the app to miss a docker setting, there are two aspects:

- when parsing configuration, anything that was not used will cause a warning to point out that something might have been skipped
- similarly, when generating configuration, if something cannot be generated, you'll also get a warning

While the application tries to do a best-effort, it will not hide these sort of issues (so you could at least look them up in the logs/stderr).

### Parallel Processing

Unfortunately, there are no current plans to achieve this at the moment, mostly due to the sheer complexity involved.

### Wrong Intentions

The application does not question your intentions and assumes you know what you're doing.
If you forget to output anything, it won't fail but it won't do anything either.
Similarly, if you forget to extract any configuration, it will continue with a clean slate.

### Why a Watermelon?

Why not? Who doesn't like watermelons? Also, because they have a [fascinating history](https://news.nationalgeographic.com/2015/08/150821-watermelon-fruit-history-agriculture/).

## :rocket: Usage

<!-- TODO arguments available go here -->

## :electric_plug: Extending

If you'd like to extend the functionality with your own, you can do so by injecting PHP into the process:
```bash
$ docker-etl --include my-include.php \
             --set-random-name=cheese- \
             ...
```
And the contents of `my-include.php`:
```php
<?php

class RandomNameSetter extends \uuf6429\DockerEtl\Task\Task
{
    public function getTaskOptionName()
    {
        return '--set-random-name';
    }

    public function getTaskOptionMode()
    {
        return self::VALUE_OPTIONAL;
    }

    public function getTaskOptionDescription()
    {
        return 'Sets container name to a random value, optionally with a prefix (option value).';
    }

    public function execute(\uuf6429\DockerEtl\Container\Container $container, $value)
    {
        $container->name = uniqid($value ?: '', true);
    }
}

/** @var \uuf6429\DockerEtl\Console\Application $application */
$application->addTasks([new RandomNameSetter()]);
```
*Note: feel free to have your class(es) somewhere else and then require/autoload them in your include file.*

### Service Awareness

If your task class depends on some service (for example logging), just implement one of the interfaces below and to receive the service:

- `\Psr\Log\LoggerAwareInterface`
- `\uuf6429\DockerEtl\Console\ApplicationAwareInterface`
- `\uuf6429\DockerEtl\Console\OutputAwareInterface`

*Note: the service becomes available after construction - it won't be available in your constructor.*

## :thought_balloon: Rationale

I needed a tool to generate `docker run ...` for existing containers.
Unfortunately, the tools I found to do this were either broken or did not do everything I needed.
