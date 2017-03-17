from table import Table, BaseFieldTypes
from connection import Cursor
from datetime import date
from env import env
from xml.sax.saxutils import escape
from collections import OrderedDict

def get_field_formatters(format):
    """
    Gets the formatter dictionaries.

    :param format: string describing format.
    :return dict: a dictionary indexed as:
            table_name: function to format table's data.
    """
    if format == "XML":
        return get_XML_formatters()
    else:
        return get_JSON_formatters()

def get_XML_formatters():
    """
    The XML formatters dictionary.

    :return dict: function dictionary indexed as dict[table_name] = pointer to formatter function.
    """
    return {
        BaseFieldTypes.ComboListField: combo_list_to_XML,
        BaseFieldTypes.DateField: date_to_XML,
        BaseFieldTypes.DocumentsField: documents_to_XML,
        BaseFieldTypes.GalleryField: gallery_to_XML,
        BaseFieldTypes.GeneratedListField: generated_to_XML,
        BaseFieldTypes.GeolocatorField: geolocator_to_XML,
        BaseFieldTypes.ListField: list_to_XML,
        BaseFieldTypes.ModelField: model_to_XML,
        BaseFieldTypes.MultiSelectListField: multi_select_list_to_XML,
        BaseFieldTypes.NumberField: number_to_XML,
        BaseFieldTypes.PlaylistField: playlist_to_XML,
        BaseFieldTypes.RichTextField: rich_text_to_XML,
        BaseFieldTypes.ScheduleField: schedule_to_XML,
        BaseFieldTypes.TextField: text_to_XML,
        BaseFieldTypes.VideoField: video_to_XML,
        BaseFieldTypes.AssociatorField: associator_to_XML
    }

def get_JSON_formatters():
    """
    The JSON formatters dictionary.

    :return dict: function dictionary indexed as dict[table_name] = pointer to formatter function.
    """
    return {
        BaseFieldTypes.ComboListField: combo_list_to_JSONable,
        BaseFieldTypes.DateField: date_to_JSONable,
        BaseFieldTypes.DocumentsField: documents_to_JSONable,
        BaseFieldTypes.GalleryField: gallery_to_JSONable,
        BaseFieldTypes.GeneratedListField: generated_to_JSONable,
        BaseFieldTypes.GeolocatorField: geolocator_to_JSONable,
        BaseFieldTypes.ListField: list_to_JSONable,
        BaseFieldTypes.ModelField: model_to_JSONable,
        BaseFieldTypes.MultiSelectListField: multi_select_list_to_JSONable,
        BaseFieldTypes.NumberField: number_to_JSONable,
        BaseFieldTypes.PlaylistField: playlist_to_JSONable,
        BaseFieldTypes.RichTextField: rich_text_to_JSONable,
        BaseFieldTypes.ScheduleField: schedule_to_JSONable,
        BaseFieldTypes.TextField: text_to_JSONable,
        BaseFieldTypes.VideoField: video_to_JSONable,
        BaseFieldTypes.AssociatorField: associator_to_JSONable
    }

##
## To JSONable and XML functions.
##

def text_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return { "text": row["text"] }

def text_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return escape(row["text"])

def rich_text_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return { "richtext": row["rawtext"] }

def rich_text_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return escape(row["rawtext"])

def number_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return { "number": float(row["number"]) }

def number_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return escape(str(float(row["number"])))

def list_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return { "option": row["option"] }

def list_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return escape(row["option"])

def multi_select_list_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    options = row['options'].split("[!]")
    return { "options": options }

def multi_select_list_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    options = row['options'].split("[!]")
    options_xml = ""

    for val in options:
        options_xml += "<value>"+escape(val)+"</value>"

    return options_xml

def generated_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    options = row['options'].split("[!]")
    return { "options": options }

def generated_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    options = row['options'].split("[!]")
    options_xml = ""

    for val in options:
        options_xml += "<value>"+escape(val)+"</value>"

    return options_xml

def combo_list_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    values = []

    name_one = field_options.split("[!Field1!]")[1].split("[Name]")[1]
    type_one = field_options.split("[!Field1!]")[1].split("[Type]")[1]

    name_two = field_options.split("[!Field2!]")[1].split("[Name]")[1]
    type_two = field_options.split("[!Field2!]")[1].split("[Type]")[1]

    # We want the data from the combo field to be returned two at a time.
    iterator = Cursor.get_support_fields(Table.ComboSupport, row['rid'], row['flid'])
    for data_1 in iterator:
        data_2 = iterator.next()

        if type_one == "Multi-Select List" or type_one == "Generated List":
            val_one = data_1['data'].split('[!]')
        else:
            val_one = data_1['data'] if data_1['data'] is not None else data_1['number']

        if type_two == "Multi-Select List" or type_two == "Generated List":
            val_two = data_2['data'].split('[!]')
        else:
            val_two = data_2['data'] if data_2['data'] is not None else data_2['number']

        val = OrderedDict(
            [
                (name_one, val_one),
                (name_two, val_two)
            ]
        )

        val = {
            name_one: val_one,
            name_two: val_two
        }

        values.append(val)

    return {"options": values}

def combo_list_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    values = ""

    name_one = field_options.split("[!Field1!]")[1].split("[Name]")[1]
    type_one = field_options.split("[!Field1!]")[1].split("[Type]")[1]

    name_two = field_options.split("[!Field2!]")[1].split("[Name]")[1]
    type_two = field_options.split("[!Field2!]")[1].split("[Type]")[1]

    # We want the data from the combo field to be returned two at a time.
    iterator = Cursor.get_support_fields(Table.ComboSupport, row['rid'], row['flid'])
    for data_1 in iterator:
        data_2 = iterator.next()

        if type_one == "Multi-Select List" or type_one == "Generated List":
            val_one_tmp = data_1['data'].split('[!]')
            val_one = ""
            for val in val_one_tmp:
                val_one += "<value>"+escape(val)+"</value>"
        else:
            val_one = data_1['data'] if data_1['data'] is not None else data_1['number']
            val_one = escape(val_one)

        if type_two == "Multi-Select List" or type_two == "Generated List":
            val_two_tmp = data_2['data'].split('[!]')
            val_two = ""
            for val in val_two_tmp:
                val_two += "<value>"+escape(val)+"</value>"
        else:
            val_two = data_2['data'] if data_2['data'] is not None else data_2['number']
            val_two = escape(val_two)

        values += "<Value><"+name_one+">"+val_one+"</"+name_one+"><"+name_two+">"+val_two+"</"+name_two+"></Value>"

    return values

def date_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return {
        "circa": row["circa"],
        "month": row["month"],
        "day": row["day"],
        "year": row["year"],
        "era": row["era"],
        "date_object": row["date_object"]
    }

def date_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """

    date_string = "<Circa>"+str(row["circa"])+"</Circa>"
    date_string += "<Month>"+str(row["month"])+"</Month>"
    date_string += "<Day>"+str(row["day"])+"</Day>"
    date_string += "<Year>"+str(row["year"])+"</Year>"
    date_string += "<Era>"+str(row["era"])+"</Era>"

    return date_string

def schedule_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return:
    """
    events = []

    for result in Cursor.get_support_fields(Table.ScheduleSupport, row['rid'], row['flid']):

        event_dict = {
            "desc": result['desc'],
            "start": result['begin'],
            "end": result['end'],
            "allday": result['allday']
        }

        events.append(event_dict)

    return { "events": events }

def schedule_to_XML(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return:
    """
    events_xml = ""

    for result in Cursor.get_support_fields(Table.ScheduleSupport, row['rid'], row['flid']):
        events_xml += "<Event>"
        events_xml += "<Title>"+escape(result['desc'])+"</Title>"
        events_xml += "<Start>"+str(result['begin'])+"</Start>"
        events_xml += "<End>"+str(result['end'])+"</End>"
        events_xml += "<All_Day>"+str(result['allday'])+"</All_Day>"
        events_xml += "</Event>"

    return events_xml

def file_formatter(files, url):
    """
    Formats file field data.

    File fields are all formatted the same way, they only differ in data name.
    :param list files:
    :return list: list of dictionaries.
    """

    file_list = []

    for file in files:
        file_list.append({
            "name": file.split("[Name]")[1],
            "size": str(int(file.split("[Size]")[1]) / 1000) + " mb",
            "type": file.split("[Type]")[1],
            "url": url+file.split("[Name]")[1]
        })
    return file_list

def file_formatter_xml(files, url):
    """
    Formats file field data.

    File fields are all formatted the same way, they only differ in data name.
    :param list files:
    :return list: list of dictionaries.
    """

    file_xml = ""

    for file in files:
        file_xml += "<File>"
        file_xml += "<Name>" + escape(file.split("[Name]")[1]) + "</Name>"
        file_xml += "<Size>" + str(int(file.split("[Size]")[1]) / 1000) + " mb</Size>"
        file_xml += "<Type>" + file.split("[Type]")[1] + "</Type>"
        file_xml += "<Url>" + escape(url+file.split("[Name]")[1]) + "</Url>"
        file_xml += "</File>"

    return file_xml

def documents_to_JSONable(row, field_options = ""):
    files = row["documents"].split("[!]")

    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return { "files": file_formatter(files,url) }

def documents_to_XML(row, field_options = ""):
    files = row["documents"].split("[!]")

    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return file_formatter_xml(files,url)

def gallery_to_JSONable(row, field_options = ""):
    files = row["images"].split("[!]")

    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return { "files": file_formatter(files,url) }

def gallery_to_XML(row, field_options = ""):
    files = row["documents"].split("[!]")

    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return file_formatter_xml(files,url)

def playlist_to_JSONable(row, field_options = ""):
    files = row["audio"].split("[!]")

    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return { "files": file_formatter(files,url) }

def playlist_to_XML(row, field_options = ""):
    files = row["documents"].split("[!]")

    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return file_formatter_xml(files,url)

def video_to_JSONable(row, field_options = ""):
    files = row["video"].split("[!]")

    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return { "files": file_formatter(files,url) }

def video_to_XML(row, field_options = ""):
    files = row["documents"].split("[!]")

    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return file_formatter_xml(files,url)

def model_to_JSONable(row, field_options = ""):
    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return { "files": file_formatter([ row["model"] ],url) }

def model_to_XML(row, field_options = ""):
    curr_pid = Cursor.pid_from_fid(row["fid"])
    url = env("BASE_URL")+"storage/app/files/p"+str(curr_pid)+"/f"+str(row["fid"])+"/r"+str(row["rid"])+"/fl"+str(row["flid"])+"/"

    return file_formatter_xml([ row["model"] ],url)

def geolocator_to_JSONable(row, field_options = ""):
    locations = []

    for location in Cursor.get_support_fields(Table.GeolocatorSupport, row['rid'], row['flid']):
        locations.append(
            {
                "desc": location['desc'],
                "lat": location['lat'],
                "lon": location['lon'],
                "zone": location['zone'],
                "east": location['easting'],
                "north": location['northing'],
                "address": location['address']
            }
        )

    return { "locations": locations }

def geolocator_to_XML(row, field_options = ""):
    locations_xml = ""

    for location in Cursor.get_support_fields(Table.GeolocatorSupport, row['rid'], row['flid']):
        locations_xml += "<Location>"
        locations_xml += "<Desc>" + escape(location['desc']) + "</Desc>"
        locations_xml += "<Lat>" + str(location['lat']) + "</Lat>"
        locations_xml += "<Lon>" + str(location['lon']) + "</Lon>"
        locations_xml += "<Zone>" + location['zone'] + "</Zone>"
        locations_xml += "<East>" + str(location['easting']) + "</East>"
        locations_xml += "<North>" + str(location['northing']) + "</North>"
        locations_xml += "<Address>" + escape(location['address']) + "</Address>"
        locations_xml += "</Location>"

    return locations_xml

def associator_to_JSONable(row, field_options = ""):
    ## TODO: Figure out if associator is even a thing.
    return {}

def associator_to_XML(row, field_options = ""):
    ## TODO: Figure out if associator is even a thing.
    return ""