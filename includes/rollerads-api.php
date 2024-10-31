<?php

class RollerAdsApi {
    /**
     * Headers required for API requests
     * @return array
     */
    function getRequestHeaders(): array
    {
        $options = get_option('rollerads_options');
        $apiKey = $options['api_key'] ?? null;

        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Access-Token' => $apiKey,
            'X-From-Source' => 'wp_plugin',
        ];
    }

    /**
     * Token verification
     * @return bool
     * @throws Exception
     */
    function checkApiKey(): bool
    {
        try {
            $response = Requests::request(ROLLERADS_API_URI . '/whoami', $this->getRequestHeaders());
        } catch (Requests_Exception $e) {
            return false;
        }

        $result = json_decode($response->body, true);

        return !isset($result['response']['is_logged_in']) && $response->status_code === 200;
    }

    /**
     * Site creation
     * @return int
     * @throws Exception
     */
    function siteCreate(): int
    {
        $requestBody = [
            'site_url' => ROLLER_ADS_ENV === 'develop' ? 'test.com' : get_site_url(),
        ];

        try {
            $response = Requests::request(
                ROLLERADS_API_URI . '/sites/new',
                $this->getRequestHeaders(),
                json_encode($requestBody),
                'POST'
            );
        } catch (Requests_Exception $e) {
            throw new Exception('Site create request error');
        }

        $result = json_decode($response->body, true);

        return $result['response']['site_id'];
    }

    /**
     * Getting all zones
     * @return array
     * @throws Exception
     */
    function zoneList(): array
    {
        try {
            $response = Requests::request(ROLLERADS_API_URI . '/sites/zone-list', $this->getRequestHeaders());
        } catch (Requests_Exception $e) {
            throw new Exception('Zone list request error');
        }

        $result = json_decode($response->body, true);

        return $result['response'];
    }

    /**
     * Getting a zone
     * @param int $zoneId
     * @return array
     * @throws Exception
     */
    function getZone(int $zoneId): array
    {
        try {
            $response = Requests::request(ROLLERADS_API_URI . "/zones/$zoneId", $this->getRequestHeaders());
        } catch (Requests_Exception $e) {
            throw new Exception('Get zone request error');
        }

        $result = json_decode($response->body, true);

        return $result['response'];
    }

    /**
     * Create a zone
     * @param int $siteID
     * @return int
     * @throws Exception
     */
    function zoneCreate(int $siteID): int
    {
        $requestBody = [
            'format_id' => 1,
            'zone_title' => 'Created from WordPress plugin',
        ];

        try {
            $response = Requests::request(
                ROLLERADS_API_URI . "/sites/$siteID/zones/new",
                $this->getRequestHeaders(),
                json_encode($requestBody),
                'POST'
            );
        } catch (Requests_Exception $e) {
            throw new Exception('Site create request error');
        }

        $result = json_decode($response->body, true);

        return $result['response']['zone_id'];
    }

    /**
     * Getting codes
     * @param int $siteID
     * @param int $zoneID
     * @return array
     * @throws Exception
     */
    function getCodes(int $siteID, int $zoneID): array
    {
        try {
            $response = Requests::request(
                ROLLERADS_API_URI . "/sites/$siteID/zones/$zoneID/codes",
                $this->getRequestHeaders(),
                [
                    "render" => 1,
                ]
            );
        } catch (Requests_Exception $e) {
            throw new Exception('Get codes request error');
        }

        $result = json_decode($response->body, true);

        return $result['response']['codes'];
    }
}