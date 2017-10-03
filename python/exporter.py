import os
import datetime
import time
from env import env
from connection import Connection, Cursor
from subprocess import call
from writer import Writer
from table import get_base_field_types
from formatter import get_field_formatters
from json import dumps
from encoder import DBEncoder

class Exporter:
    """
    Exporter base class.
    """
    def _connect_to_database(self):
        """
        Get a cursor from a connection.Connection object. (Private)

        Database connections are not picklable (serializable) so we must create
        the connection once the __call__ method is used by apply_async (used in a pool).
        :return connection.Cursor:
        """

        return Cursor(Connection())

class RecordExporter(Exporter):
    """
    Exports on a per record basis, rather than a per field basis like FieldExporter.
    """
    def __init__(self, rids, start_time, output = "JSON", fields_displayed = [], meta = "False", show_data = "True", assoc_data = "False"):
        """
        Constructor.
        :param rids: array of rids to export.
        :param output: output format default is JSON.
        :param fields_displayed: fields to display.
        :param meta: gather record meta.
        :param show_data: should we show the data? or just record stuff.
        """

        if output not in ["JSON", "XML", "META"]:
            raise TypeError("Invalid output type.")

        self._rids = rids
        self._output = output
        self._fields_displayed = fields_displayed
        self._meta = meta
        self._show_data = show_data
        self._assoc_data = assoc_data
        self._start_time = start_time

    def __call__(self):
        """
        Call magic method.

        This is used when the object is passed through a pool to a process.
        """
        cursor = self._connect_to_database()
        fid = cursor.fid_from_rid(self._rids[0])
        pid = cursor.pid_from_fid(fid)
        lod_resource_title = cursor.get_form_resource_title(fid)

        stash = cursor.get_field_stash(fid)

        file_name = str(self._rids[0]) + "_" + \
                    str(self._rids[-1]) + "_" + \
                    self._start_time

        python_dir = os.path.dirname(os.path.abspath(__file__))
        target = open(os.path.join(python_dir, "temp", file_name), "w")

        field_formatters = get_field_formatters(self._output)

        for rid in self._rids:

            if  self._output == "JSON":
                record_dict = {
                    "kid": cursor.kid_from_rid(rid),
                    "Fields": {}
                }

                if self._meta == "True":
                    record_dict["meta"] = cursor.meta_from_rid(rid)

                if self._show_data == "True":
                    for table in get_base_field_types():
                        for field in cursor.get_field_data(table, rid):
                            field_dict = {
                                "type": stash[field["flid"]]["type"],
                            }

                            if not self._fields_displayed or stash[field["flid"]]["slug"] in self._fields_displayed:
                                ## Pass the field and field options to the appropriate field formatter based on its type.
                                if table == "associator_fields":
                                    field_dict.update(field_formatters[table]( field, stash[field["flid"]]["options"], self._assoc_data))
                                else:
                                    field_dict.update(field_formatters[table]( field, stash[field["flid"]]["options"]))

                                record_dict["Fields"][stash[field["flid"]]["slug"]] = field_dict
                else:
                    record_dict.pop("Fields")

                target.write(dumps(record_dict, separators=(',', ':'), cls=DBEncoder) + ",")

            if  self._output == "XML":
                record_xml = "<Record kid=\""+cursor.kid_from_rid(rid)+"\">"

                for table in get_base_field_types():
                    for field in cursor.get_field_data(table, rid):
                        ## Pass the field and field options to the appropriate field formatter based on its type.
                        record_xml += "<"+stash[field["flid"]]["slug"]+" type=\""+stash[field["flid"]]["type"]+"\">"
                        if table == "associator_fields":
                            record_xml += field_formatters[table]( field, stash[field["flid"]]["options"], self._assoc_data)
                        else:
                            record_xml += field_formatters[table]( field, stash[field["flid"]]["options"])
                        record_xml += "</"+stash[field["flid"]]["slug"]+">"

                record_xml += "</Record>"

                target.write(record_xml)

            if  self._output == "META":
                resource = "<rdf:Description "

                resource_index_value = cursor.get_resource_index_value(fid, rid)
                resource += "rdf:about=\""+env("BASE_URL")+"projects/"+str(pid)+"/forms/"+str(fid)+"/metadata/public/"+resource_index_value+"\">"

                for table in get_base_field_types():
                    for field in cursor.get_field_data_lod(table, rid):
                        if table == "associator_fields":
                            resource += "<"+lod_resource_title+":"+field["name"]+" rdf:parseType=\"Collection\">"
                        else:
                            resource += "<"+lod_resource_title+":"+field["name"]+">"
                        resource += field_formatters[table]( field, stash[field["flid"]]["options"])
                        resource += "</"+lod_resource_title+":"+field["name"]+">"

                resource += "</rdf:Description>"

                target.write(resource);

        target.close()

class FieldExporter(Exporter):
    """
    Exporter does the actual logic of exporting a table.
    """
    def __init__(self, table, rids, writer):
        """
        Constructor.
        :param table: the table name to export (in BaseFieldTable).
        :param rids: the rids to export.
        :param writer: Writer object.
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
        file_name = self._table + \
                    str(self._rids[0]) + "_" + \
                    str(self._rids[-1]) + "_" + \
                    self._writer.start_time

        python_dir = os.path.dirname(os.path.abspath(__file__))
        target = open(os.path.join(python_dir, "temp", file_name), "w")

        for item in cursor.get_typed_fields(self._rids, self._table):
            target.write(self._writer.write(item))

        target.close()

def collapse_files(writer):
    """
    Concatenates all the files in the writer's temporary directory into one file.

    :param writer: Writer object.
    :return string: absolute path of the out*.* file.
    """
    start_time = writer.start_time
    exports_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "exports")
    temp_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), "temp")

    stamp = datetime.datetime.fromtimestamp(time.time()).strftime('%Y_%m_%d_%H_%M_%S')
    out_file = os.path.join(exports_path, writer.file_name() + stamp + writer.file_extension())

    ## Make sure the outfile name is unique.
    while os.path.exists(out_file):
        stamp = datetime.datetime.fromtimestamp(time.time()).strftime('%Y_%m_%d_%H_%M_%S')
        out_file = os.path.join(exports_path, writer.file_name() + stamp + writer.file_extension())

    writer.header(out_file)

    ## If there are files in the temp directory.
    if len([ name for name in os.listdir(temp_path) if start_time in name ]):
        ## Concatenate all temporary files into one.
        call("cat " + os.path.join(temp_path, "*" + start_time) + " >> " + out_file, shell=True)

        ## Remove temporary files.
        call("rm " + os.path.join(temp_path, "* -f"), shell=True)

    writer.footer(out_file)

    return out_file