<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## First Time SetUp Extra Reminder
Since new version of laravel 12 passport change some of it way compare to previous version, so need to run 2 command to generate client secret
- `php artisan passport:client --personal`
- `php artisan passport:client --password`

then update env: `PASSPORT_PASSWORD_ID`, `PASSPORT_PASSWORD_SECRET`
Library IssueToken has been update to work with new passport version but currently only support from env, not work with param pass down along with method

Next, run seeder `php artisan db:seed --class="Database\\Seeders\\Production\\BaseSeeder"`
