#!/usr/bin/php
<?php
declare(strict_types=1);

use GunnaPHP\SMA\SunnyPortal;

require __DIR__ . '/vendor/autoload.php';

class SmaDataTransfer
{

    /**
     * @var array
     */
    protected $settings;

    protected $sunnyPortalOpenHabItemMap = [
        'PV' => 'sunnyportal_pv_output',
        'FeedIn' => 'sunnyportal_grid_feedin',
        'GridConsumption' => 'sunnyportal_grid_consumption',
        'SelfConsumption' => 'sunnyportal_self_consumption',
        'TotalConsumption' => 'sunnyportal_total_consumption',
        'SelfConsumptionQuote' => 'sunnyportal_self_consumption_ratio',
        'AutarkyQuote' => 'sunnyportal_autarky_ratio',
    ];

    public function __construct()
    {
        $this->settings = include(__DIR__ . '/settings.php');
    }

    public function updateValuesInOpenHab(): void
    {

        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->settings['openHabRestUrl'] . '/rest/items/',
            'timeout' => 2.0,
            'headers' => [
                'Content-Type' => 'text/plain',
                'Accept' => 'application/json',
            ]
        ]);

        $data = $this->getDataFromSunnyPortal();

        foreach ($this->sunnyPortalOpenHabItemMap as $sunnyPortalKey => $openHabKey) {
            $client->post(new \GuzzleHttp\Psr7\Uri($openHabKey), ['body' => $data[$sunnyPortalKey] ?? 0]);
        }

    }

    protected function getDataFromSunnyPortal(): array
    {
        $portal = new SunnyPortal([
            'username' => $this->settings['sunnyPortalUserName'],
            'password' => $this->settings['sunnyPortalPassword']
        ]);

        return (array)$portal->liveData();
    }
}

(new SmaDataTransfer())->updateValuesInOpenHab();
