![@patrasq/dev-tools](https://i.imgur.com/GGHIKR2.png)

# laravel-netopia
[![Packagist Version (including pre-releases)](https://img.shields.io/github/v/release/codestagero/laravel-netopia?include_prereleases)](https://packagist.org/packages/codestage/laravel-netopia)

A fluent interface for interacting with Netopia's services.

# Info

## Database
It'll create a table named `netopia_payments` with the following configuration:
```
Schema::create('netopia_payments', function (Blueprint $table): void {
    $table->string('id')->primary();
    $table->string('status')->default(PaymentStatus::NotStarted->value);
    $table->decimal('amount');
    $table->string('currency', 6);
    $table->text('description')->nullable()->default(null);
    $table->nullableMorphs('billable');
    $table->json('shipping_address');
    $table->json('billing_address');
    $table->timestamps();
});
```

## Routes
3 routes will be added, as follows:
| Method| URL| Note|
|----|-------------|------------|
| GET| /netopia/pay/{payment}| Todo|
| GET| /netopia/success| Todo|
| POST| /netopia/ipn| Todo|


## Get started
Paste into your terminal
`composer require codestage/laravel-netopia`

# Configuration

## 1. Environment variables
Add the following variables to your .env file:
```
NETOPIA_ENVIRONMENT=
NETOPIA_SIGNATURE=
```

## 2. Config file
Create a netopia.php under the app's config directory. It should return an array composed of

| Parameter   | Value   |  Info |
|---------|:---------------|--------------|
| environment |  env('NETOPIA_ENVIRONMENT', 'sandbox') | The environment that Netopia is running in. Valid values are: 'sandbox', 'production' |
| signature |    env('NETOPIA_SIGNATURE')   | The merchant signature provided by Netopia. |
| currency | env('NETOPIA_CURRENCY', 'EUR') |    The currency used for Netopia. |
| certificate_path | array |    The paths to the certificate files used for Netopia requests. |
| certificate_path['public'] | base_path('certificates/' . env('NETOPIA_PUBLIC_FILE', 'netopia.cer')) |     |
| certificate_path['secret'] | base_path('certificates/' . env('NETOPIA_PUBLIC_FILE', 'netopia.cer')) |    |
