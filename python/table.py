class Table:
    """
    "Enumeration" class to hold the table names.

    Do not change these values dynamically.
    """
    ComboListField = "combo_list_fields"
    DateField = "date_fields"
    DocumentsField = "documents_fields"
    GalleryField = "gallery_fields"
    GeneratorListField = "generated_list_fields"
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

class BaseFieldTypes:
    """
    "Enumeration" class to hold the base field types.
    """
    ComboListField = Table.ComboListField
    DateField = Table.DateField
    DocumentsField = Table.DocumentsField
    GalleryField = Table.GalleryField
    GeneratorListField = Table.GeneratorListField
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
    types = []

    for field_name in vars(BaseFieldTypes).items():
        if not field_name[0].startswith("__"):
            types.append(field_name[1])

    return types