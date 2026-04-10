import sys
def split_row(row_str):
    row_str = row_str.strip()
    if row_str.startswith('('):
        row_str = row_str[1:]
    if row_str.endswith(')'):
        row_str = row_str[:-1]
    values = []
    current = []
    in_string = False
    quote_char = None
    escape = False
    i = 0
    while i < len(row_str):
        ch = row_str[i]
        if escape:
            current.append(ch)
            escape = False
            i += 1
            continue
        if ch == '\\\\':
            escape = True
            current.append(ch)
            i += 1
            continue
        if ch in ("'", '"'):
            if not in_string:
                in_string = True
                quote_char = ch
            elif ch == quote_char:
                in_string = False
            current.append(ch)
        elif ch == ',' and not in_string:
            values.append(''.join(current).strip())
            current = []
        else:
            current.append(ch)
        i += 1
    if current:
        values.append(''.join(current).strip())
    return values

with open('test_input.sql', 'r') as f:
    for line in f:
        if line.strip().startswith('('):
            line = line.rstrip()
            print('Original line:', line[:80])
            vals = split_row(line.rstrip(',;'))
            print('Boolean values:', vals[33], vals[34], vals[35], vals[36], vals[37])
            # transform
            for idx in [33,34,35,36,37]:
                if vals[idx] == '1':
                    vals[idx] = 'TRUE'
                elif vals[idx] == '0':
                    vals[idx] = 'FALSE'
            print('After transform:', vals[33], vals[34], vals[35], vals[36], vals[37])
            new_row = '(' + ', '.join(vals) + ')'
            print('New row:', new_row[:80])
