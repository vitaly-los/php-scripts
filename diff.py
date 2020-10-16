# download files using requests

import requests
r = requests.get('https://imgs.xkcd.com/comics/python.png')

with open('comic.png', 'wb') as f:
  f.write(r.content)


 # find all links on page

import requests
from bs4 import BeautifulSoup

url = 'https://site.ua'

req = requests.get(url)

soup = BeautifulSoup(req.text, 'lxml')

for link in soup.find_all('a'):
    print(link.get('href'))
