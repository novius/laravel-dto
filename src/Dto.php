<?php

namespace Novius\LaravelDto;

use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Closure;
use DateTimeInterface;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use JsonException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;

abstract class Dto
{
    /**
     * @throws ValidationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __construct(array $attributes = [])
    {
        $this->applyDefaults();
        $this->fill($attributes);
    }

    /**
     * Define validation rules.
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Define custom validation messages.
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Define custom validation attributes.
     */
    protected function attributes(): array
    {
        return [];
    }

    /**
     * Define default values.
     */
    protected function defaults(): array
    {
        return [];
    }

    /**
     * Define cast types.
     */
    protected function casts(): array
    {
        return [];
    }

    /**
     * Fill the object with attributes.
     *
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setProperty($key, $value);
        }

        return $this;
    }

    /**
     * Apply defined default values.
     *
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function applyDefaults(): static
    {
        foreach ($this->defaults() as $key => $value) {
            $this->setProperty($key, $value);
        }

        return $this;
    }

    /**
     * Set a property with validation and cast.
     *
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function setProperty(string $name, mixed $value): void
    {
        if (! property_exists($this, $name)) {
            $snakeName = Str::snake($name);
            if (property_exists($this, $snakeName)) {
                $name = $snakeName;
            } else {
                throw new InvalidArgumentException("Property '$name' does not exist in class ".static::class);
            }
        }

        $this->validateProperty($name, $value);
        $this->{$name} = $this->castProperty($name, $value);
    }

    /**
     * Validate a property according to defined rules.
     */
    protected function validateProperty(string $name, mixed $value): void
    {
        $rules = $this->rules();
        if (isset($rules[$name])) {
            $validator = Validator::make(
                [$name => $value],
                [$name => $rules[$name]],
                $this->messages(),
                $this->attributes()
            );

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }

    /**
     * Cast a property according to defined types or native PHP types.
     *
     * @throws JsonException
     * @throws ReflectionException
     */
    protected function castProperty(string $name, mixed $value): mixed
    {
        $casts = $this->casts();
        $type = $casts[$name] ?? $this->getNativeType($name);

        if ($type === null || $value === null) {
            return $value;
        }

        if ($type instanceof Closure) {
            return $type($value);
        }

        $parameter = null;
        if (is_string($type) && str_contains($type, ':')) {
            [$type, $parameter] = explode(':', $type, 2);
        }

        return match ($type) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'string' => (string) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'array' => (array) $value,
            'object' => (object) $value,
            'date', 'datetime', Carbon::class => Carbon::parse($value),
            'immutable_date', 'immutable_datetime', CarbonImmutable::class => CarbonImmutable::parse($value),
            'decimal' => number_format((float) $value, (int) ($parameter ?? 2), '.', ''),
            'json' => is_array($value) ? $value : json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR),
            'encrypted' => Crypt::decryptString($value),
            default => $this->castToSpecialType($type, $value),
        };
    }

    /**
     * Get the native PHP type of a property.
     *
     * @throws ReflectionException
     */
    protected function getNativeType(string $name): ?string
    {
        $property = new ReflectionProperty($this, $name);
        $type = $property->getType();

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        return null;
    }

    /**
     * Cast a value to a special type (Enum, DTO, etc.).
     */
    protected function castToSpecialType(string $type, mixed $value): mixed
    {
        if (is_subclass_of($type, BackedEnum::class) && enum_exists($type)) {
            if ($value instanceof $type) {
                return $value;
            }

            return $type::from($value);
        }

        if ($type === Fluent::class && is_array($value)) {
            return new Fluent($value);
        }

        if (is_subclass_of($type, self::class) && is_array($value)) {
            return new $type($value);
        }

        return $value;
    }

    /**
     * Magic getter for properties or via getProperty().
     *
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __call(string $name, array $arguments)
    {
        if (str_starts_with($name, 'get')) {
            $property = lcfirst(substr($name, 3));

            return $this->__get($property);
        }

        if (str_starts_with($name, 'set')) {
            $property = lcfirst(substr($name, 3));
            $this->setProperty($property, $arguments[0] ?? null);

            return $this;
        }

        // Fluent interface: $dto->name() or $dto->name('value')
        if (property_exists($this, $name) || property_exists($this, Str::snake($name))) {
            if (count($arguments) > 0) {
                $this->setProperty($name, $arguments[0]);

                return $this;
            }

            return $this->__get($name);
        }

        throw new InvalidArgumentException("Method $name not found on ".static::class);
    }

    /**
     * Magic getter for properties.
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        $snakeName = Str::snake($name);
        if (property_exists($this, $snakeName)) {
            return $this->{$snakeName};
        }

        throw new InvalidArgumentException("Property '$name' does not exist in class ".static::class);
    }

    /**
     * Magic setter for properties.
     *
     * @throws JsonException
     * @throws ReflectionException
     */
    public function __set(string $name, mixed $value): void
    {
        $this->setProperty($name, $value);
    }

    /**
     * Magic isset for properties.
     */
    public function __isset(string $name): bool
    {
        if (property_exists($this, $name)) {
            return isset($this->{$name});
        }

        $snakeName = Str::snake($name);
        if (property_exists($this, $snakeName)) {
            return isset($this->{$snakeName});
        }

        return false;
    }

    /**
     * Convert the DTO to an array with validated and casted properties.
     *
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();

            // Ignore uninitialized properties
            if (! $property->isInitialized($this)) {
                continue;
            }

            $data[$name] = $this->transformValue($this->{$name}, $name);
        }

        return $data;
    }

    /**
     * Transform a value for toArray() conversion.
     *
     * @throws ReflectionException
     */
    protected function transformValue(mixed $value, string $propertyName): mixed
    {
        if ($value instanceof self) {
            return $value->toArray();
        }

        if ($value instanceof Fluent) {
            return $value->toArray();
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            $casts = $this->casts();
            $castType = $casts[$propertyName] ?? $this->getNativeType($propertyName) ?? '';
            $format = str_contains($castType, 'date') && ! str_contains($castType, 'datetime') ? 'Y-m-d' : 'Y-m-d H:i:s';
            if (str_contains($castType, ':')) {
                [$type, $parameter] = explode(':', $castType, 2);
                if (in_array($type, ['date', 'datetime', 'immutable_date', 'immutable_datetime'])) {
                    $format = $parameter;
                }
            }

            return $value->format($format);
        }

        if (is_array($value)) {
            return array_map(fn ($item) => $this->transformValue($item, $propertyName), $value);
        }

        return $value;
    }
}
