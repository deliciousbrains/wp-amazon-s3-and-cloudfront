<?php

namespace DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise;

/**
 * A promise that has been rejected.
 *
 * Thenning off of this promise will invoke the onRejected callback
 * immediately and ignore other callbacks.
 */
class RejectedPromise implements \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\PromiseInterface
{
    private $reason;
    public function __construct($reason)
    {
        if (is_object($reason) && method_exists($reason, 'then')) {
            throw new \InvalidArgumentException('You cannot create a RejectedPromise with a promise.');
        }
        $this->reason = $reason;
    }
    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        // If there's no onRejected callback then just return self.
        if (!$onRejected) {
            return $this;
        }
        $queue = \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Utils::queue();
        $reason = $this->reason;
        $p = new \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise([$queue, 'run']);
        $queue->add(static function () use($p, $reason, $onRejected) {
            if (\DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Is::pending($p)) {
                try {
                    // Return a resolved promise if onRejected does not throw.
                    $p->resolve($onRejected($reason));
                } catch (\Throwable $e) {
                    // onRejected threw, so return a rejected promise.
                    $p->reject($e);
                } catch (\Exception $e) {
                    // onRejected threw, so return a rejected promise.
                    $p->reject($e);
                }
            }
        });
        return $p;
    }
    public function otherwise(callable $onRejected)
    {
        return $this->then(null, $onRejected);
    }
    public function wait($unwrap = true, $defaultDelivery = null)
    {
        if ($unwrap) {
            throw \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Create::exceptionFor($this->reason);
        }
        return null;
    }
    public function getState()
    {
        return self::REJECTED;
    }
    public function resolve($value)
    {
        throw new \LogicException("Cannot resolve a rejected promise");
    }
    public function reject($reason)
    {
        if ($reason !== $this->reason) {
            throw new \LogicException("Cannot reject a rejected promise");
        }
    }
    public function cancel()
    {
        // pass
    }
}
