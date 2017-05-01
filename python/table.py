class Table:
    """
    "Enumeration" class to hold the table names.

    Do not change these values dynamically.
    """
    ComboListField = "combo_list_fields"
    DateField = "date_fields"
    DocumentsField = "documents_fields"
    GalleryField = "gallery_fields"
    GeneratedListField = "generated_list_fields"
    GeolocatorField = "geolocator_fields"
    ListField = "list_fields"
    ModelField = "model_fields"
    MultiSelectListField = "multi_select_list_fields"
    NumberField = "number_fields"
    PlaylistField = "playlist_fields"
    RichTextField = "rich_text_fields"
    ScheduleField = "schedule_fields"
    TextField = "text_fields"
    VideoField = "video_fields"
    AssociatorField = "associator_fields"
    Project = "projects"
    PasswordReset = "password_resets"
    Form = "forms"
    Field = "fields"
    Record = "records"
    User = "users"
    Token = "tokens"
    ProjectToken = "project_token"  ## Pivot table
    Metadata = "metadatas"
    ProjectGroup = "project_groups"
    ProjectGroupUser = "project_group_user"  ## Pivot Table
    FormGroup = "form_groups"
    FormGroupUser = "form_group_user"  ## Pivot Table
    Revision = "revisions"
    RecordPreset = "record_presets"
    OptionPreset = "option_presets"
    Association = "associations"
    Version = "versions"
    Script = "scripts"
    Plugins = "plugins"
    PluginsSettings = "plugin_settings"
    PluginsUsers = "plugin_users"
    PluginMenus = "plugin_menus"
    ScheduleSupport = "schedule_support"
    GeolocatorSupport = "geolocator_support"
    AssociatorSupport = "associator_support"
    ComboSupport = "combo_support"

class BaseFieldTypes:
    """
    "Enumeration" class to hold the base field types.
    """
    ComboListField = Table.ComboListField
    DateField = Table.DateField
    DocumentsField = Table.DocumentsField
    GalleryField = Table.GalleryField
    GeneratedListField = Table.GeneratedListField
    GeolocatorField = Table.GeolocatorField
    ListField = Table.ListField
    ModelField = Table.ModelField
    MultiSelectListField = Table.MultiSelectListField
    NumberField = Table.NumberField
    PlaylistField = Table.PlaylistField
    RichTextField = Table.RichTextField
    ScheduleField = Table.ScheduleField
    TextField = Table.TextField
    VideoField = Table.VideoField
    AssociatorField = Table.AssociatorField

def get_base_field_types():
    """
    :return list: all the non-magic attributes of BaseFieldTypes
    """
    types = []

    for field_name in vars(BaseFieldTypes).items():
        if not field_name[0].startswith("__"):
            types.append(field_name[1])

    return types

def get_data_names(table):
    """
    Gets the name(s) of the important data in a certain BaseField, formatted for MySQL.

    :param table: a valid BaseField table name.
    :return string:
    """

    return {
        BaseFieldTypes.ComboListField: "",
        BaseFieldTypes.DateField: ", `circa`, `month`, `day`, `year`, `era`, `date_object`",
        BaseFieldTypes.DocumentsField: ", `documents`",
        BaseFieldTypes.GalleryField: ", `images`",
        BaseFieldTypes.GeneratedListField: "",
        BaseFieldTypes.GeolocatorField: "",
        BaseFieldTypes.ListField: ", `option`",
        BaseFieldTypes.ModelField: ", `model`",
        BaseFieldTypes.MultiSelectListField: ", `options`",
        BaseFieldTypes.NumberField: ", `number`",
        BaseFieldTypes.PlaylistField: ", `audio`",
        BaseFieldTypes.RichTextField: ", `rawtext`",
        BaseFieldTypes.ScheduleField: ", `events`",
        BaseFieldTypes.TextField: ", `text`",
        BaseFieldTypes.VideoField: ", `video`",
        BaseFieldTypes.AssociatorField: ""
    }[table]

def get_all_tables():
    """
    :return list: all the non-magic attributes of Table
    """

    tables = []

    for table in vars(Table).items():
        if not table[0].startswith("__"):
            tables.append(table[1])

    return tables

def is_valid_table(table):
    """
    Determine if a string is a valid table name.

    :param table: string to test
    :return bool: true if valid
    """
    return table in get_all_tables()

def is_valid_base_field(table):
    """
    Determine if a string is a valid base field name.

    :param table: string to test
    :return bool: true if valid
    """
    return table in get_base_field_types()