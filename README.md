-- Active: 1723998450972@@127.0.0.1@3306
# AccountFlow - Reusable Dynamic Accounts Module for Laravel

AccountFlow is a reusable dynamic accounts module designed for Laravel, providing customization for views, controllers, models, migrations, and configurations.

![AccountFlow Logo](https://via.placeholder.com/468x300?text=AccountFlow+Logo)

## Features

- Configurable Views
- Modular Controllers and Models
- Dynamic Layouts
- Publishable Migrations and Configurations


## Installation


Install the package using Composer: 

```bash
composer require artflow-studio/accountflow
```

## Publish Files


publish the files separately or at once. use --force to overwrite

```bash
php artisan vendor:publish --tag=accountflow-config
php artisan vendor:publish --tag=accountflow-migrations
php artisan vendor:publish --tag=accountflow-views
php artisan vendor:publish --tag=accountflow-controllers
php artisan vendor:publish --tag=accountflow-models
php artisan vendor:publish --tag=accountflow-routes

```

## Usage

In your controller: 
```php 
use App\Http\Controllers\AccountFlow\AccountController;

public function index(){ 
    return view(config('accountflow.view_path') . 'accounts'); 
} 
```

Extend your views: 
```blade 
@extends(config('accountflow.layout')) 
```

Include partials: 
```blade 
@include(config('accountflow.view_path').'modals.add_transaction') 
```

## Configuration

Ensure you have published the configuration file: 
```bash 
php artisan vendor:publish --tag=accountflow-config 
```

The configuration file will be located at `config/accountflow.php`. Customize your paths and settings as needed.

## License

This project is licensed under the MIT License.
