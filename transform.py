#!/usr/bin/env python3
import sys
import re

def transform_line(line: str) -> str:
    # Replace backticks with double quotes
    line = line.replace('`', '"')
    # Remove IGNORE keyword from INSERT IGNORE INTO
    line = re.sub(r'INSERT\s+IGNORE\s+INTO', 'INSERT INTO', line, flags=re.IGNORECASE)
    # Also replace INSERT IGNORE INTO with INSERT INTO (already done)
    return line

def main():
    # Read from stdin, write to stdout
    for line in sys.stdin:
        sys.stdout.write(transform_line(line))

if __name__ == '__main__':
    main()