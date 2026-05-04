<?php

namespace Novius\LaravelDto;

use BackedEnum;
use Carbon\CarbonImmutable;
use Closure;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use JsonException;
use Novius\LaravelDto\Attributes\Cast;
use Novius\LaravelDto\Attributes\DefaultValue;
use Novius\LaravelDto\Attributes\ExcludeFromDTO;
use Novius\LaravelDto\Attributes\Map;
use Novius\LaravelDto\Attributes\Rules;
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
     * @throws ReflectionException
     * @throws JsonException
     */
    public static function make(array $attributes = []): static
    {
        /** @phpstan-ignore-next-line */
        return new static($attributes);
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
     * Define property mapping for toArray().
     */
    protected function map(): array
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
        $defaults = $this->defaults();

        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            // Method has priority
            if (array_key_exists($name, $defaults)) {
                $this->setProperty($name, $defaults[$name]);

                continue;
            }

            // Attribute as fallback
            $attributes = $property->getAttributes(DefaultValue::class);
            if (! empty($attributes)) {
                $defaultValue = $attributes[0]->newInstance()->value;
                $this->setProperty($name, $defaultValue);
            }
        }

        return $this;
    }

    /**
     * Convert a string to snake case, including numbers as word boundaries.
     */
    protected function snakeCase(string $value): string
    {
        return Str::snake(preg_replace('/(\d+)/', '_$1', $value));
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
            $snakeWithNumbers = $this->snakeCase($name);
            $snakeSimple = Str::snake($name);

            if (property_exists($this, $snakeWithNumbers)) {
                $name = $snakeWithNumbers;
            } elseif (property_exists($this, $snakeSimple)) {
                $name = $snakeSimple;
            } else {
                throw new InvalidArgumentException("Property '$name' does not exist in class ".static::class);
            }
        }

        if ($this->isPropertyExcluded($name)) {
            throw new InvalidArgumentException("Property '$name' is excluded from DTO in class ".static::class);
        }

        $this->{$name} = $this->castProperty($name, $value);
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
        $type = $casts[$name] ?? null;

        if ($type === null) {
            $property = new ReflectionProperty($this, $name);
            $attributes = $property->getAttributes(Cast::class);
            if (! empty($attributes)) {
                $type = $attributes[0]->newInstance()->type;
            }
        }

        $type ??= $this->getNativeType($name);

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
            'date', 'datetime', Carbon::class, \Carbon\Carbon::class => Carbon::parse($value),
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
        $propertyToSet = null;
        if (property_exists($this, $name)) {
            $propertyToSet = $name;
        } elseif (property_exists($this, $this->snakeCase($name))) {
            $propertyToSet = $this->snakeCase($name);
        } elseif (property_exists($this, Str::snake($name))) {
            $propertyToSet = Str::snake($name);
        }

        if ($propertyToSet !== null && ! $this->isPropertyExcluded($propertyToSet)) {
            if (count($arguments) > 0) {
                $this->setProperty($propertyToSet, $arguments[0]);

                return $this;
            }

            return $this->__get($propertyToSet);
        }

        throw new InvalidArgumentException("Method $name not found on ".static::class);
    }

    /**
     * Magic getter for properties.
     *
     * @throws ReflectionException
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name) && ! $this->isPropertyExcluded($name)) {
            return $this->{$name};
        }

        $snakeWithNumbers = $this->snakeCase($name);
        if (property_exists($this, $snakeWithNumbers) && ! $this->isPropertyExcluded($snakeWithNumbers)) {
            return $this->{$snakeWithNumbers};
        }

        $snakeSimple = Str::snake($name);
        if (property_exists($this, $snakeSimple) && ! $this->isPropertyExcluded($snakeSimple)) {
            return $this->{$snakeSimple};
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
     *
     * @throws ReflectionException
     */
    public function __isset(string $name): bool
    {
        if (property_exists($this, $name) && ! $this->isPropertyExcluded($name)) {
            return isset($this->{$name});
        }

        $snakeWithNumbers = $this->snakeCase($name);
        if (property_exists($this, $snakeWithNumbers) && ! $this->isPropertyExcluded($snakeWithNumbers)) {
            return isset($this->{$snakeWithNumbers});
        }

        $snakeSimple = Str::snake($name);
        if (property_exists($this, $snakeSimple) && ! $this->isPropertyExcluded($snakeSimple)) {
            return isset($this->{$snakeSimple});
        }

        return false;
    }

    /**
     * Validate the DTO according to defined rules.
     *
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validate(): void
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        $data = [];
        $allRules = $this->rules();

        // Collect all data and initial rules (from methods)
        foreach ($properties as $property) {
            $name = $property->getName();

            if ($this->isPropertyExcluded($name)) {
                continue;
            }

            if ($property->isInitialized($this)) {
                $data[$name] = $this->transformValue($this->{$name}, $name);
            }

            // Rules from attribute as fallback if not in rules()
            if (! isset($allRules[$name])) {
                $attributes = $property->getAttributes(Rules::class);
                if (! empty($attributes)) {
                    $allRules[$name] = $attributes[0]->newInstance()->rules;
                }
            }
        }

        $validator = Validator::make(
            $data,
            $allRules,
            $this->messages(),
            $this->attributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Convert the DTO to an array with validated and casted properties.
     *
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function toArray(): array
    {
        $this->validate();

        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        $data = [];
        foreach ($properties as $property) {
            $name = $property->getName();

            // Ignore uninitialized properties
            if (! $property->isInitialized($this)) {
                continue;
            }

            // Ignore properties with #[ExcludeFromDTO] attribute
            if ($this->isPropertyExcluded($name)) {
                continue;
            }

            $key = $this->getMappedKey($name);
            $data[$key] = $this->transformValue($this->{$name}, $name);
        }

        return $data;
    }

    /**
     * Get the mapped key for a property in toArray().
     *
     * @throws ReflectionException
     */
    protected function getMappedKey(string $name): string
    {
        $map = $this->map();
        if (isset($map[$name])) {
            return $map[$name];
        }

        $property = new ReflectionProperty($this, $name);
        $attributes = $property->getAttributes(Map::class);

        if (! empty($attributes)) {
            return $attributes[0]->newInstance()->name;
        }

        return $name;
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
            $castType = $casts[$propertyName] ?? null;

            if ($castType === null) {
                $property = new ReflectionProperty($this, $propertyName);
                $attributes = $property->getAttributes(Cast::class);
                if (! empty($attributes)) {
                    $castType = $attributes[0]->newInstance()->type;
                }
            }

            $castType ??= $this->getNativeType($propertyName) ?? '';
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

    /**
     * Check if a property is excluded from DTO mechanisms.
     *
     * @throws ReflectionException
     */
    protected function isPropertyExcluded(string $name): bool
    {
        $property = new ReflectionProperty($this, $name);

        return ! empty($property->getAttributes(ExcludeFromDTO::class));
    }
}
