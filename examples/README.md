# NetPolicy PHP - Examples

This directory contains practical examples demonstrating various features of the NetPolicy PHP library.

## 📁 Examples Overview

### 1. Basic Usage (`01-basic-usage.php`)
**Difficulty:** Beginner  
**Topics:** Loading, validation, evaluation

Learn the fundamental workflow:
- Loading policies from JSON files
- Validating policies for correctness
- Evaluating traffic against policies
- Understanding decision results

```bash
php examples/01-basic-usage.php
```

**What you'll learn:**
- Basic NetPolicy API usage
- Policy structure
- Simple traffic evaluation
- Accept/reject decisions

---

### 2. Conflict Detection (`02-conflict-detection.php`)
**Difficulty:** Intermediate  
**Topics:** Validation, conflict detection

Understand how NetPolicy detects conflicts:
- Overlapping prefixes with different actions
- Same action overlaps (no conflict)
- Non-overlapping rules
- Protocol and direction matching

```bash
php examples/02-conflict-detection.php
```

**What you'll learn:**
- What causes policy conflicts
- How validation detects conflicts
- Best practices for avoiding conflicts
- Conflict resolution strategies

---

### 3. Cisco IOS-XR Rendering (`03-cisco-rendering.php`)
**Difficulty:** Intermediate  
**Topics:** Rendering, Cisco IOS-XR

Generate Cisco IOS-XR route-policy configuration:
- Route-policy syntax
- Prefix-set generation
- Attribute manipulation (local-pref, community, MED)
- Real-world BGP policy examples

```bash
php examples/03-cisco-rendering.php
```

**Output:** IOS-XR configuration ready to deploy

**What you'll learn:**
- How to render policies to vendor configs
- IOS-XR route-policy structure
- BGP attribute manipulation
- Production-ready configuration generation

---

### 4. IPv6 Support (`04-ipv6-support.php`)
**Difficulty:** Intermediate  
**Topics:** IPv6, prefix operations

Work with IPv6 prefixes:
- IPv6 CIDR notation
- IPv6 prefix contains/overlaps
- IPv6 policy evaluation
- Common IPv6 prefix types (ULA, link-local, etc.)

```bash
php examples/04-ipv6-support.php
```

**What you'll learn:**
- IPv6 prefix handling
- IPv6 bogon filtering
- Dual-stack considerations
- IPv6 best practices

---

### 5. Multi-Format Loading (`05-multi-format.php`)
**Difficulty:** Beginner  
**Topics:** Loading, formats (JSON/YAML/XML)

Load policies from different formats:
- JSON (recommended)
- YAML (requires yaml extension)
- XML
- PHP arrays (programmatic)

```bash
php examples/05-multi-format.php
```

**What you'll learn:**
- Format auto-detection
- When to use each format
- Converting between formats
- Best practices for policy storage

---

## 🚀 Running Examples

### Prerequisites

Ensure dependencies are installed:

```bash
composer install
```

### Run an Example

```bash
# From project root
php examples/01-basic-usage.php

# Or with full path
php /path/to/netpolicy-php/examples/01-basic-usage.php
```

### Run All Examples

```bash
# Linux/macOS
for f in examples/*.php; do php "$f"; echo; done

# Windows PowerShell
Get-ChildItem examples\*.php | ForEach-Object { php $_.FullName; Write-Host "" }
```

---

## 📝 Example Structure

Each example follows this pattern:

1. **Header Comment** - Describes what the example demonstrates
2. **Setup** - Load required classes and data
3. **Demonstration** - Show the feature in action
4. **Output** - Display results with explanation
5. **Summary** - Key takeaways and best practices

---

## 🎯 Learning Path

**Recommended order for beginners:**

1. Start with `01-basic-usage.php` to understand fundamentals
2. Learn conflict detection with `02-conflict-detection.php`
3. Try multi-format loading with `05-multi-format.php`
4. Explore IPv6 with `04-ipv6-support.php`
5. Generate configs with `03-cisco-rendering.php`

**For experienced users:**

Jump directly to examples relevant to your use case.

---

## 📂 Generated Files

Some examples create files in subdirectories:

```
examples/
├── policies/          # Example policy files (JSON, YAML, XML)
│   ├── basic-policy.json
│   ├── policy.json
│   ├── policy.yaml
│   └── policy.xml
└── output/            # Generated configurations
    └── iosxr-config.txt
```

These are automatically created and can be safely deleted.

---

## 🔧 Troubleshooting

### "Class not found" errors

Run from project root and ensure autoload is working:

```bash
composer dump-autoload
```

### YAML examples fail

Install the YAML extension:

```bash
# Ubuntu/Debian
sudo apt-get install php-yaml

# macOS (with Homebrew)
pecl install yaml

# Windows
# Enable extension in php.ini: extension=yaml
```

### Permission errors

Ensure write permissions for `examples/policies/` and `examples/output/`:

```bash
chmod -R 755 examples/
```

---

## 💡 Tips

- **Read the comments** - Each example has detailed inline documentation
- **Modify the examples** - Experiment with different values
- **Check output** - Examples are verbose for learning purposes
- **Use as templates** - Copy examples as starting points for your code

---

## 📚 Additional Resources

- [Main README](../README.md) - Project overview
- [Testing Guide](../TESTING.md) - How to run tests
- [API Documentation](../docs/API.md) - Full API reference (if available)

---

## 🤝 Contributing Examples

Have a useful example to share? Contributions welcome!

**Example template:**

```php
<?php
/**
 * Your Example Title
 * 
 * Brief description of what this example demonstrates.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Your example code here

echo "=== Example completed! ===\n";
```

---

## 📄 License

These examples are part of the NetPolicy PHP project and are released under the MIT License.

---

**Happy learning! 🚀**

If you have questions, please open an issue on GitHub.

