<?php

declare(strict_types = 1);

namespace Skilldisplay\Scorm;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use SkillDisplay\PHPToolKit\Configuration\Settings;
use SkillDisplay\PHPToolKit\Entity\SkillSet;
use SkillDisplay\PHPToolKit\Entity\SkillSet as Entity;

class Search
{
    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(
        Settings $settings,
        Client $client
    )
    {
        $this->settings = $settings;
        $this->client = $client;
    }

    public function searchByName(string $needle): array
    {
        $url = $this->settings->getAPIUrl() . '/api/v1/search?q='.urlencode($needle).'&skills=0&skillsets=1&verifications=0&organisations=0';

        try {
            $result = $this->client->send(new Request(
                'GET',
                $url,
                [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->settings->getApiKey()
                ]
            ));
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new \InvalidArgumentException('Given SkillSet with keyword "' . $needle . '" not available.', 1601881616);
            }
            throw $e;
        }

        if ($result->getStatusCode() !== 200) {
            throw new \Exception('Did not get proper response for SkillSet.', 1600694312);
        }

        $body = (string)$result->getBody();

        if (strpos($body, 'Oops, an error occurred') !== false) {
            throw new \Exception('Did not get proper response for SkillSet. SkillSet with needle "' . $needle . '" does probably not exist.', 1600694312);
        }
        $response_array = json_decode($body, true);
        $skillsets = [];

        foreach($response_array['skillSets'] as $skillset){
            $skillsets[] = SkillSet::createFromJson(json_encode($skillset),$this->settings)->toArray();
        }

        return $skillsets;

    }
}