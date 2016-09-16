import time
import os
from encoder import DBEncoder
from json import dumps


class Writer:
    """
    Base class for writer types.
    """
    def __init__(self, start_time):
        """
        Writer constructor.
        :param temp_path: path to temporary file where writing should occur.
        """
        self.start_time = start_time

    @staticmethod
    def set_up():
        """
        Creates the initial time stamp all other files will use for uniqueness.
        :return string:
        """
        return str(time.time())

    def write(self, item):
        """
        Base method.
        :param item: expects a dictionary.
        :return string: empty.
        """
        return ""

    def file_extension(self):
        """
        Base method.
        :return string: empty.
        """
        return ""

    def header(self, filepath):
        """
        Base method.
        :param filepath: expects absolute path to file.
        """
        pass

    def footer(self, filepath):
        """
        Base method.
        :param filepath: expects absolute path to file.
        :return:
        """
        pass

class JSONWriter(Writer):
    """
    JSON writer class.
    """
    def write(self, item):
        """
        Uses the built in JSON methods.

        :param item: thing to be written to json.
        :return string: json object.
        """
        return dumps(item, cls = DBEncoder, separators=(',', ':')) + "," ## Note special datetime encoding.

    def file_extension(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        return ".json"

    def header(self, filepath):
        """
        Writes the header to a file. Should be an empty file, else it will be truncated.
        :param filepath: string, absolute path to set up the file header in.
        """
        with open(filepath, "w") as target:
            target.write("{\"Records\":[")

    def footer(self, filepath):
        """
        Writes the footer to a file.
        :param filepath: string, absolute path to file to append footer to.
        """
        with open(filepath, "rb+") as target: #a to append
            target.seek(-1, os.SEEK_END)
            target.truncate()
            target.write("]}")

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

    def header(self, filepath): ## TODO: Implement
        """
        Writes the header to a file. Should be an empty file, else it will be truncated.
        :param filepath: string, absolute path to set up the file header in.
        """
        pass

    def footer(self, filepath): ## TODO: Implement
        """
        Writes the footer to a file.
        :param filepath: string, absolute path to file to append footer to.
        """
        pass

def make_writer(format, temp_path):
    """
    Create a writer object based on desired output format.

    :param format: string, desired output.
    :param temp_path: string, file path of temporary output folder.
    :return Writer:
    """
    if format == "JSON":
        return JSONWriter(temp_path)

    elif format == "XML":
        return XMLWriter(temp_path)

    return JSONWriter(temp_path) ## Default to JSON.