import csv
import sys

columns = ['gatuadress','postnummer','postort','forsamling','kommun','lan','adressandring','telfonnummer','stjarntacken','fodelsedag','personnummer','alder','kon','civilstand','fornamn','efternamn','personnamn','telefon','epost_adress','agandeform','bostadstyp','boarea','byggar','fastighet','personer','foretag','grannar','fordon','hundar','bolagsengagemang','longitude','latitud','google_maps','google_streetview','ratsit_se','is_active','is_hus','is_telefon','created_at','updated_at','kommun_ratsit','is_queued']

csv_path = '/home/baba/apps/api/database/imports/ratsit_data.csv'
header_path = '/home/baba/apps/api/database/imports/ratsit_data_with_header.csv'

with open(csv_path, 'r', encoding='utf-8') as f_in, \
     open(header_path, 'w', encoding='utf-8', newline='') as f_out:
    writer = csv.writer(f_out, delimiter=',', quoting=csv.QUOTE_MINIMAL)
    writer.writerow(columns)
    # copy rest of file
    for line in f_in:
        # already CSV lines, but we need to parse and write again? Simpler: just write raw line
        f_out.write(line)

print(f'Header added, saved to {header_path}')