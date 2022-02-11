# Trying Out Laravel 9

This project is built with Laravel 9. Trying out the features. This project also includes Thinkific integration and 
automations.

# Requirement

- PHP >= 8
- node >= 14
- composer
- A database Refer Laravel documentation for the suported databases

# Run

- Clone the Repository
- Change to the cloned directory
- copy `.env.example` to `.env`
- Make necessary changes in `.env`
- `composer install`
- `php artisan migrate`
- `npm install`
- `npm run dev`
- App is ready to access. We need a server: Apache / nginx or can be local. Configure the respective server to point to 
  `public` folder
- To run locally: `php artisan serve`

