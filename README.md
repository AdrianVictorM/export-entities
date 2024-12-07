# Export Entities (Laravel Package)

Export PHP constants and/or PHP enums to JavaScript/TypeScript for seamless backend-frontend sharing.

## Requirements

- PHP 8.0 or above (8.1 or above for Enums command to work)
- Laravel 10 or above

## Installation

```bash
composer require --dev adrianvm/export-entities
```

## Usage

This package adds two new commands:

### 1. Export constants

```bash
php artisan export:constants
```

### 1.1 Options

- `--suffix`: Append a custom suffix (optional, no default).
- `--path`: Models folder path (default: `Models`)
- `output`: The output file path for JavaScript constants (default: `resources/js/constants.js`)
- `--typescript`: Generate TypeScript definitions.

### 1.2. Input Example

```php
...
class Post extends Model
{
  ...

  const STATUS_DRAFT = 'draft';
  const STATUS_PUBLISHED = 'published';
  ...
  const TYPE_PRIVATE = 0;
  const TYPE_PUBLIC = 1;

  ...
}
```

### 1.3. Output Example

```javascript
export const Post = {
  STATUS_DRAFT: "draft",
  STATUS_PUBLISHED: "published",
  TYPE_PRIVATE: 0,
  TYPE_PUBLIC: 1
};
```

##

### 2. Export enums

```bash
php artisan export:enums
```

### 2.1 Options

- `--suffix`: Append a custom suffix (optional, no default).
- `--path`: Enums folder path (default: `Enums`)
- `output`: The output file path for JavaScript constants (default: `resources/js/enums.js`)
- `--typescript`: Generate TypeScript definitions.

### 2.2.1 Input Example (no type)

```php
...
enum BurgerCookingStages
{
    case Raw;
    case Medium;
    case WellDone;
    case Congratulations;
}
```

### 2.2.1 Output Example (no type)

```javascript
export const Post = {
  Raw: 'raw',
  Medium: 'medium',
  WellDone: 'well_done',
  Congratulations: 'congratulations'
};
```

##

### 2.2.2 Input Example (string type)

```php
...
enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case DELETED = 'is_deleted';
}
```

### 2.2.2 Output Example (string type)

```javascript
export const Status = {
  DRAFT: 'draft',
  PUBLISHED: 'published',
  ARCHIVED: 'archived',
  DELETED: 'is_deleted'
};
```

##

### 2.2.3 Input Example (int type)

```php
...
enum Status: int
{
    case DRAFT = 1;
    case PUBLISHED = 2;
    case ARCHIVED = 3;
}
```

### 2.2.3 Output Example (int type)

```javascript
export const Status = {
  DRAFT: 1,
  PUBLISHED: 2,
  ARCHIVED: 3
};
```

## License

This package is open-source software licensed under the [MIT license](LICENSE.md).
