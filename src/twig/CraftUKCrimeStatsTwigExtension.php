<?php
/**
 * UK Crime stats plugin for Craft CMS 3.x
 *
 * @link      https://github.com/signalfire
 * @copyright Copyright (c) 2019 Robert Coster
 */

namespace signalfire\craftukcrimestats\twig;

use signalfire\craftukcrimestats\CraftUKCrimeStats;
use Craft;

/**
* @author  Robert Coster
* @package craftukcrimestats
* @since   1.0.0
*/
class CraftUKCrimeStatsTwigExtension extends \Twig_Extension
{
    /**
     * Get name of plugin
     * 
     * @return string
     */
    public function getName() : string
    {
        return 'UK Crime Stats';
    }

    /**
     * Register twig functions
     * 
     * @return array
     */
    public function getFunctions() : array
    {
        return [
            new \Twig_SimpleFunction('getPoliceAndCrimeData', [$this, 'getPoliceAndCrimeData']),
        ];
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
        return CraftUKCrimeStats::getInstance()
            ->ukcrimestatsservice
            ->getPoliceAndCrimeData($name, $params, $method);
    }
}