# Craft CMS 3 Police & Crime Data Plugin

Plugin will load data from [https://data.police.uk](data.police.uk) and return to Twig templates as array

Function in twig will look something like...

```
{% set response = getPoliceAndCrimeData('forces') %}
{% if (not response.error is defined) %}
  {% set forces = response.body %}
  {% for force in forces %}
    ...
  {% endfor %}
{% endif %}
```

A function with params would look something like...

```
{% set response = getPoliceAndCrimeData('streetcrimepoint', {category: 'all-crime', lat: 51.507375, lng: -0.127537, month: '2017-01' }) %}
{% if (not response.error is defined) %}
  {% set crimes = response.body %}
  {% for crime in crimes %}
    ...
  {% endfor %}
{% endif %}
```

| Function Name           | Params                    | URL                                                          |
|-------------------------|---------------------------|--------------------------------------------------------------|
| forces                  | none                      | https://data.police.uk/docs/method/forces/                   |
| force                   | name                      | https://data.police.uk/docs/method/force/                    |
| forceofficers           | name                      | https://data.police.uk/docs/method/senior-officers/          |
| streetcrimepoint        | category, lat, lng, month | https://data.police.uk/docs/method/crime-street/             |
| streetcrimecustom       | poly, date                | https://data.police.uk/docs/method/crime-street/             |
| crimesatlocationid      | date, location            | https://data.police.uk/docs/method/crimes-at-location/       |
| crimesatlocationpoint   | date,lat,lng              | https://data.police.uk/docs/method/crimes-at-location/       |
| crimesnolocation        | category, force, month    | https://data.police.uk/docs/method/crimes-no-location/       |
| categories              | month                     | https://data.police.uk/docs/method/crime-categories/         |
| crimeoutcomes           | id                        | https://data.police.uk/docs/method/outcomes-for-crime/       |
| neighbourhoods          | force                     | https://data.police.uk/docs/method/neighbourhoods/           |
| neighbourhood           | force, id                 | https://data.police.uk/docs/method/neighbourhood/            |
| neighbourhoodboundary   | force, id                 | https://data.police.uk/docs/method/neighbourhood-boundary/   |
| neighbourhoodteam       | force, id                 | https://data.police.uk/docs/method/neighbourhood-team/       |
| neighbourhoodevents     | force, id                 | https://data.police.uk/docs/method/neighbourhood-events/     |
| neighbourhoodpriorities | force, id                 | https://data.police.uk/docs/method/neighbourhood-priorities/ |
| neighbourhoodlocate     | lat, lng                  | https://data.police.uk/docs/method/neighbourhood-locate/     |
| stopsearcharea          | lat, lng, month           | https://data.police.uk/docs/method/stops-street/             |
| stopsearchcustom        | poly, month               | https://data.police.uk/docs/method/stops-street/             |
| stopsearchlocationid    | location, month           | https://data.police.uk/docs/method/stops-at-location/        |
| stopsearchnolocation    | force, month              | https://data.police.uk/docs/method/stops-no-location/        |
| stopsearchforce         | force, month              | https://data.police.uk/docs/method/stops-force/              |

Plugin caches data for a period of 1 hour. 

To get a reference to the services class powering the twig extension elsewhere (in your own class for example) 
you'd need to add a use reference and then call the getPoliceAndCrimeData method on ukcrimestatsservice as below...

```
use signalfire\craftukcrimestats\CraftUKCrimeStats;

CraftUKCrimeStats::getInstance()
  ->ukcrimestatsservice
  ->getPoliceAndCrimeData($name, $params, $method);

```

