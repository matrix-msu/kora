#!/usr/bin/env python2.7

import multiprocessing
import sys
from exporter import RecordExporter, collapse_files
from writer import make_writer, Writer
from json import loads
from startup import startup

##
## Main entry point, this script should be called from a PHP exec, or preferred method.
##

def export_routine(argv):
    """
    From the command line (or PHP shell_exec)
    Expected values in argv:
        argv[1]: JSON array of rids to export.
        argv[2]: desired output type (JSON or XML)
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

    writer = make_writer(writer_type, Writer.set_up())

    pool = multiprocessing.Pool(processes = 8)

    ## Get "slice_on" rids at a time.
    slice_on = 500

    i = slice_on
    chunk = data[i - slice_on : i]

    while i - slice_on < len(data):
        exporter = RecordExporter(chunk, writer.start_time, writer_type)

        pool.apply_async(exporter)

        i += slice_on
        chunk = data[i - slice_on : i]

    pool.close()
    pool.join() ## Wait for processes to complete.

    return collapse_files(writer)

if __name__ == "__main__":
    print export_routine(sys.argv)