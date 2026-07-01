# ExecWithFallback Library

This is the **exec-with-fallback** library integrated into the Advanced File Manager plugin.

## About

Some shared hosts may have disabled `exec()`, but left `proc_open()`, `passthru()`, `popen()` or `shell_exec()` open. This library provides fallback functionality to emulate `exec()` using these alternative functions.

## Usage

The library is automatically loaded by the FMA Debug Validator class and provides enhanced PHP syntax validation capabilities.

## Files

- `src/ExecWithFallback.php` - Main class with fallback functionality
- `src/Passthru.php` - Passthru implementation
- `src/POpen.php` - POpen implementation  
- `src/ProcOpen.php` - ProcOpen implementation
- `src/ShellExec.php` - ShellExec implementation
- `src/Availability.php` - Function availability checker
- `autoload.php` - Simple autoloader for the library

## Integration

This library is used by:
- `class_fma_debug_validator.php` - For robust PHP syntax validation
- Enhanced debug features in the file manager

## License

GPL-3.0 (same as the main plugin)

## Original Source

https://github.com/rosell-dk/exec-with-fallback