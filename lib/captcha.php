<?php
require '../vendor/autoload.php';

// Include Google Cloud dependencies using Composer
use Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient;
use Google\Cloud\RecaptchaEnterprise\V1\Event;
use Google\Cloud\RecaptchaEnterprise\V1\Assessment;
use Google\Cloud\RecaptchaEnterprise\V1\TokenProperties\InvalidReason;

/**
 * Create an assessment to analyse the risk of a UI action.
 * @param string $recaptchaKey The reCAPTCHA key associated with the site/app
 * @param string $token The generated token obtained from the client.
 * @param string $project Your Google Cloud project ID.
 * @param string $action Action name corresponding to the token.
 */
function create_assessment(
  string $recaptchaKey,
  string $token,
  string $project,
  string $action
) {
  // Create the reCAPTCHA client.
  // TODO: Cache the client generation code (recommended) or call client.close() before exiting the method.
  $client = new RecaptchaEnterpriseServiceClient();
  $projectName = $client->projectName($project);

  // Set the properties of the event to be tracked.
  $event = (new Event())
    ->setSiteKey($recaptchaKey)
    ->setToken($token);

  // Build the assessment request.
  $assessment = (new Assessment())
    ->setEvent($event);

  try {
    $response = $client->createAssessment(
      $projectName,
      $assessment
    );

    // Check if the token is valid.
    if ($response->getTokenProperties()->getValid() == false) {
      $reason = InvalidReason::name($response->getTokenProperties()->getInvalidReason());
      throw new Exception("Invalid token: $reason");
    }

    if ($response->getTokenProperties()->getAction() !== $action) {

      throw new Exception('Action mismatch in reCAPTCHA');
    }

    // Get the risk score and the reason(s).
    // For more information on interpreting the assessment, see:
    // https://cloud.google.com/recaptcha-enterprise/docs/interpret-assessment
    $score = $response->getRiskAnalysis()->getScore();
    $threshold = 0.5;
    if ($score < $threshold) {
      throw new Exception("reCAPTCHA score is below threshold: $score");
    }

    return true;
  } catch (exception $e) {
    error_log('reCAPTCHA verification failed: ' . $e->getMessage());
    return false;
  }
}
