#!/usr/bin/env python

import multiprocessing
from table import get_base_field_types
from exporter import FieldExporter, collapse_files
from writer import JSONWriter

def main():
    """
    From the command line (or PHP shell_exec)
    Expected values in argv:
        argv[1]: JSON array of rids to export.
        argv[2]: output type (JSON, CSV, or XML)
    """
    # try:
    #     data = loads(argv[1])
    #
    # except IndexError:
    #     return "No arguments given!"

    # try:
    #     out_type = argv[2]
    #
    # except IndexError:
    #     return "No output format given!"

    # python_dir = os.path.dirname(os.path.abspath(__file__))
    # sys.stdout = open(os.path.join(python_dir, "out.json"), "w")
    #
    # cnx = Connection()
    # cursor = Cursor(cnx)
    #
    # data = []
    # for i in range(1, 10000):
    #     data.append(i)
    #
    # print "[",
    # for text_field in cursor.get_typed_fields(data, BaseFieldTypes.TextField):
    #     print dumps(text_field, cls = DBEncoder), ",",
    # print "]"

    writer = JSONWriter() #$ Should be depend on user input.
    data = [ i for i in range(1, 10001) ]
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

    collapse_files(writer.file_extension())

    return 1

if __name__ == "__main__":
    main()