<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## Installation
- Clone this repository
- Open terminal or command prompt
- Run ```cd /this-cloned-repository/path``` 
- Run ```composer install```
- Run ```composer dumpautoload```
- Run ```cp .env.example .env``` then change configuration on your copied .env file (check below for example)
- Run ```php artisan key:generate```
- Run ```php artisan migrate```

### for local/development testing only
- Run ```php artisan serve```
- Open new terminal tab and run ```php artisan schedule:work```

### for production
- Create new cron
- ```* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1```
