<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\AbstractHasDispatcher;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchBuilder;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchSizeDivisor;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchClosureTransfer;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version;
/**
 * Apply a callback to the contents of a {@see ResourceIteratorInterface}
 * @deprecated Will be removed in a future version and is no longer maintained. Use the Batch\ abstractions instead.
 * @codeCoverageIgnore
 */
class ResourceIteratorApplyBatched extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\AbstractHasDispatcher
{
    /** @var callable|array */
    protected $callback;
    /** @var ResourceIteratorInterface */
    protected $iterator;
    /** @var integer Total number of sent batches */
    protected $batches = 0;
    /** @var int Total number of iterated resources */
    protected $iterated = 0;
    public static function getAllEvents()
    {
        return array(
            // About to send a batch of requests to the callback
            'iterator_batch.before_batch',
            // Finished sending a batch of requests to the callback
            'iterator_batch.after_batch',
            // Created the batch object
            'iterator_batch.created_batch',
        );
    }
    /**
     * @param ResourceIteratorInterface $iterator Resource iterator to apply a callback to
     * @param array|callable            $callback Callback method accepting the resource iterator
     *                                            and an array of the iterator's current resources
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\ResourceIteratorInterface $iterator, $callback)
    {
        $this->iterator = $iterator;
        $this->callback = $callback;
        \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version::warn(__CLASS__ . ' is deprecated');
    }
    /**
     * Apply the callback to the contents of the resource iterator
     *
     * @param int $perBatch The number of records to group per batch transfer
     *
     * @return int Returns the number of iterated resources
     */
    public function apply($perBatch = 50)
    {
        $this->iterated = $this->batches = $batches = 0;
        $that = $this;
        $it = $this->iterator;
        $callback = $this->callback;
        $batch = \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchBuilder::factory()->createBatchesWith(new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchSizeDivisor($perBatch))->transferWith(new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchClosureTransfer(function (array $batch) use($that, $callback, &$batches, $it) {
            $batches++;
            $that->dispatch('iterator_batch.before_batch', array('iterator' => $it, 'batch' => $batch));
            call_user_func_array($callback, array($it, $batch));
            $that->dispatch('iterator_batch.after_batch', array('iterator' => $it, 'batch' => $batch));
        }))->autoFlushAt($perBatch)->build();
        $this->dispatch('iterator_batch.created_batch', array('batch' => $batch));
        foreach ($this->iterator as $resource) {
            $this->iterated++;
            $batch->add($resource);
        }
        $batch->flush();
        $this->batches = $batches;
        return $this->iterated;
    }
    /**
     * Get the total number of batches sent
     *
     * @return int
     */
    public function getBatchCount()
    {
        return $this->batches;
    }
    /**
     * Get the total number of iterated resources
     *
     * @return int
     */
    public function getIteratedCount()
    {
        return $this->iterated;
    }
}
