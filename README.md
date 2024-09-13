This app is based on Laravels Filament 3 with Roles and Permission

Follow the below steps to get it running

git clone
composer install
cp .env.example .env  
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan shield:generate --all

In the table : model_has_roles set the values as below to get the Super Admin role for the first ID, Later you can create from the application itself
role_id = 1
model_type = App\Models\User
model_id=1

composer update
