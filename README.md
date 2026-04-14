# Laravel DTO

A simple and extensible DTO (Data Transfer Objects) package for Laravel.

This package allows you to define structured data objects with built-in validation, default values, and automatic casting, while supporting both camelCase and snake_case naming conventions.

## Installation

You can install the package via composer:

```bash
composer require novius/laravel-dto
```

## Artisan Command

You can easily generate a new DTO class using the following Artisan command:

```bash
php artisan make:dto UserDto
```

By default, the class will be created in the `app/Dtos` directory.

## Basic Usage

To create a DTO, simply extend the `Novius\LaravelDto\Dto` class and define your properties.

```php
use Novius\LaravelDto\Dto;

/**
 * @property string $name
 * @property int $age
 * @property ?string $email
 *
 * @method string getName()
 * @method self setName(string $name)
 * @method int getAge()
 * @method self setAge(int $age)
 * @method ?string getEmail()
 * @method self setEmail(?string $email)
 */
class UserDto extends Dto
{
    protected string $name;
    protected int $age;
    protected ?string $email;
}

// Instantiation from an array
$dto = new UserDto([
    'name' => 'John Doe',
    'age' => 30,
    'email' => 'john@example.com',
]);

echo $dto->name; // John Doe
```

If you try to pass a property that is not defined in the class, an `InvalidArgumentException` will be thrown.

## Features

### Validation

Override the `rules()` method to define Laravel validation rules for your properties.

```php
protected function rules(): array
{
    return [
        'name' => 'required|string|min:3',
        'age' => 'required|integer|min:18',
        'email' => 'nullable|email',
    ];
}
```

You can also customize validation messages and attributes by overriding the `messages()` and `attributes()` methods:

```php
protected function messages(): array
{
    return [
        'name.min' => 'The :attribute is too short (min :min characters)!',
    ];
}

protected function attributes(): array
{
    return [
        'email' => 'user email address',
    ];
}
```

A `ValidationException` is thrown if the data does not comply with these rules during instantiation or modification via a setter.

### Default Values

Override the `defaults()` method to define default values.

```php
protected function defaults(): array
{
    return [
        'age' => 18,
        'status' => 'active',
    ];
}
```

### Casting

The package supports the same cast types as Laravel's Eloquent models. It also **automatically infers** the cast type from the native PHP type of your properties.

```php
class UserDto extends Dto
{
    protected int $age;           // Automatically cast to int
    protected bool $is_active;    // Automatically cast to bool
    protected Carbon $created_at; // Automatically cast to Carbon
    protected UserStatus $status; // Automatically cast to Backed Enum
    protected AddressDto $address;// Automatically cast to nested DTO
}
```

If you need more control, you can still override the `casts()` method. Explicit casts always take precedence over native type inference.

Supported types: `int`, `float`, `string`, `bool`, `array`, `object`, `date`, `datetime`, `immutable_date`, `immutable_datetime`, `decimal:x`, `json`, `encrypted`.

You can also cast properties to [Backed Enums](https://www.php.net/manual/en/language.enumerations.backed.php):

```php
protected function casts(): array
{
    return [
        'status' => UserStatus::class,
    ];
}
```

You can also specify a custom format for date and datetime casts, which will be used when calling `toArray()`:

```php
protected function casts(): array
{
    return [
        'created_at' => 'datetime:Y-m-d H:i',
        'birth_date' => 'date:d/m/Y',
    ];
}
```

### Magic Getters, Setters and Fluent Interface

You can access your properties via magic methods. Setters automatically apply validation and casting.

```php
$dto->setName('Jane Doe'); // Setter
echo $dto->getName();      // Getter
```

The package also supports a fluent interface (methods with the same name as the property):

```php
$dto->name('Jane Doe');    // Setter (returns $this)
echo $dto->name();         // Getter
```

### CamelCase / Snake_case Support

If your properties are defined in `snake_case` in your class, you can access them in `camelCase` seamlessly.

```php
/**
 * @property string $first_name
 *
 * @method string getFirstName()
 * @method self setFirstName(string $firstName)
 */
class UserDto extends Dto {
    protected string $first_name;
}

$dto = new UserDto(['first_name' => 'John']);

echo $dto->firstName;      // John
echo $dto->getFirstName(); // John
$dto->setFirstName('Jane');
```

### Array Conversion

The `toArray()` method returns all properties (public, protected, or private). It recursively handles nested DTOs.
Dates are formatted according to their cast:
- `Y-m-d` for `date` and `immutable_date`
- `Y-m-d H:i:s` for `datetime` and `immutable_datetime` (MySQL format)

```php
$array = $dto->toArray();
```

## Testing

The package uses [Pest](https://pestphp.com/) for testing.

```bash
composer test
```

## Static Analysis

```bash
composer analyze
```

## License

This project is licensed under the AGPL-3.0-or-later License.
