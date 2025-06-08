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

## License

MIT
