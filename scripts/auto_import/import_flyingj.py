# from urllib.request import urlopen as urlreq
import sys
import pandas as pd
import pymysql
from os import mkdir

# cacheFileName = 'cache/ta-locations.xls'
cacheFileName = 'flyingj_locations.xlsx'
# def downloadLocationList():
#     url = 'http://www.ta-petro.com/assets/ce/Documents/Master-Location-List.xls'
#     client = urlreq(url)
#     sheet = client.read()
#     client.close()
#     print("Location list read from website.", file=sys.stderr)
#     try:
#         mkdir('cache')
#     except FileExistsError:
#         # ignore an error that indicates cache already exists
#         pass
#     file = open(cacheFileName, 'wb')
#     file.write(sheet)
#     file.close()


# def getLocationlist():

#     try:
#         # first try to read from local file cache
#         file = open(cacheFileName, 'rb')
#         file.close()

#         print("Location list read from cache.", file=sys.stderr)
#     except Exception as e:
#         # not found in cache, download from website
#         downloadLocationList()


def printSql():
    # create a set of default values in the case of a null cell
    default_values = {
        'Store#': 0,
        'Name':'unknown',
        'Address': 'unknown',
        'State/Province':'unknown',
        'City': 'unknown',
        'Zip':'unknown',
        'Country': 'unknown',
        'Phone':'unknown',
        'Latitude':0.0,
        'Longitude':0.0,
        'Showers':0,
        'Parking Spaces': 0,
        'Diesel Lanes': 0,
        'Facilities/Restaurants': 'No'
    }

    # read in the excel spreadsheet, specifying columns
    # skip rows with no data matching the column headers
    sheet = pd.read_excel(cacheFileName,
        header = 0,
        index_col = 'Store#',
        usecols = [
            'Store#',
            'Name',
            'Address',
            'State/Province',
            'City',
            'Zip',
            'Country',
            'Phone',
            'Latitude',
            'Longitude',
            'Showers',
            'Parking Spaces',
            'Diesel Lanes',
            'Facilities/Restaurants']
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
            'Store#': 'location',
            'Name':'name',
            'Address': 'address',
            'State/Province':'state',
            'City': 'city',
            'Zip':'zipcode',
            'Country': 'country',
            'Phone':'phone',
            'Latitude':'lat',
            'Longitude':'long',
            'Showers':'private_showers',
            'Parking Spaces': 'parking_spaces',
            'Diesel Lanes': 'diesel',
            'Facilities/Restaurants': 'restaurant'
        })

    sqlinsert = 'INSERT INTO facilities (submitted_by, submitter_type, name, address, city, province_state, country, postal, email, phone, website, approval_status, diesel, shower, lat, lng)'

    # print each row as an insert statement. This way if a syntax error arrises
    # in the future, as many rows as possible can be imported successfully.
    for row in newsheet.itertuples():
        submitted_by = 'Pilot Flying J importer'
        submitter_type = 'Other'
        name = pymysql.escape_string(row.name)
        address = pymysql.escape_string(row.address)
        city = pymysql.escape_string(row.city)
        province_state = pymysql.escape_string(row.state)
        country = pymysql.escape_string(row.country)
        postal = row.zipcode
        email = ''
        phone = pymysql.escape_string(row.phone)
        website = 'https://pilotflyingj.com'
        approval_status = 'approved'
        diesel = 1 if (row.diesel != 0) else 0
        shower = 1 if (row.private_showers != 0) else 0
        lat = row.lat
        lng = row.long

        sqlvalues = f"VALUES ('{submitted_by}', '{submitter_type}', '{name}', '{address}', '{city}', '{province_state}', '{country}', '{postal}', '{email}', '{phone}', '{website}', '{approval_status}', '{diesel}', '{shower}', '{lat}', '{lng}');"

        print('{} {}'.format(sqlinsert, sqlvalues))



def main():
    # getLocationlist()
    printSql()


main()
