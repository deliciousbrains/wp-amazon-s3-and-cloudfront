<?php
namespace Aws\CodeCommit;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS CodeCommit** service.
 *
 * @method \Aws\Result associateApprovalRuleTemplateWithRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise associateApprovalRuleTemplateWithRepositoryAsync(array $args = [])
 * @method \Aws\Result batchAssociateApprovalRuleTemplateWithRepositories(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchAssociateApprovalRuleTemplateWithRepositoriesAsync(array $args = [])
 * @method \Aws\Result batchDescribeMergeConflicts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchDescribeMergeConflictsAsync(array $args = [])
 * @method \Aws\Result batchDisassociateApprovalRuleTemplateFromRepositories(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchDisassociateApprovalRuleTemplateFromRepositoriesAsync(array $args = [])
 * @method \Aws\Result batchGetCommits(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchGetCommitsAsync(array $args = [])
 * @method \Aws\Result batchGetRepositories(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchGetRepositoriesAsync(array $args = [])
 * @method \Aws\Result createApprovalRuleTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createApprovalRuleTemplateAsync(array $args = [])
 * @method \Aws\Result createBranch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createBranchAsync(array $args = [])
 * @method \Aws\Result createCommit(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createCommitAsync(array $args = [])
 * @method \Aws\Result createPullRequest(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createPullRequestAsync(array $args = [])
 * @method \Aws\Result createPullRequestApprovalRule(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createPullRequestApprovalRuleAsync(array $args = [])
 * @method \Aws\Result createRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createRepositoryAsync(array $args = [])
 * @method \Aws\Result createUnreferencedMergeCommit(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createUnreferencedMergeCommitAsync(array $args = [])
 * @method \Aws\Result deleteApprovalRuleTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteApprovalRuleTemplateAsync(array $args = [])
 * @method \Aws\Result deleteBranch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteBranchAsync(array $args = [])
 * @method \Aws\Result deleteCommentContent(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteCommentContentAsync(array $args = [])
 * @method \Aws\Result deleteFile(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteFileAsync(array $args = [])
 * @method \Aws\Result deletePullRequestApprovalRule(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deletePullRequestApprovalRuleAsync(array $args = [])
 * @method \Aws\Result deleteRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteRepositoryAsync(array $args = [])
 * @method \Aws\Result describeMergeConflicts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeMergeConflictsAsync(array $args = [])
 * @method \Aws\Result describePullRequestEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describePullRequestEventsAsync(array $args = [])
 * @method \Aws\Result disassociateApprovalRuleTemplateFromRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise disassociateApprovalRuleTemplateFromRepositoryAsync(array $args = [])
 * @method \Aws\Result evaluatePullRequestApprovalRules(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise evaluatePullRequestApprovalRulesAsync(array $args = [])
 * @method \Aws\Result getApprovalRuleTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getApprovalRuleTemplateAsync(array $args = [])
 * @method \Aws\Result getBlob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getBlobAsync(array $args = [])
 * @method \Aws\Result getBranch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getBranchAsync(array $args = [])
 * @method \Aws\Result getComment(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCommentAsync(array $args = [])
 * @method \Aws\Result getCommentReactions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCommentReactionsAsync(array $args = [])
 * @method \Aws\Result getCommentsForComparedCommit(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCommentsForComparedCommitAsync(array $args = [])
 * @method \Aws\Result getCommentsForPullRequest(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCommentsForPullRequestAsync(array $args = [])
 * @method \Aws\Result getCommit(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCommitAsync(array $args = [])
 * @method \Aws\Result getDifferences(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getDifferencesAsync(array $args = [])
 * @method \Aws\Result getFile(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getFileAsync(array $args = [])
 * @method \Aws\Result getFolder(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getFolderAsync(array $args = [])
 * @method \Aws\Result getMergeCommit(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getMergeCommitAsync(array $args = [])
 * @method \Aws\Result getMergeConflicts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getMergeConflictsAsync(array $args = [])
 * @method \Aws\Result getMergeOptions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getMergeOptionsAsync(array $args = [])
 * @method \Aws\Result getPullRequest(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPullRequestAsync(array $args = [])
 * @method \Aws\Result getPullRequestApprovalStates(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPullRequestApprovalStatesAsync(array $args = [])
 * @method \Aws\Result getPullRequestOverrideState(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPullRequestOverrideStateAsync(array $args = [])
 * @method \Aws\Result getRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getRepositoryAsync(array $args = [])
 * @method \Aws\Result getRepositoryTriggers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getRepositoryTriggersAsync(array $args = [])
 * @method \Aws\Result listApprovalRuleTemplates(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listApprovalRuleTemplatesAsync(array $args = [])
 * @method \Aws\Result listAssociatedApprovalRuleTemplatesForRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listAssociatedApprovalRuleTemplatesForRepositoryAsync(array $args = [])
 * @method \Aws\Result listBranches(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listBranchesAsync(array $args = [])
 * @method \Aws\Result listPullRequests(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPullRequestsAsync(array $args = [])
 * @method \Aws\Result listRepositories(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRepositoriesAsync(array $args = [])
 * @method \Aws\Result listRepositoriesForApprovalRuleTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRepositoriesForApprovalRuleTemplateAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result mergeBranchesByFastForward(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise mergeBranchesByFastForwardAsync(array $args = [])
 * @method \Aws\Result mergeBranchesBySquash(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise mergeBranchesBySquashAsync(array $args = [])
 * @method \Aws\Result mergeBranchesByThreeWay(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise mergeBranchesByThreeWayAsync(array $args = [])
 * @method \Aws\Result mergePullRequestByFastForward(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise mergePullRequestByFastForwardAsync(array $args = [])
 * @method \Aws\Result mergePullRequestBySquash(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise mergePullRequestBySquashAsync(array $args = [])
 * @method \Aws\Result mergePullRequestByThreeWay(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise mergePullRequestByThreeWayAsync(array $args = [])
 * @method \Aws\Result overridePullRequestApprovalRules(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise overridePullRequestApprovalRulesAsync(array $args = [])
 * @method \Aws\Result postCommentForComparedCommit(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise postCommentForComparedCommitAsync(array $args = [])
 * @method \Aws\Result postCommentForPullRequest(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise postCommentForPullRequestAsync(array $args = [])
 * @method \Aws\Result postCommentReply(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise postCommentReplyAsync(array $args = [])
 * @method \Aws\Result putCommentReaction(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putCommentReactionAsync(array $args = [])
 * @method \Aws\Result putFile(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putFileAsync(array $args = [])
 * @method \Aws\Result putRepositoryTriggers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putRepositoryTriggersAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result testRepositoryTriggers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise testRepositoryTriggersAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateApprovalRuleTemplateContent(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateApprovalRuleTemplateContentAsync(array $args = [])
 * @method \Aws\Result updateApprovalRuleTemplateDescription(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateApprovalRuleTemplateDescriptionAsync(array $args = [])
 * @method \Aws\Result updateApprovalRuleTemplateName(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateApprovalRuleTemplateNameAsync(array $args = [])
 * @method \Aws\Result updateComment(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateCommentAsync(array $args = [])
 * @method \Aws\Result updateDefaultBranch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateDefaultBranchAsync(array $args = [])
 * @method \Aws\Result updatePullRequestApprovalRuleContent(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updatePullRequestApprovalRuleContentAsync(array $args = [])
 * @method \Aws\Result updatePullRequestApprovalState(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updatePullRequestApprovalStateAsync(array $args = [])
 * @method \Aws\Result updatePullRequestDescription(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updatePullRequestDescriptionAsync(array $args = [])
 * @method \Aws\Result updatePullRequestStatus(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updatePullRequestStatusAsync(array $args = [])
 * @method \Aws\Result updatePullRequestTitle(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updatePullRequestTitleAsync(array $args = [])
 * @method \Aws\Result updateRepositoryDescription(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateRepositoryDescriptionAsync(array $args = [])
 * @method \Aws\Result updateRepositoryName(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateRepositoryNameAsync(array $args = [])
 */
class CodeCommitClient extends AwsClient {}
