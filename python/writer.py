from encoder import DBEncoder
from json import dumps

class Writer:
    """
    Base class for writer types.
    """
    def write(self, item):
        return

    def file_extension(self):
        return ""

class JSONWriter(Writer):
    """
    JSON writer class.
    """
    def write(self, item):
        """
        Uses the built in JSON methods.

        :param item: thing to be written to json.
        :return: json object.
        """
        return dumps(item, cls = DBEncoder) ## Note special datetime encoding.

    def file_extension(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        return ".json"

class XMLWriter(Writer):
    """
    XML Writer class.
    """
    def write(self, item): ## TODO: Implement.
        return

    def file_extension(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        return ".xml"


class CSVWriter(Writer):
    """
    CSV writer class.
    """
    def write(self, item): ## TODO: Implement.
        return

    def file_extension(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        return ".csv"

