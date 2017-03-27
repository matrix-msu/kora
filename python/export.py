#!/usr/bin/env python2.7

import multiprocessing
import sys
from exporter import RecordExporter, collapse_files
from writer import make_writer, Writer
from json import loads
from startup import startup
from connection import Connection, Cursor

##
## Main entry point, this script should be called from a PHP exec, or preferred method.
##

def export_routine(argv):
    """
    From the command line (or PHP shell_exec)
    Expected values in argv:
        argv[1]: JSON array of rids to export.
        argv[2]: desired output type (JSON or XML)
        argv[3]: JSON array of fields to display
        argv[4]: Gather record meta
        argv[5]: Outright hide the field data if we just want record data
    """

    startup() ## Initialize file structure.

    try:
        data = loads(argv[1])

    except IndexError:
        return "No arguments given!"

    try:
        writer_type = argv[2]

    except IndexError:
        return "No output format given!"

    try:
        fields_displayed = loads(argv[3])

    except IndexError:
        fields_displayed = []

    try:
        meta = argv[4]

    except IndexError:
        meta = "False"

    try:
        show_data = argv[5]

    except IndexError:
        show_data = "True"

    cursor = connect_to_database()
    fid = cursor.fid_from_rid(data[0])
    pid = cursor.pid_from_fid(fid)

    writer = make_writer(writer_type, Writer.set_up(), fid, pid)

    pool = multiprocessing.Pool(processes = 8)

    ## Get "slice_on" rids at a time.
    slice_on = 500

    i = slice_on
    chunk = data[i - slice_on : i]

    while i - slice_on < len(data):
        exporter = RecordExporter(chunk, writer.start_time, writer_type, fields_displayed, meta, show_data)

        exporter()
        ## pool.apply_async(exporter)

        i += slice_on
        chunk = data[i - slice_on : i]

    pool.close()
    pool.join() ## Wait for processes to complete.

    return collapse_files(writer)

def connect_to_database():
    """
    Get a cursor from a connection.Connection object. (Private)

    Database connections are not picklable (serializable) so we must create
    the connection once the __call__ method is used by apply_async (used in a pool).
    :return connection.Cursor:
    """

    return Cursor(Connection())

if __name__ == "__main__":
    print export_routine(sys.argv)