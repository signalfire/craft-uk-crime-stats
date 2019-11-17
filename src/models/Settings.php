<?php

namespace signalfire\craftukcrimestats\models;

use craft\base\Model;

class Settings extends Model
{
    public $base = 'https://data.police.uk';
    public $timeout = 10;
    public $cache = 3600; 

    public function attributeLabels()
    {
        return [
            'base' => 'API Base URL',
            'timeout' => 'API Timeout (seconds)',
            'cache' => 'Results cache (seconds)',
        ];
    }

    public function rules()
    {
        return [
            [['base', 'timeout', 'cache'], 'required'],
            [['base'], 'url'],
            [['timeout', 'cache'], 'integer']
        ];
    }
}