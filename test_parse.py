import ast
import sys

def parse_line(line):
    # Replace NULL with None
    line = line.replace('NULL', 'None')
    # Replace TRUE with True, FALSE with False
    line = line.replace('TRUE', 'True').replace('FALSE', 'False')
    # Evaluate as Python literal
    try:
        return ast.literal_eval(line)
    except SyntaxError as e:
        print('Syntax error:', e)
        print('Line:', line[:200])
        return None

with open('/home/baba/apps/api/database/imports/ratsit_data_fixed.sql', 'r', encoding='utf-8') as f:
    lines = f.readlines()
    # line 2 (index 1)
    row1 = lines[1].rstrip().rstrip(',')
    print('Row1:', row1[:200])
    parsed = parse_line(row1)
    if parsed:
        print('Parsed length:', len(parsed))
        for i, val in enumerate(parsed[:5]):
            print(f'{i}: {repr(val)}')
    # line 127480 (approx)
    # find a line with M-CM-6 pattern
    for i, line in enumerate(lines):
        if 'M-CM-6' in line:
            print(f'Found at line {i}')
            row2 = line.rstrip().rstrip(',')
            print('Row2 sample:', row2[:200])
            parsed2 = parse_line(row2)
            if parsed2:
                print('Parsed2 length:', len(parsed2))
            else:
                print('Failed to parse')
            break
    # test a line with backslash quote
    for i, line in enumerate(lines):
        if "\\'" in line:
            print(f'Found backslash quote at line {i}')
            row3 = line.rstrip().rstrip(',')
            print('Row3 sample:', row3[:200])
            parsed3 = parse_line(row3)
            if parsed3:
                print('Parsed3 length:', len(parsed3))
            else:
                print('Failed')
            break