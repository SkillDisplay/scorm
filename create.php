<?php

declare(strict_types=1);

namespace SkillDisplay\Scorm;

require 'vendor/autoload.php';

use SkillDisplay\PHPToolKit\Api\Skill;
use SkillDisplay\PHPToolKit\Configuration\Settings;
use Alchemy\Zippy\Zippy;

// Prompt user to enter a SkillSet ID
$skillsetID = intval(readline('Please enter the SkillSet ID: '));

require('config/config.inc.php');
// We don't need an APIKey or Verifier Credentials, just create some empty settings
$mySettings = new Settings($apiKey);

// we want to create Verification Buttons styled in the standard SkillDisplay Design for "Choosing a secure password"
// A click on a link created this way will trigger the Verification interface on the SkillDisplay platform.
// The user will be invited to choose a verifier for the according level and submit a request for skill verification which can be granted or denied.
$myGuzzle =  new \GuzzleHttp\Client();
$mySkillSetAPI = new \SkillDisplay\PHPToolKit\Api\SkillSet($mySettings, $myGuzzle);
$mySkillAPI = new Skill($mySettings, $myGuzzle);
$mySearchAPI = new Search($mySettings, $myGuzzle);

/* @var \SkillDisplay\PHPToolKit\Entity\SkillSet $mySkillSet */
$mySkillSet = $mySkillSetAPI->getById($skillsetID);
$mySkills = $mySkillSet->getSkills();
sort($mySkills);

$writer = new Writer('templates', 'output');

/* @var \SkillDisplay\PHPToolKit\Entity\Skill $skill */
foreach($mySkills as $skill){
    echo "Loading details for Skill ".$skill->getTitle().' ...'.PHP_EOL;
    $fullSkill = $mySkillAPI->getById($skill->getId());
    echo "Loading related SkillSets for Skill ".$skill->getTitle().' ...'.PHP_EOL;
    $relatedSkillSets = $mySearchAPI->searchByName($fullSkill->getTitle());
    $relatedSkillSets = groupByCategory($relatedSkillSets, $mySettings);
    $writer->writeSkillToHTML($fullSkill, $relatedSkillSets);
}

$writer->writeImsManifest($mySkillSet, $mySkills);

// Load Zippy
$zippy = Zippy::load();

$archive = $zippy->create(__DIR__.DIRECTORY_SEPARATOR.'output'.DIRECTORY_SEPARATOR.$writer->getShortTitleFromTitle($mySkillSet->getName()).'_scorm.zip', array(
    'shared' => './output/shared',
    'skills' => './output/skills',
    'adlcp_rootv1p2.xsd' => './output/adlcp_rootv1p2.xsd',
    'ims_xml.xsd' => './output/ims_xml.xsd',
    'imscp_rootv1p1p2.xsd' => './output/imscp_rootv1p1p2.xsd',
    'imsmanifest.xml' => './output/imsmanifest.xml',
    'imsmd_rootv1p2p1.xsd' => './output/imsmd_rootv1p2p1.xsd'
));

function groupByCategory(array $skillsets, Settings $settings){
    $sorted = array();

    /* @var \SkillDisplay\PHPToolKit\Entity\SkillSet $skillset */
    foreach($skillsets as $skillset){
        $skillset['url'] = $settings->getMySkillDisplayUrl() . '/en/skillset/'.$skillset['uid'];
        $sorted[$skillset['firstCategoryTitle']][] = $skillset;
    }
    return $sorted;
}
