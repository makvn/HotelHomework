Install the project
=========

1. Run command `composer install` to install library packages
2. Edit the MySQL config in the file `/app/config/parameters.yml`.
3. Run the command `php bin/console doctrine:database:create` to create the database.
4. Run the command `php bin/console doctrine:schema:update --force` to update the database schema.

Run project
=========
Run command `php bin/console server:run` to start the built-in web server.

APIs
=========
1. `POST   /api/offers ` 
	* Post with `date` parammeter format as 'Y-m-d'. Ex: 2016-02-17
	* Search and save for all room names available at the given date 
2. `DELETE /api/offers/{id}
	* `{id}`: offer id
	* Delete an offer and all related rooms

> Scenario: I can find room-names at `"The Reverie Residence"`
>
> Given I search for room-names available at `2017-02-27`
>
> Then I should see `1 Bedroom Classic`

[1]: http://www.hotels.com
[2]: http://www.hotels.com/ho555246/the-reverie-residence-ho-chi-minh-city-vietnam/?FPQ=6&JHR=5&MGT=1&SYE=3&WOD=6&WOE=7&YGF=14&ZSX=0&pa=1&q-localised-check-in=02%2F27%2F17&q-localised-check-out=02%2F28%2F17&q-room-0-adults=2&q-room-0-children=0&tab=description
[3]: http://jsonapi.org/format/
