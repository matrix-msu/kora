import time
import os
from encoder import DBEncoder
from json import dumps
from env import env
from connection import Connection, Cursor

class Writer:
    """
    Base class for writer types.
    """
    def __init__(self, start_time, fid, pid):
        """
        Writer constructor.
        :param temp_path: path to temporary file where writing should occur.
        """
        self.start_time = start_time
        self.fid = fid
        self.pid = pid

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

    def file_name(self):
        """
        Base method.
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

    def file_name(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        cursor = self._connect_to_database()

        project_slug = cursor.get_project_slug(self.pid)
        form_slug = cursor.get_form_slug(self.fid)

        return project_slug+"_"+form_slug+"_"

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
        We have to open the file twice to see if there was anything written to the file.
        Then truncate in "a" mode if that is the case (truncate() only works in append mode for some systems).
        :param filepath: string, absolute path to file to append footer to.
        """
        records_written = False

        with open(filepath, "r+") as target:
            target.seek(-1, os.SEEK_END)

            if target.read() == ",":
                records_written = True

            else:
                target.write("]}")

        if records_written:
            with open(filepath, "a") as target:
                target.seek(-1, os.SEEK_END)

                target.truncate() ## Remove trailing ",".
                target.write("]}")

    def _connect_to_database(self):
        """
        Get a cursor from a connection.Connection object. (Private)

        Database connections are not picklable (serializable) so we must create
        the connection once the __call__ method is used by apply_async (used in a pool).
        :return connection.Cursor:
        """

        return Cursor(Connection())

class XMLWriter(Writer):
    """
    XML Writer class.
    """
    def write(self, item): ## TODO: Implement.
        return

    def file_name(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        cursor = self._connect_to_database()

        project_slug = cursor.get_project_slug(self.pid)
        form_slug = cursor.get_form_slug(self.fid)

        return project_slug+"_"+form_slug+"_"

    def file_extension(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        return ".xml"

    def header(self, filepath):
        """
        Writes the header to a file. Should be an empty file, else it will be truncated.
        :param filepath: string, absolute path to set up the file header in.
        """
        with open(filepath, "w") as target:
                    target.write("<?xml version=\"1.0\" encoding=\"utf-8\"?><Records>")

    def footer(self, filepath):
        """
        Writes the footer to a file.
        :param filepath: string, absolute path to file to append footer to.
        """
        with open(filepath, "a") as target:
                    target.write("</Records>")

    def _connect_to_database(self):
        """
        Get a cursor from a connection.Connection object. (Private)

        Database connections are not picklable (serializable) so we must create
        the connection once the __call__ method is used by apply_async (used in a pool).
        :return connection.Cursor:
        """

        return Cursor(Connection())

class METAWriter(Writer):
    """
    META Writer class.
    """
    def write(self, item): ## TODO: Implement.
        return

    def file_name(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        cursor = self._connect_to_database()

        project_slug = cursor.get_project_slug(self.pid)
        form_slug = cursor.get_form_slug(self.fid)

        return project_slug+"_"+form_slug+"_"

    def file_extension(self):
        """
        Returns the appropriate file extension.
        :return string:
        """
        return ".xml"

    def header(self, filepath):
        """
        Writes the header to a file. Should be an empty file, else it will be truncated.
        :param filepath: string, absolute path to set up the file header in.
        """
        cursor = self._connect_to_database()

        resource_title = cursor.get_form_resource_title(self.fid)

        with open(filepath, "w") as target:
                    header = "<?xml version=\"1.0\"?><rdf:RDF "
                    header += "xmlns:rdf=\"http://www.w3.org/1999/02/22-rdf-syntax-ns#\" "
                    header += "xmlns:"+resource_title+"=\""+env("BASE_URL")+"public/projects/"+str(self.pid)+"/forms/"+str(self.fid)+"/metadata/public#\">"
                    target.write(header)

    def footer(self, filepath):
        """
        Writes the footer to a file.
        :param filepath: string, absolute path to file to append footer to.
        """
        with open(filepath, "a") as target:
                    target.write("</rdf:RDF>")

    def _connect_to_database(self):
        """
        Get a cursor from a connection.Connection object. (Private)

        Database connections are not picklable (serializable) so we must create
        the connection once the __call__ method is used by apply_async (used in a pool).
        :return connection.Cursor:
        """

        return Cursor(Connection())

def make_writer(format, temp_path, fid=0, pid=0):
    """
    Create a writer object based on desired output format.

    :param format: string, desired output.
    :param temp_path: string, file path of temporary output folder.
    :return Writer:
    """
    if format == "JSON":
        return JSONWriter(temp_path,fid,pid)

    elif format == "XML":
        return XMLWriter(temp_path,fid,pid)

    elif format == "META":
            return METAWriter(temp_path,fid,pid)

    return JSONWriter(temp_path,fid,pid) ## Default to JSON.