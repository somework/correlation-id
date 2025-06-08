# PHP CorrelationIdMiddleware

Minimalistic Composer package providing automatic correlation ID handling for HTTP requests.
Compatible with Symfony 6 and 7 or any PSR-15 stack.

## Installation

```bash
composer require somework/correlation-id
```

## Quick Start

### Symfony

Register the `SomeWork\CorrelationId\Symfony\CorrelationIdSubscriber` as an event subscriber.

### PSR-15

```php
use SomeWork\CorrelationId\CorrelationIdMiddleware;
use Nyholm\Psr7\ServerRequest;

$middleware = new CorrelationIdMiddleware();
$response = $middleware->process(new ServerRequest('GET', '/'), $handler);
```

## Using CorrelationIdProvider

```php
use SomeWork\CorrelationId\CorrelationIdProvider;

$id = CorrelationIdProvider::get();
```

## Custom ID Generator

Provide your own generator by implementing `IdGeneratorInterface` and passing it to the middleware.

## Configuration

### Symfony

Create the middleware service and event subscriber in your container. You can inject a PSR-3 logger or a custom `IdGeneratorInterface` implementation.

```yaml
# config/services.yaml
SomeWork\CorrelationId\CorrelationIdMiddleware:
    arguments:
        $logger: '@logger'                   # optional
        $generator: '@App\RequestIdGenerator' # optional
SomeWork\CorrelationId\Symfony\CorrelationIdSubscriber:
    arguments:
        $middleware: '@SomeWork\CorrelationId\CorrelationIdMiddleware'
    tags:
        - { name: kernel.event_subscriber }
```

### PSR-15

Instantiate the middleware with optional logger and generator and add it to your middleware stack.

```php
use SomeWork\CorrelationId\CorrelationIdMiddleware;
use Psr\Log\LoggerInterface;
use SomeWork\CorrelationId\IdGeneratorInterface;

$middleware = new CorrelationIdMiddleware(
    $logger,     // LoggerInterface|null
    $generator   // IdGeneratorInterface|null
);
```

## Extending the ID generator

Implement the `IdGeneratorInterface`:

```php
use SomeWork\CorrelationId\IdGeneratorInterface;

class MyGenerator implements IdGeneratorInterface
{
    public function generate(): string
    {
        return uniqid('', true);
    }
}

$middleware = new CorrelationIdMiddleware(null, new MyGenerator());
```

## Development

### Running checks

Run the code style fixer, static analysis and test suite:

```bash
vendor/bin/php-cs-fixer fix --diff --dry-run
vendor/bin/phpstan analyse --no-progress
vendor/bin/phpunit
```

### Continuous integration

GitHub Actions runs the workflow defined in `.github/workflows/ci.yml`. The matrix installs Symfony 6 and 7 and then executes php-cs-fixer, phpstan and phpunit with coverage.

## License

MIT
