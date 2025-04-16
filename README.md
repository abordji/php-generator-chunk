# PHP Generator Chunk

A utility function to split a PHP Generator into chunks of a specified length, returning a Generator of Generators, with optional key preservation.

## Description

This library provides a single function, `generator_chunk`, that allows you to efficiently process large datasets by splitting a PHP Generator into smaller, manageable chunks. This is particularly useful when dealing with data that cannot fit into memory all at once.

## Features

* **Chunking:** Splits a Generator into chunks of a specified length.
* **Key Preservation:** Optionally preserves the original keys of the Generator.
* **Generator of Generators:** Returns a Generator where each element is itself a Generator.
* **Usage Considerations:** Requires careful handling of the returned Generator of Generators. Directly using `iterator_to_array` is **not recommended**.
* **Error Handling:** Throws a `ValueError` if the chunk length is invalid (less than or equal to 0).
* **PHP 8.1+:** Requires PHP 8.1 or later due to the use of `ValueError`.

## Requirements

* **PHP 8.1 or later** (Requirement for the code itself)
* **Docker** (for the development and testing environment)
* **Docker Compose** (for orchestrating the Docker environment)
* **Make** (for automating development tasks)

## Installation

You can install this library using Composer:

```bash
composer require abordji/php-generator-chunk
```

(If you are contributing to this library, please refer to the Contributing section for development setup instructions. You do not need Composer on your host machine to contribute.)

## API

### `generator_chunk(Generator $generator, int $length, bool $preserve_keys = false): Generator`

Splits a PHP Generator into chunks of a specified length.

* `$generator` (*Generator*): The Generator to be chunked.
* `$length` (*int*): The length of each chunk. Must be greater than 0.
* `$preserve_keys` (*bool*, optional): Whether to preserve the original keys. Defaults to `false`.

**Returns:** (*Generator*) A Generator where each yielded element is itself a Generator containing the items for that chunk.

**Throws:** `ValueError` if `$length` is less than or equal to 0.

## Usage

```php
<?php

// Example usage:
$sourceGenerator = (function () {
    yield 'a' => 1;
    yield 'b' => 2;
    yield 'c' => 3;
    yield 'd' => 4;
    yield 'e' => 5;
})();

$chunkedGenerator = generator_chunk($sourceGenerator, 2, true); // Chunk size 2, preserve keys

echo "Processing chunked generator:\n";
foreach ($chunkedGenerator as $index => $chunk) {
    echo "Chunk " . ($index + 1) . ": [";
    $items = [];
    // $chunk is also a Generator
    foreach ($chunk as $key => $value) {
        $items[] = $key . ':' . $value;
    }
    echo implode(', ', $items) . "]\n";
}
// Output:
// Processing chunked generator:
// Chunk 1: [a:1, b:2]
// Chunk 2: [c:3, d:4]
// Chunk 3: [e:5]
```

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues on the repository.

### Development Workflow

This project uses **Make** to automate common development tasks within a **Dockerized** environment. Ensure you have Docker, Docker Compose, and Make installed on your system. (You do not need PHP or Composer installed locally).

After cloning the repository, start by setting up the development environment:

```bash
make setup
```

This command performs the initial project setup: installs Composer dependencies (inside Docker), builds the required Docker image, and configures Git pre-commit hooks for automated quality checks.

Here are the main commands available for development:
* `make help`: Displays all available `make` commands and their descriptions.
* `make setup`: Performs the full initial project setup (run once after cloning).
* `make install`: Installs or updates Composer dependencies inside the Docker container.
* `make update`: Updates Composer dependencies inside the Docker container.
* `make test`: Runs the PHPUnit test suite inside the Docker container. (Also run automatically before commits).
* `make cs-check`: Checks for code style violations using PHP CS Fixer inside the Docker container without applying fixes. 
* `make cs-fix`: Automatically formats the code according to PSR-12 standards using PHP CS Fixer inside the Docker container.
* `make grumphp`: Manually runs all checks configured in GrumPHP (tests, code style, etc.) inside the Docker container. This simulates the pre-commit hook.
* `make build`: Builds or updates the Docker development image using the cache.
* `make rebuild`: Forces a rebuild of the Docker development image without using the cache (use this after changing the `Dockerfile`).

**Pre-commit Hooks:** Note that after running `make setup`, Git hooks are installed. These hooks will automatically run tasks like `make test` and `make fix` before each commit to ensure code quality and consistency. You can also run these checks manually using the commands listed above.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE.md) file for details.
