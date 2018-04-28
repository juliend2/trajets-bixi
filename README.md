# README

## Updating the code

If you want to see which stations are available, go [here](https://secure.bixi.com/map/).

Then take the name of the station, and look it up [there](https://api-core.bixi.com/gbfs/en/station_information.json) to get the `station_id`.

Then, in `index.php`:

- Update the `$stations_couples` array or arrays to do a mapping for a trip.

- Add a line like this after the `<header>` tag:

```php
<section class="station-couples">
  <?php format_trip($interesting_stations_statuses, STATION_ID_LAJEUNESSE_JARRY, STATION_ID_METRO_SHERBROOKE, 'Metro Jarry', 'Metro Sherbrooke'); ?>
</section>
<hr>
```

