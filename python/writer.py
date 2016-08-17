from encoder import DBEncoder
from json import dumps
from os import SEEK_END

class Writer:
    """
    Base class for writer types.
    """
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
        return dumps(item, cls = DBEncoder) + "," ## Note special datetime encoding.

    def file_extension(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        return ".json"

    def header(self, filepath):
        """
        Returns the file header.
        :param filepath: string, absolute path to set up the file header in.
        :return string:
        """
        with open(filepath, "w") as target:
            target.write("[")

    def footer(self, filepath):
        """
        Returns the file footer.
        :param filepath: string, absolute path to file to append footer to.
        :return string:
        """
        with open(filepath, "rb+") as target: ## rb+ ; reading and writing.
            ## Remove the trailing comma and space from the end of the file.
            target.seek(-2, SEEK_END)
            target.truncate()
            target.write("]")

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

