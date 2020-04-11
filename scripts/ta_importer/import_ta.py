from urllib.request import urlopen as urlreq
import sys
import pandas as pd
import pymysql
from os import mkdir

cacheFileName = 'cache/ta-locations.xls'


def downloadLocationList():
    url =  'http://www.ta-petro.com/assets/ce/Documents/Master-Location-List.xls'
    client = urlreq(url)
    sheet = client.read()
    client.close()
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
        'LOCATION_ID': 0,
        'LOCATION':'unknown',
        'ADDRESS': 'unknown',
        'STATE':'unknown',
        'CITY': 'unknown',
        'ZIPCODE':'unknown',
        'PHONE':'unknown',
        'LATITUDE':0.0,
        'LONGITUDE':0.0,
        'PRIVATE SHOWERS':0,
        'HANDICAPPED SHOWERS':0,
        'LAUNDRY':False,
        'TOTAL DIESEL DISPENSERS/LANES':0,
        'TRUCK PARKING SPACES': 0,
        'FULL SERVE RESTAURANT': 'No',
        "CAFÉ' EXPRESS": 'No'
    }

    # read in the excel spreadsheet, specifying columns
    # skip rows with no data matching the column headers
    sheet = pd.read_excel(cacheFileName,
        header = 0,
        index_col = 'LOCATION_ID',
        skiprows = [1,2,3,4],
        usecols = [
            'LOCATION_ID',
            'LOCATION',
            'ADDRESS',
            'STATE',
            'CITY',
            'ZIPCODE',
            'PHONE',
            'LATITUDE',
            'LONGITUDE',
            'PRIVATE SHOWERS',
            'HANDICAPPED SHOWERS',
            'LAUNDRY',
            'TOTAL DIESEL DISPENSERS/LANES',
            'TRUCK PARKING SPACES',
            'FULL SERVE RESTAURANT',
            "CAFÉ' EXPRESS"]
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
            'LOCATION_ID': 'location',
            'LOCATION':'name',
            'ADDRESS': 'address',
            'STATE':'state',
            'CITY': 'city',
            'ZIPCODE':'zipcode',
            'PHONE':'phone',
            'LATITUDE':'lat',
            'LONGITUDE':'long',
            'PRIVATE SHOWERS':'private_showers',
            'HANDICAPPED SHOWERS':'handicapped_showers',
            'LAUNDRY':'laundry',
            'TOTAL DIESEL DISPENSERS/LANES':'diesel_lanes',
            'TRUCK PARKING SPACES': 'parking_spaces',
            'FULL SERVE RESTAURANT': 'restaurant',
            "CAFÉ' EXPRESS": 'cafe'
        })

    sqlinsert = 'INSERT INTO facilities (submitted_by, submitter_type, name, address, city, province_state, country, postal, email, phone, website, approval_status, diesel, shower, lat, lng)'

    # print each row as an insert statement. This way if a syntax error arrises
    # in the future, as many rows as possible can be imported successfully.
    for row in newsheet.itertuples():
        submitted_by = 'TA importer'
        submitter_type = 'Other'
        name = pymysql.escape_string(row.name)
        address = pymysql.escape_string(row.address)
        city = pymysql.escape_string(row.city)
        province_state = pymysql.escape_string(row.state)
        country = 'USA'
        postal = row.zipcode
        email = ''
        phone = pymysql.escape_string(row.phone)
        website = 'https://www.ta-petro.com'
        approval_status = 'approved'
        diesel = 1 if (row.diesel_lanes > 0) else 0
        shower = 1 if (row.private_showers + row.handicapped_showers > 0) else 0
        lat = row.lat
        lng = row.long

        sqlvalues = f"VALUES ('{submitted_by}', '{submitter_type}', '{name}', '{address}', '{city}', '{province_state}', '{country}', '{postal}', '{email}', '{phone}', '{website}', '{approval_status}', '{diesel}', '{shower}', '{lat}', '{lng}');"

        print('{} {}'.format(sqlinsert, sqlvalues))



def main():
    getLocationlist()
    printSql()


main()
