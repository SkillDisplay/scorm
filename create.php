<?php

declare(strict_types=1);

namespace SkillDisplay\Scorm;

require 'vendor/autoload.php';

use SkillDisplay\PHPToolKit\Api\Skill;
use SkillDisplay\PHPToolKit\Configuration\Settings;
use SkillDisplay\PHPToolKit\Entity\SkillSet;

// We don't need an APIKey or Verifier Credentials, just create some empty settings
$mySettings = new Settings('none');

// we want to create Verification Buttons styled in the standard SkillDisplay Design for "Choosing a secure password"
// A click on a link created this way will trigger the Verification interface on the SkillDisplay platform.
// The user will be invited to choose a verifier for the according level and submit a request for skill verification which can be granted or denied.
$myGuzzle =  new \GuzzleHttp\Client();
$mySkillSetAPI = new \SkillDisplay\PHPToolKit\Api\SkillSet($mySettings, $myGuzzle);
$mySkillAPI = new Skill($mySettings, $myGuzzle);

/* @var \SkillDisplay\PHPToolKit\Entity\SkillSet $mySkillSet */
$mySkillSet = $mySkillSetAPI->getById(108);
$mySkills = $mySkillSet->getSkills();
sort($mySkills);

$writer = new Writer('templates', 'output');

/* @var \SkillDisplay\PHPToolKit\Entity\Skill $skill */
foreach($mySkills as $skill){
    $fullSkill = $mySkillAPI->getById($skill->getId());
    $writer->writeSkillToHTML($fullSkill);

}
