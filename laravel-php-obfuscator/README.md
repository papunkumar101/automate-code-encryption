# Universal PHP / JS / CSS Cleaner Tool

This tool is designed to clean and optimize PHP, JavaScript, and CSS files within a specified directory. It performs various operations such as removing comments, minifying code, and replacing external CDN links with locally downloaded copies. The tool is particularly useful for preparing code for production by reducing file sizes and improving performance.

## Features

1. **Whitespace Normalization**:
    - Replaces multiple spaces with a single space.
    - Converts tabs and newlines into single spaces.

2. **Comment Removal**:
    - Removes single-line (`//`) and multi-line (`/* ... */`) comments from PHP, JavaScript, and CSS files.
    - Ensures that `//` comments in URLs (e.g., `https://`) are not removed.

3. **HTML Comment Removal**:
    - Removes HTML comments (`<!-- ... -->`) from PHP files.

4. **CDN Link Replacement**:
    - Detects external CDN links in `<script>`, `<link>`, and `<img>` tags.
    - Downloads the CDN resources (JavaScript, CSS, images) and saves them locally.
    - Replaces the external links with local paths in the code.

5. **Recursive Directory Traversal**:
    - Iterates through all files and folders in the specified directory.
    - Skips specified files and folders (e.g., `vendor`, `.git`, `cdn`, etc.).

6. **File Type Handling**:
    - **PHP**: Removes comments, replaces CDN links, and removes unnecessary spaces between HTML tags.
    - **JavaScript**: Minifies code by removing comments and excessive whitespace.
    - **CSS**: Minifies code by removing comments and excessive whitespace.

7. **Keyword Formatting**:
    - Ensures exactly one space before JavaScript keywords (`function`, `var`, `let`, `const`, `if`, `for`, `while`, `async`) when they directly follow a newline, tab, or `}`.

8. **Customizable Configuration**:
    - Define folders to skip and CDN save paths at the beginning of the program.

## Configuration

### Skip List
The `$skipList` array defines the files and folders to skip during processing:

```php
$skipList = [
    'vendor',
    '.git',
    'cdn',
    // Add other folders or files to skip here
];
```

### CDN Save Path
Define the folder where downloaded CDN resources will be saved:

```php
$cdnSavePath = 'public/assets/cdn';
```

### Additional Notes
- The tool ensures that single-line comment removal only matches `//` not preceded by a colon (`:`), to avoid removing protocol-based URLs like `https://`.
- It checks for the existence of folders at the start of the program and downloads CDN resources starting with `https`, `http`, or `//`, replacing them with local links in `<script>`, `<style>`, and `<img>` tags.
- The tool recursively iterates through all files and folders, skipping those specified in the `$skipList`.
- It ensures exactly one space before JavaScript keywords (`function`, `var`, `let`, `const`, `if`, `for`, `while`, `async`) only when:
  - The keyword directly follows a newline or tab.
  - There is no space and it is adjacent to the `}` character.
- The configuration for skipped files and folders, as well as the CDN save path, is defined at the beginning of the program for easy customization.

## Requirements

- PHP version **8.3** or higher is required to run this tool.

## Running the Tool

To start the tool and serve the `index.php` file locally, use the following command:

```bash
php -S localhost:8000 index.php
```