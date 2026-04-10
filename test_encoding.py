import sys
with open('/home/baba/apps/api/database/imports/ratsit_data_fixed.sql', 'rb') as f:
    lines = f.readlines()
    # line 1 is INSERT, line 2 is first row
    line = lines[1]
    print('Raw bytes (first 100):', line[:100])
    print('As UTF-8 decode:', line.decode('utf-8')[:100])
    # Try decode as latin1
    try:
        decoded = line.decode('latin1')
        print('Latin1 decode:', decoded[:100])
        # Encode back to utf-8
        reencoded = decoded.encode('utf-8')
        print('Re-encoded:', reencoded[:100])
        # See if any improvement
    except Exception as e:
        print('Latin1 error:', e)
    # Check for M-CM-6 pattern
    if b'M-CM-6' in line:
        print('Found M-CM-6 pattern')
    # Let's examine a specific character: the 'å' character
    # Find position of 'M-CM-6'
    import re
    matches = re.findall(b'M-CM-6', line)
    print('Matches:', len(matches))
    # Try to replace with proper utf-8
    # The byte sequence for 'å' in UTF-8 is C3 A5
    # What bytes represent M-CM-6? Let's examine context
    idx = line.find(b'M-CM-6')
    if idx != -1:
        print('Context:', line[idx-10:idx+20])
        print('Hex:', line[idx-10:idx+20].hex())
        # The pattern likely corresponds to C3 A5
        # Let's replace b'M-CM-6' with b'\xc3\xa5'
        # But we need to know mapping
        # Let's search for all unique multi-byte sequences
        pass