# Export Model Constants

Export Laravel model constants to JavaScript/TypeScript for seamless backend-frontend sharing.

## Installation

1. Install the package:

```bash
composer require adrianvm/export-model-constants
```

2. Run the Artisan command:

```bash
php artisan export:constants --suffix=Model --typescript
```

## Options

- `--suffix`: Append a custom suffix (default: `Model`).
- `--typescript`: Generate TypeScript definitions.

## Output Example

```javascript
export const PostModel = {
  STATUS_DRAFT: "draft",
  STATUS_PUBLISHED: "published",
};
```

## License

This package is open-source software licensed under the [MIT license](LICENSE.md).
