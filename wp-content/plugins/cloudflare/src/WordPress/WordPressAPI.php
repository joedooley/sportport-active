<?php

namespace CF\WordPress;

use CF\Integration\IntegrationAPIInterface;
use CF\DNSRecord;

class WordPressAPI implements IntegrationAPIInterface
{
    const API_NONCE = 'cloudflare-db-api-nonce';

    private $dataStore;

    /**
     * @param $dataStore
     */
    public function __construct(DataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    /**
     * @param $domain_name
     *
     * @return mixed
     */
    public function getDNSRecords($domain_name)
    {
        return;
    }

    /**
     * @param $domain_name
     * @param DNSRecord $DNSRecord
     *
     * @return mixed
     */
    public function addDNSRecord($domain_name, DNSRecord $DNSRecord)
    {
        return;
    }

    /**
     * @param $domain_name
     * @param DNSRecord $DNSRecord
     *
     * @return mixed
     */
    public function editDNSRecord($domain_name, DNSRecord $DNSRecord)
    {
        return;
    }

    /**
     * @param $domain_name
     * @param DNSRecord $DNSRecord
     *
     * @return mixed
     */
    public function removeDNSRecord($domain_name, DNSRecord $DNSRecord)
    {
        return;
    }

    /**
     * @return mixed
     */
    public function getHostAPIKey()
    {
        return;
    }

    /**
     * We wrap the return value with an array to be consistent between
     * other plugins.
     * 
     * @param null $userId
     *
     * @return mixed
     */
    public function getDomainList($userId = null)
    {
        $cachedDomainName = $this->dataStore->getDomainNameCache();
        if (empty($cachedDomainName)) {
            return;
        }

        return array($cachedDomainName);
    }

    /**
     * @return string
     */
    public function getOriginalDomain()
    {
        return $this->formatDomain($_SERVER['SERVER_NAME']);
    }

    /**
     * @return bool
     */
    public function setDomainNameCache($newDomainName)
    {
        return $this->dataStore->setDomainNameCache($newDomainName);
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->dataStore->getCloudFlareEmail();
    }

    /**
     * @param domain name
     *
     * @return string
     */
    private function formatDomain($domainName)
    {
        // Remove instances which are before the domain name:
        // * http
        // * https
        // * www
        // * user:pass@
        preg_match_all('/^(?:https?:\/\/)?(?:[^@\/\n]+@)?(?:www\.)?([^:\/\n]+)/im', $domainName, $matches);
        $formattedDomain = $matches[1][0];

        return $formattedDomain;
    }

    /**
     * @return mixed
     */
    public function getValidCloudflareDomain($response, $domainName)
    {
        foreach ($response['result'] as $zone) {
            if (Utils::isSubdomainOf($domainName, $zone['name'])) {
                return $zone['name'];
            }
        }

        return false;
    }

    public function clearDataStore()
    {
        $pluginKeys = \CF\API\Plugin::getPluginSettingsKeys();

        // Delete Plugin Setting Options
        foreach ($pluginKeys as $optionName) {
            $this->dataStore->clear($optionName);
        }

        // Delete DataStore Options
        $this->dataStore->clear(DataStore::API_KEY);
        $this->dataStore->clear(DataStore::EMAIL);
        $this->dataStore->clear(DataStore::CACHED_DOMAIN_NAME);
    }
}
