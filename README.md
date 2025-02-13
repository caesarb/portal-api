# Portal Api
- Contains Model Classes for Crims Users (Roles, Groups & Shares)
- A Client to connect to our Laravel/Passport-based Authentication Server
- A User- and AuthServiceProvider to resolve <code>Illuminate\Support\Facades\Auth::user()</code>. Providers will self register once this package is required on <code>composer update</code> or <code>@php artisan package:discover --ansi</code>

## Usage
In composer.json add to the require field:

"require": {
  "php": "^8.2",
  "crims/portal-api": "dev-main"
  ...
}

## Required Env Variables
Docker Container of the consuming microservice needs to pass:

PORTAL_URL             - Url of the Authentication Server

EXTERNAL_ACCESS_KEYS   - Optional: listing of key-value pairs for Robot connections
