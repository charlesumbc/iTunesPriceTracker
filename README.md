# iTunesPriceChecker

An iTunes price checker using iTunes Search API lookups and a CSV file. Written in PHP.

For more information on the iTunes Search API:
https://www.apple.com/itunes/affiliates/resources/documentation/itunes-store-web-service-search-api.html

### How to get iTunes item ID's:
----------------------------

1. **Get the iTunes URL:** In iTunes Mac/PC, you can copy URL links from the iTunes Store. Right click an item and select "Copy URL." If you don't want to use iTunes, search for items using: https://linkmaker.itunes.apple.com/ and get the URL from the results.

2. **Extract the ID from the URL:** Once you have the URL, the ID is the digits after "id" in the URL.

3. **Use my JavaScript helper:** It's in the URLtoID directory.

For the regex pattern: id(.+)\?
