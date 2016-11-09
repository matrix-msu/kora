from table import BaseFieldTypes

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
    # TODO: implement.
    return {}

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
## To JSONable functions.
##

def text_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return { "text": row["text"] }

def rich_text_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return { "richtext": row["rawtext"] }

def number_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return { "number": float(row["number"]) }

def list_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    return { "option": row["option"] }

def multi_select_list_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    options = row['options'].split("[!]")
    return { "options": options }

def generated_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return dict:
    """
    options = row['options'].split("[!]")
    return { "options": options }

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

    for item in row["options"].split("[!val!]"):
        val_one = item.split("[!f1!]")
        val_two = item.split("[!f2!]")

        ## If the combo fields have array-type, we have to split them apart.
        if type_one == "Multi-Select List" or type_one == "Generated List":
            val_one = val_one[1].split("[!]")
        else:
            val_one = val_one[1]

        if type_two == "Multi-Select List" or type_two == "Generated List":
            val_two = val_two[1].split("[!]")
        else:
            val_two = val_two[1]

        values.append({
            name_one: val_one,
            name_two: val_two
        })

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

def schedule_to_JSONable(row, field_options = ""):
    """
    :param row:
    :param field_options:
    :return:
    """
    events = []

    for item in row["events"].split("[!]"):
        title_date = item.split(": ") ## An array with the title at [0] and the date range at [1].

        ## Two arrays that vary based on if the dates are "all day" instances.
        start = title_date[1].split(" - ")[0].split(" ")
        end = title_date[1].split(" - ")[1].split(" ")

        if len(start) == 1: ## "All day".
            event_dict = {
                "start": start[0],
                "end": end[0],
                "allday": 1
            }
        else: ## Has a start and end time.
            event_dict = {
                "start": start[0] + start[1] + start[2],
                "end": end[0] + end[1] + end[2],
                "allday": 0
            }

        events.append(event_dict)

    return { "events": events }

def file_formatter(files):
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
            "type": file.split("[Type]")[1]
        })
    return file_list

def documents_to_JSONable(row, field_options = ""):
    files = row["documents"].split("[!]")

    return { "files": file_formatter(files) }

def gallery_to_JSONable(row, field_options = ""):
    files = row["images"].split("[!]")

    return { "files": file_formatter(files) }

def playlist_to_JSONable(row, field_options = ""):
    files = row["audio"].split("[!]")

    return { "files": file_formatter(files) }

def video_to_JSONable(row, field_options = ""):
    files = row["video"].split("[!]")

    return { "files": file_formatter(files) }

def model_to_JSONable(row, field_options = ""):
    return { "files": file_formatter([ row["model"] ]) }

def geolocator_to_JSONable(row, field_options = ""):
    locations = []

    for location in row["locations"].split("[!]"):
        locations.append({
            "desc": location.split("[Desc]")[1],
            "lat": location.split("[LatLon]")[1].split(",")[0],
            "lon": location.split("[LatLon]")[1].split(",")[1],
            "zone": location.split("[UTM]")[1].split(":")[0],
            "east": location.split("[UTM]")[1].split(":")[1].split(",")[0],
            "north": location.split("[UTM]")[1].split(":")[1].split(",")[1],
            "address": location.split("[Address]")[1]
        })

    return { "locations": locations }

def associator_to_JSONable(row, field_options = ""):
    ## TODO: Figure out if associator is even a thing.
    return {}

##
## To XMLable functions.
##