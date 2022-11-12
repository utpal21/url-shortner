<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * SafeBrowsing
 */
class SafeBrowsing
{
    protected ?string $apiKey = null;
    protected string $clientId;
    protected string $clientVersion;
    protected array $threatTypes;
    protected array $platformTypes;
    
    /**
     * __construct
     *
     * @param  mixed $apiKey
     * @return void
     */
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('safe-browsing.google.api_key');
        $this->clientId = config('safe-browsing.google.clientId');
        $this->clientVersion = config('safe-browsing.google.clientVersion');
        $this->threatTypes = config('safe-browsing.google.threatTypes');
        $this->platformTypes = config('safe-browsing.google.threatPlatforms');
    }
    
    /**
     * isSafeUrl
     *
     * @param  mixed $url
     * @param  mixed $returnType
     * @return bool
     */
    public function isSafeUrl(string $url, bool $returnType = false): bool|string
    {
        $this->checkApiKey();
        $result = $this->getApiResult($url);
        if (is_array($result) and isset($result['matches'])) {
            if ($returnType) {
                foreach ($result['matches'] as $match) {
                    if ($match['threat']['url'] === $url) {
                        return $match['threatType'];
                    }
                }
            }

            return false;
        }

        return true;
    }
    
    /**
     * checkApiKey
     *
     * @return void
     */
    protected function checkApiKey(): void
    {
        if (is_null($this->apiKey) or empty($this->apiKey)) {
            throw new \Exception('API Key is required');
        }
    }
    
    /**
     * getApiResult
     *
     * @param  mixed $url
     * @return array
     */
    protected function getApiResult(string $url): array|string
    {
        $lookupUrl = sprintf("https://safebrowsing.googleapis.com/v4/threatMatches:find?key=%s", $this->apiKey);
        $lookupData = [
            'client' => [
                'clientId' => $this->clientId,
                'clientVersion' => $this->clientVersion,
            ],
            'threatInfo' => [
                'threatTypes' => $this->threatTypes,
                'platformTypes' => $this->platformTypes,
                'threatEntryTypes' => ['URL'],
                'threatEntries' => [
                    [
                        'url' => $url,
                    ],
                ],
            ],
        ];
        $response = Http::post($lookupUrl, $lookupData);
        if ($response->failed()) {
            throw new \Exception($response->json('error.message'));
        }

        return $response->json();
    }
}