# docker-etl

<!-- TODO badges go here -->

- **E**xtract container configuration from docker or files
- **T**ransform it - add, remove or replace configuration
- **L**oad it back into docker, files or console

## Installation

There are a few ways you can get this up and running:

1. Via [Composer (Globally)](https://getcomposer.org/):
   ```bash
   $ compose global require uuf6429/docker-etl
   ```
   Make sure that Composer binaries are in your `PATH` (if not, either set it up or instead use `composer docker-etl ...`).

2. Via [Docker Hub](https://hub.docker.com/r/uuf6429):
   ```bash
   $ docker pull uuf6429/docker-etl
   ```
   Later on, you can run it like so (privileged mode required):
   ```bash
   $ docker run --rm -v /var/run/docker.sock:/var/run/docker.sock uuf6429/docker-etl
   ```

3. Via [PHIVE (as a phar)](https://phar.io/):
   ```bash
   $ phive install uuf6429/docker-etl
   ```

4. Or you can download the desired [GitHub release](https://github.com/uuf6429/docker-etl/releases)

## Concepts

### Argument Order

Most arguments' order is important. This is due to [batch processing](#batch-processing); you will be able
to influence the application and configuration state with every CLI argument.

### Batch Processing

This app allows you perform several actions after each other in one go.
For example, you can load config for a container, change volumes, generate a docker-compose file and then do a few more
actions on a completely different container:
```bash
$ docker-etl --extract-from-docker-cmd my-service \
             --add volume host/data:/app \
             --load-into-docker-compose host/docker-compose.yml:my-service \
             --reset \
             --extract-from-docker-cmd my-service2 \
             ...
```

### State Tokenizer

To avoid incompatibilities and the possibility of the app to miss a docker setting, there are two aspects:

- when parsing configuration, anything that was not used will cause a warning to point out that something might have been skipped
- similarly, when generating configuration, if something cannot be generated, you'll also get a warning

While the application tries to do a best-effort, it will not hide these sort of issues (so you could at least look them up in the logs).

### Parallel Processing

Unfortunately, there are no current plans to achieve this at the moment, mostly due to the sheer complexity involved.

### Wrong Intentions

The application does not question your intentions. If you forget to output anything, it won't fail but it won't do anything either.
Similarly, if you forget to extract any configuration, it will simply write an empty one later on.

## Usage

<!-- TODO arguments available go here -->

## Extending

If you'd like to extend the functionality with your own, you can do so by injecting PHP into the process:
```bash
$ docker-etl --include my-include.php
             --extract-from-my-include \
             --load-into-docker-compose host/docker-compose.yml:my-service \
```
And the contents of `my-include.php`:
```php
<?php

class MyExtractor extends \uuf6429\DockerEtl\Task\Task
{
    public function getTaskOptionName()
    {
        return '--extract-from-my-include';
    }

    public function getTaskOptionMode()
    {
        return self::VALUE_NONE;
    }

    public function getTaskOptionDescription()
    {
        return 'My own extractor.';
    }

    public function execute(\uuf6429\DockerEtl\ContainerState $container)
    {
        $container->setImage('my/image');
    }
}

/** @var \uuf6429\DockerEtl\Console\Application $application */
$application->addTasks([new MyExtractor()]);
```
*Note: feel free to have your class(es) somewhere else and then require/autoload them in your include file.*

## Rationale

I needed a tool to generate `docker run ...` for existing containers.
Unfortunately, the tools I found to this were either broken or did not do everything I needed.
