#!/usr/bin/env python

import multiprocessing
from table import get_base_field_types
from exporter import FieldExporter, collapse_files
from writer import JSONWriter, XMLWriter, CSVWriter
from sys import argv
from json import loads
from startup import startup

##
## Main entry point, this script should be called from a PHP shell_exec, or preferred method.
##

def main():
    """
    From the command line (or PHP shell_exec)
    Expected values in argv:
        argv[1]: JSON array of rids to export.
        argv[2]: desired output type (JSON, CSV, or XML)
    """

    startup()

    try:
        data = loads(argv[1])

    except IndexError:
        return "No arguments given!"

    try:
        writer_type = argv[2]

    except IndexError:
        return "No output format given!"

    if writer_type == "JSON":
        writer = JSONWriter()

    elif writer_type == "XML":
        writer = XMLWriter()

    elif writer_type == "CSV":
        writer = CSVWriter()

    else:
        writer = JSONWriter() ## Default to JSON.

    pool = multiprocessing.Pool(processes = 8)

    for table in get_base_field_types():
        ## Get 1000 rids at a time.
        i = 1000
        slice = data[i - 1000 : i]

        while i - 1000 < len(data):
            exporter = FieldExporter(table, slice, writer)

            pool.apply_async(exporter)

            i += 1000
            slice = data[i - 1000 : i]

    pool.close()
    pool.join() ## Wait for processes to complete.

    return collapse_files(writer)

if __name__ == "__main__":
    print main()