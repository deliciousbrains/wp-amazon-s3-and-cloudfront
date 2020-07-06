<?php
namespace Aws\RDSDataService;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS RDS DataService** service.
 * @method \Aws\Result batchExecuteStatement(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchExecuteStatementAsync(array $args = [])
 * @method \Aws\Result beginTransaction(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise beginTransactionAsync(array $args = [])
 * @method \Aws\Result commitTransaction(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise commitTransactionAsync(array $args = [])
 * @method \Aws\Result executeSql(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise executeSqlAsync(array $args = [])
 * @method \Aws\Result executeStatement(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise executeStatementAsync(array $args = [])
 * @method \Aws\Result rollbackTransaction(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise rollbackTransactionAsync(array $args = [])
 */
class RDSDataServiceClient extends AwsClient {}
