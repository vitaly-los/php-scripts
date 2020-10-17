# script for using dorks
import requests

url = 'http://target.org/'

lines = open('dorks.txt', 'r')

for l in lines:
    full_url = url + l.strip()
    r = requests.get(full_url)
    if r.status_code == 200:
        with open('ds.txt', 'a+') as d_txt:
            d_txt.write(r.url + '\n')
