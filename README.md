Github Repositories Comparison
===================================

Small sample application prepared by Bartek Olewiński as a part of job application in Shibsted


Solutions and technologies used:
------------------
* GUI and API are build on microframework [slim](https://www.slimframework.com)
* **Monolog** is used for logging
* **Twig** and **Bootstrap** for templates
* **PHPUnit** for unit tests
* **frisby.js** for end-to-end tests
* **Swagger** for Api documentation

Installation:
------------------

* Download Composer Manager to root directory as it's described on [getcomposer.org](https://getcomposer.org) 
* Install dependencies and autoloader by typing `php composer install`
* If you want to run automatic tests download and install **Phpunit** as it's described on [phpunit.de](https://phpunit.de/) 


Unit-Tests:
------------------------
* Use **phpunit** in root dir to run unit tests

End-to-End Tests:
------------------------
In **tests/end-to-end/README.md** you can find detailed info about preparing and running end-to-end tests


Run application:
------------------------
* In Dir **public** type `php -S localhost:8080` to make a 'quick start'
* In case you decide to change host, please update respectively files **config/config.php** and **tests/end-to-end/config/local.json**

* GUI is under url **http://localhost:8080** then
* API is under url **http://localhost:8080/api**


API (auto-generated) documentation:
------------------------

* API is auto-documented using [Swagger](http://swagger.io/) adnotations
* Swagger json is under **http://localhost:8080/swagger/swagger.php** url
* To see Swagger documentation use [Swagger UI](http://swagger.io/swagger-ui/) tool. You can fast way and open 
**/vendor/swagger-api/swagger-ui/dist/index.html** to get one without installation.


Comparison criteria
------------------------

* Application compares every part of statistics (fors, watchers, etc.)
* When one of repositories has bigger values for given part it gets points
* There is different scoring for every part as some parts are more valuable than others
* i.e. For winning in "Stars part" repository gets 5 points but for winning in "Forks part" only 2
* Detailed scoring for parts is in Comparer class
* When there is draw in given part no one gets points
* At the end earned points are converted into percent value of all distributed points 

Requests caching
------------------------

For sake of application performance there is no actual requests cached 
but statistics downloaded for each repository. You can call this solution "half-request cache".
