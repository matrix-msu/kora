import os
import sys
import datetime
import time
from connection import Connection, Cursor
from subprocess import call
from writer import Writer

class FieldExporter:
    """
    Exporter does the actual logic of exporting a table.
        1. Spawns one thread to "produce"; format the database records.
        2. Spawns one thread to "consume"; write records to a file.
    """
    def __init__(self, table, rids, writer):
        """
        Constructor.
        :param table: the table name to export (in BaseFieldTable).
        :param rids: the rids to export.
        :param writer_type: string, which kind of writer should be used.
        """

        if not isinstance(writer, Writer):
            raise TypeError("writer in FieldExporter constructor needs to be an instance of writer.Writer")

        self._table = table
        self._rids = rids
        self._writer = writer

    def __call__(self):
        """
        Call magic method.

        This is used when a FieldExporter object is passed through a pool to a process.
        """
        cursor = self._connect_to_database()

        ## Unique file name to eliminate any possible writing collisions.
        file_name = self._table + "_" + str(self._rids[0]) + "to" + str(self._rids[-1]) + self._writer.file_extension()
        python_dir = os.path.dirname(os.path.abspath(__file__))
        sys.stdout = open(os.path.join(python_dir, "temp", file_name), "w")

        for item in cursor.get_typed_fields(self._rids, self._table):
            print self._writer.write(item)

    def _connect_to_database(self):
        """
        Get a cursor from a connection.Connection object. (Private)

        Database connections are not picklable (serializable) so we must create
        the connection once the __call__ method is used by apply_async.
        :return connection.Cursor:
        """

        return Cursor(Connection())

def collapse_files(writer):
    """
    Concatenates all the files in the /exports directory into one file.

    :param file_extension: string, the desired file extension.
    :return string: absolute path of the out*.* file.
    """
    temp_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "temp")
    exports_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "exports")

    stamp = datetime.datetime.fromtimestamp(time.time()).strftime('%Y_%m_%d_%H_%M_%S')

    out_file = os.path.join(exports_path, "out" + stamp + writer.file_extension())

    writer.header(out_file)

    ## Concatenate all temporary files into one.
    call("cat " + os.path.join(temp_path, "*") + " >> " + out_file, shell=True)

    ## Remove temporary files.
    call("rm " + os.path.join(temp_path, "* -f"), shell=True)

    writer.footer(out_file)

    return out_file