import urllib.request
import urllib.parse
import sys
import pandas as pd
import pymysql
from os import mkdir

cacheFileName = 'cache/loves_locations.xlsx'
def downloadLocationList():
    url = 'https://www.loves.com/api/sitecore/StoreSearch/Download'
    values = {
        'locationSearch':'{"StoreTypes":["Travel Stop"],"Amenities":[],"Restaurants":[],"FoodConcepts":[],"State":"All"}',
        'lat': '0.0',
        'lng': '0.0'
    }
    data = urllib.parse.urlencode(values)
    data = data.encode('ascii')
    req = urllib.request.Request(url, data)
    with urllib.request.urlopen(req) as response:
        sheet = response.read()
    response.close()
    print("Location list read from website.", file=sys.stderr)
    try:
        mkdir('cache')
    except FileExistsError:
        # ignore an error that indicates cache already exists
        pass
    file = open(cacheFileName, 'wb')
    file.write(sheet)
    file.close()


def getLocationlist():

    try:
        # first try to read from local file cache
        file = open(cacheFileName, 'rb')
        file.close()

        print("Location list read from cache.", file=sys.stderr)
    except Exception as e:
        # not found in cache, download from website
        downloadLocationList()


def printSql():
    # create a set of default values in the case of a null cell
    default_values = {
        'Store #': 0,
        'Store Type':'unknown',
        'Address': 'unknown',
        'State':'unknown',
        'City': 'unknown',
        'Zip':'unknown',
        'Phone':'unknown',
        'Latitude':0.0,
        'Longitude':0.0,
        'Private Showers':0,
        'Laundry Facility':False,
        'Truck Parking': 0,
        'Restaurants': 'No'
    }

    # read in the excel spreadsheet, specifying columns
    # skip rows with no data matching the column headers
    sheet = pd.read_excel(cacheFileName,
        header = 5,
        index_col = 'Store #',
        usecols = [
            'Store #',
            'Store Type',
            'Address',
            'State',
            'City',
            'Zip',
            'Phone',
            'Latitude',
            'Longitude',
            'Private Showers',
            'Laundry Facility',
            'Truck Parking',
            'Restaurants']
    )

    # apply the default values for null cells
    sheet.fillna(value=default_values, inplace=True)

    # convert floating point cells to integer if it suits the data
    newsheet = sheet.convert_dtypes(convert_integer=True)

    # rename the columns in the dataframe. otherwise column names
    # with spaces cannot be found in the dataset by name
    newsheet.rename(
        inplace = True,
        columns = {
            'Store #': 'location',
            'Store Type':'name',
            'Address': 'address',
            'State':'state',
            'City': 'city',
            'Zip':'zipcode',
            'Phone':'phone',
            'Latitude':'lat',
            'Longitude':'long',
            'Private Showers':'private_showers',
            'Laundry Facility':'laundry',
            'Truck Parking': 'parking_spaces',
            'Restaurants': 'restaurant'
        })

    sqlinsert = 'INSERT INTO facilities (submitted_by, submitter_type, name, address, city, province_state, country, postal, email, phone, website, approval_status, diesel, shower, meal, lat, lng)'

    # print each row as an insert statement. This way if a syntax error arises
    # in the future, as many rows as possible can be imported successfully.
    for row in newsheet.itertuples():
        submitted_by = 'Loves importer'
        submitter_type = 'Other'
        name = pymysql.escape_string(f"Loves {row.name}")
        address = pymysql.escape_string(row.address)
        city = pymysql.escape_string(row.city)
        province_state = pymysql.escape_string(row.state)
        country = 'USA'
        postal = row.zipcode
        email = ''
        phone = pymysql.escape_string(row.phone)
        website = f'https://loves.com/locations/{row.Index}'
        approval_status = 'approved'
        diesel = 1
        shower = 1 if (row.private_showers == 'Y') else 0
        meal = 1 if (row.restaurant != 'No') else 0
        lat = row.lat
        lng = row.long

        sqlvalues = f"VALUES ('{submitted_by}', '{submitter_type}', '{name}', '{address}', '{city}', '{province_state}', '{country}', '{postal}', '{email}', '{phone}', '{website}', '{approval_status}', '{diesel}', '{shower}', '{meal}', '{lat}', '{lng}');"

        print('{} {}'.format(sqlinsert, sqlvalues))



def main():
    getLocationlist()
    printSql()


main()
