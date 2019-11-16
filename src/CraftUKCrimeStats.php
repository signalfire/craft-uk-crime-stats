<?php
/**
 * UK Crime stats plugin for Craft CMS 3.x
 * 
 * @link      https://github.com/signalfire
 * @copyright Copyright (c) 2019 Robert Coster
 */

namespace signalfire\craftukcrimestats;

use craft\base\Plugin;
use signalfire\craftukcrimestats\twig\CraftUKCrimeStatsTwigExtension;
use signalfire\craftukcrimestats\services\CraftUKCrimeStatsService;

use Craft;

/**
* @author  Robert Coster
* @package craftukcrimestats
* @since   1.0.0
*/
class CraftUKCrimeStats extends Plugin
{
    /**
     * Initialization
     * 
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'ukcrimestatsservice' => CraftUKCrimeStatsService::class,
        ]);

        if (Craft::$app->request->getIsSiteRequest()) {
            $extension = new CraftUKCrimeStatsTwigExtension();
            Craft::$app->view->registerTwigExtension($extension);
        }

    }
}