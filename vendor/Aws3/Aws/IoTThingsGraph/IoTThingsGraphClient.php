<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoTThingsGraph;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Things Graph** service.
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result associateEntityToThing(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise associateEntityToThingAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createFlowTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createFlowTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createSystemInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createSystemInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createSystemTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createSystemTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteFlowTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteFlowTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteNamespace(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteNamespaceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteSystemInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteSystemInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteSystemTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteSystemTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deploySystemInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deploySystemInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deprecateFlowTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deprecateFlowTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deprecateSystemTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deprecateSystemTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeNamespace(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeNamespaceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result dissociateEntityFromThing(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise dissociateEntityFromThingAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getEntities(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getEntitiesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getFlowTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getFlowTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getFlowTemplateRevisions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getFlowTemplateRevisionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getNamespaceDeletionStatus(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getNamespaceDeletionStatusAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getSystemInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getSystemInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getSystemTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getSystemTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getSystemTemplateRevisions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getSystemTemplateRevisionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getUploadStatus(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getUploadStatusAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listFlowExecutionMessages(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listFlowExecutionMessagesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result searchEntities(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise searchEntitiesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result searchFlowExecutions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise searchFlowExecutionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result searchFlowTemplates(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise searchFlowTemplatesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result searchSystemInstances(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise searchSystemInstancesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result searchSystemTemplates(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise searchSystemTemplatesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result searchThings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise searchThingsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result undeploySystemInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise undeploySystemInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateFlowTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFlowTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateSystemTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateSystemTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result uploadEntityDefinitions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise uploadEntityDefinitionsAsync(array $args = [])
 */
class IoTThingsGraphClient extends AwsClient {}
