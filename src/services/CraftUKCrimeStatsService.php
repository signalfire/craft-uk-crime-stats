<?php
/**
 * UK Crime stats plugin for Craft CMS 3.x
 *
 * @link      https://github.com/signalfire
 * @copyright Copyright (c) 2019 Robert Coster
 */

namespace signalfire\craftukcrimestats\services;

use yii\base\Component;

use Craft;
use signalfire\craftukcrimestats\CraftUKCrimeStats;

/**
* @author  Robert Coster
* @package craftukcrimestats
* @since   1.0.0
*/
class CraftUKCrimeStatsService extends Component
{
    /**
     * Makes HTTP request using Guzzle
     * 
     * @param string $method   GET, POST etc method to use
     * @param string $endpoint URL endpoint to connect to
     * @param array  $data     Data to send with request
     * 
     * @return array
     */
    private function makeRequest(string $method, string $endpoint, array $data = []) : array
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => CraftUKCrimeStats::getInstance()->getSettings()->base,
            'timeout' => CraftUKCrimeStats::getInstance()->getSettings()->timeout      
        ]);

        try{

            $response = $client->request($method, $endpoint, $data);

            $body = json_decode($response->getBody(), true);

            return [
                'statusCode' => $response->getStatusCode(),
                'reason' => $response->getReasonPhrase(),
                'body' => $body
            ];

        } catch(\Exception $ex) {

            return [
                'error' => true,
                'reason' => $ex->getMessage()
            ];

        }       
    }

    /**
     * Merge 2 arrays to return a option array
     * 
     * @param array $defaults Default options
     * @param array $params   User provided options
     * 
     * @return array
     */
    private function createOptions(array $defaults = [], array $params = []) : array
    {
        return array_merge($defaults, $params);
    }

    /**
     * Create url from provided name and options
     * 
     * @param string $name    Name of endpoint to create url for
     * @param array  $options Array of options 
     * 
     * @return array
     */
    private function createUrl(string $name, array $options = []) : string
    {
        $endpoints = [
            'forces' => '/api/forces',
            'force' => '/api/forces/%s',
            'forceofficers' => '/api/forces/%s/people',
            'streetcrimepoint' => '/api/crimes-street/%s?lat=%s&lng=%s&month=%s',
            'streetcrimecustom' => '/api/crimes-street/%s?poly=%s&date=%s',
            'crimesatlocationid' => '/api/crimes-at-location?date=%s&location_id=%s',
            'crimesatlocationpoint' => '/api/crimes-at-location?date=%s&lat=%s&lng=%s',
            'crimesnolocation' => '/api/crimes-no-location?category=%s&force=%s&date=%s',
            'categories' => '/api/crime-categories?date=%s',
            'crimeoutcomes' => '/api/outcomes-for-crime/%s',
            'neighbourhoods' => '/api/%s/neighbourhoods',
            'neighbourhood' => '/api/%s/%s',
            'neighbourhoodboundary' => '/api/%s/%s/boundary',
            'neighbourhoodteam' => '/api/%s/%s/people',
            'neighbourhoodevents' => '/api/%s/%s/events',
            'neighbourhoodpriorities' => '/api/%s/%s/priorities',
            'neighbourhoodlocate' => '/api/locate-neighbourhood?q=%s,%s',
            'stopsearcharea' => '/api/stops-street?lat=%s&lng=%s&date=%s',
            'stopsearchcustom' => '/api/stops-street?poly=%s&date=%s',
            'stopsearchlocationid' => '/api/stops-at-location?location_id=%s&date=%s',
            'stopsearchnolocation' => '/api/stops-no-location?force=%s&date=%s',
            'stopsearchforce' => '/api/stops-force?force=%s&date=%s',
        ];
        return vsprintf($endpoints[$name], array_map(function($option) { return $option; }, $options));
    }

    /**
     * Create a string of long, lat representing polygon
     * 
     * @param array $coordinates Coordinates for polygon
     * 
     * @return string
     */
    private function createPolygonFromCoords(array $coordinates = []) : string
    {
        return implode(':', array_map(function($coords){
            return implode(',', $coords);
        }, $coordinates));
    }

    /**
     * Create a key for caching based on URL hashed
     * 
     * @param string $url URL to hash
     */
    private function createCacheKey(string $url) : string
    {
        return md5($url);
    }

    /**
     * Check if data in cache or not. Return from live or cache
     * 
     * @param string $method GET, POST method
     * @param string $url    URL to get
     *
     * @return array
     */
    private function makeRequestOrLoadFromCache(string $method, string $url) : array
    {
        $key = $this->createCacheKey($url);

        if (Craft::$app->cache->exists($key)){
            return Craft::$app->cache->get($key);
        }else{
            $response = $this->makeRequest($method, $url);
            Craft::$app->cache->set($key, $response, CraftUKCrimeStats::getInstance()->getSettings()->cache);  
            return $response;
        }
    }

    /**
     * Generate URL for request based on name, params
     * 
     * @param string $name   Name of function
     * @param array  $params Params passed by user
     * @param string $method Method to use for request
     * 
     * @return array
     */
    public function getPoliceAndCrimeData(string $name, array $params = [], string $method = 'GET') : array
    {
        switch(\strtolower($name)){
            case 'forces':
                $url = $this->createUrl(strtolower($name), []);
                break;
            case 'force':
            case 'forceofficers':
                $defaults = ['name' => 'metropolitan'];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'streetcrimepoint': 
                $defaults = [
                    'category' => 'all-crime', 
                    'lat' => 51.507375, 
                    'lng' => -0.127537, 
                    'month' => date('Y-m', strtotime('-1 month'))
                ];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'streetcrimecustom':
                $defaults = [
                    'category' => 'all-crime', 
                    'coordinates' => [
                        [52.268,0.543],
                        [52.794,0.238],
                        [52.130,0.478]
                    ], 
                    'month' => date('Y-m', strtotime('-1 month'))
                ];
                $options = $this->createOptions($defaults, $params);                
                $url = $this->createUrl(
                    strtolower($name),
                    [
                        'category' => $options['category'],
                        'polygon' => $this->createPolygonFromCoords($options['coordinates']),
                        'month' => $options['month']
                    ]
                );
                break;         
            case 'crimesatlocationid':
                $defaults = [
                    'month' => date('Y-m', strtotime('-1 month')),                
                    'location' => 884227
                ];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'crimesatlocationpoint':
                $defaults = [
                    'month' => date('Y-m', strtotime('-1 month')),             
                    'lat' => 52.629729,
                    'lng' => -1.131592,
                ];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'crimesnolocation':
                $defaults = [
                    'category' => 'all-crime',
                    'force' => 'leicestershire',
                    'month' => date('Y-m', strtotime('-1 month'))
                ];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'categories': 
                $defaults = [
                    'month' => date('Y-m', strtotime('-1 month'))
                ];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'crimeoutcomes':
                $defaults = ['id' => '590d68b69228a9ff95b675bb4af591b38de561aa03129dc09a03ef34f537588c'];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'neighbourhoods':
                $defaults = ['force' => 'metropolitan'];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'neighbourhood':
            case 'neighbourhoodboundary': 
            case 'neighbourhoodteam': 
            case 'neighbourhoodevents':
            case 'neighbourhoodpriorities':
                $defaults = [
                    'force' => 'leicestershire',
                    'id' => 'NC04'
                ];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;                       
            case 'neighbourhoodlocate':
                $defaults = [
                    'lat' => 51.500617,
                    'lng' => -0.124629
                ];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'stopsearcharea':
                $defaults = [
                    'lat' => 52.629729,
                    'lng' => -1.131592,
                    'month' => date('Y-m', strtotime('-1 month'))
                ];
                $options = $this->createOptions($defaults, $params);
                $url = $this->createUrl(strtolower($name), $options);
                break;
            case 'stopsearchcustom':
                $defaults = [
                    'coordinates' => [
                        [52.2,0.5],
                        [52.8,0.2], 
                        [52.1,0.88], 
                    ], 
                    'month' => date('Y-m', strtotime('-1 month'))
                ];
                $options = $this->createOptions($defaults, $params);                
                $url = $this->createUrl(
                    strtolower($name),
                    [
                        'polygon' => $this->createPolygonFromCoords($options['coordinates']),
                        'month' => $options['month']
                    ]
                );                
                break;                 
            case 'stopsearchlocationid': 
                $defaults = [
                    'location' => 883407,
                    'month' => date('Y-m', strtotime('-1 month'))
                ];
                $options = $this->createOptions($defaults, $params);                
                $url = $this->createUrl(strtolower($name), $options);
                break;
            
            case 'stopsearchnolocation':
            case 'stopsearchforce':
                $defaults = [
                    'force' => 'cleveland',
                    'month' => date('Y-m', strtotime('-1 month'))
                ];
                $options = $this->createOptions($defaults, $params);                
                $url = $this->createUrl(strtolower($name), $options);
                break;           
            default:
                $url = '/api/crime-last-updated';
                break;
        }
        
        return $this->makeRequestOrLoadFromCache($method, $url);
    }
}